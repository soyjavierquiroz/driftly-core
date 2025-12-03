<?php

if (!defined('ABSPATH')) {
    exit;
}

class Driftly_VDS_Ajax {

    public function __construct() {
        add_action('wp_ajax_driftly_toggle_product_vds', [ $this, 'toggle_product' ]);
        add_action('wp_ajax_driftly_update_product_vds', [ $this, 'update_product' ]);
        add_action('wp_ajax_driftly_get_product_details', [ $this, 'get_product_details' ]);
    }

    /**
     * ACTIVAR / DESACTIVAR producto para este VDS.
     */
    public function toggle_product() {

        if (!is_user_logged_in()) {
            wp_send_json_error([ 'message' => 'No autorizado' ]);
        }

        $vds_id     = get_current_user_id();
        $product_id = intval($_POST['producto_id'] ?? 0);

        if (!$product_id) {
            wp_send_json_error([ 'message' => 'Producto inválido' ]);
        }

        global $wpdb;
        $tabla = $wpdb->prefix . 'driftly_vds_productos';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tabla 
                 WHERE vds_id = %d AND product_id = %d 
                 LIMIT 1",
                $vds_id,
                $product_id
            )
        );

        if ($row) {

            // Cambiar estado
            $nuevo_estado = $row->activo ? 0 : 1;

            $wpdb->update(
                $tabla,
                [
                    'activo'            => $nuevo_estado,
                    'fecha_actualizado' => current_time('mysql'),
                ],
                [
                    'vds_id'     => $vds_id,
                    'product_id' => $product_id,
                ],
                [ '%d', '%s' ],
                [ '%d', '%d' ]
            );

            wp_send_json_success([ 'activo' => $nuevo_estado ]);

        } else {
            // Crear registro nuevo (activando el producto)
            $precio_vendedor = get_field('precio_vendedor', $product_id);
            $precio_inicial   = floatval($precio_vendedor) * 1.10;

            $wpdb->insert(
                $tabla,
                [
                    'vds_id'           => $vds_id,
                    'product_id'       => $product_id,
                    'precio_final'     => $precio_inicial,
                    'descripcion'      => '',
                    'orden'            => 0,
                    'activo'           => 1,
                    'fecha_creado'     => current_time('mysql'),
                    'fecha_actualizado'=> current_time('mysql'),
                ]
            );

            wp_send_json_success([ 'activo' => 1 ]);
        }
    }

    /**
     * ACTUALIZAR precio_final, descripcion y orden
     */
    public function update_product() {

        if (!is_user_logged_in()) {
            wp_send_json_error([ 'message' => 'No autorizado' ]);
        }

        $vds_id     = get_current_user_id();
        $product_id = intval($_POST['product_id'] ?? 0);

        if (!$product_id) {
            wp_send_json_error([ 'message' => 'Producto inválido' ]);
        }

        $precio = floatval($_POST['precio'] ?? 0);
        $orden  = intval($_POST['orden'] ?? 0);
        $desc   = sanitize_textarea_field($_POST['descripcion'] ?? '');

        global $wpdb;
        $tabla = $wpdb->prefix . 'driftly_vds_productos';

        // Verificar existencia
        $existe = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $tabla 
                 WHERE vds_id = %d AND product_id = %d",
                $vds_id,
                $product_id
            )
        );

        if (!$existe) {
            wp_send_json_error([
                'message' => 'Este producto no está habilitado para este VDS.'
            ]);
        }

        // Actualizar
        $wpdb->update(
            $tabla,
            [
                'precio_final'      => $precio,
                'descripcion'       => $desc,
                'orden'             => $orden,
                'fecha_actualizado' => current_time('mysql'),
            ],
            [
                'vds_id'     => $vds_id,
                'product_id' => $product_id,
            ],
            [ '%f', '%s', '%d', '%s' ],
            [ '%d', '%d' ]
        );

        wp_send_json_success([
            'ok' => true,
            'precio' => $precio,
            'orden' => $orden,
            'descripcion' => $desc,
            'message' => 'Producto actualizado correctamente.'
        ]);
    }

    /**
     * OBTENER detalle completo de un producto para el modal.
     */
    public function get_product_details() {

        if (!is_user_logged_in()) {
            wp_send_json_error([ 'message' => 'No autorizado' ]);
        }

        $vds_id     = get_current_user_id();
        $product_id = intval($_POST['product_id'] ?? 0);

        if (!$product_id) {
            wp_send_json_error([ 'message' => 'Producto inválido' ]);
        }

        $post = get_post($product_id);

        if (!$post || $post->post_type !== 'product') {
            wp_send_json_error([ 'message' => 'Producto no encontrado' ]);
        }

        global $wpdb;
        $tabla = $wpdb->prefix . 'driftly_vds_productos';
        $tabla_urls = $wpdb->prefix . 'driftly_vds_urls';

        // Datos WooCommerce
        $precio_vendedor = get_field('precio_vendedor', $product_id);
        $precio_sugerido  = get_field('precio_sugerido', $product_id);
        $proveedor_id     = get_field('proveedor_id', $product_id);

        $proveedor_nombre = $proveedor_id
            ? get_user_meta($proveedor_id, 'nombre_comercial', true)
            : 'Sin proveedor';

        $thumb = get_the_post_thumbnail_url($product_id, 'large');
        if (!$thumb && function_exists('wc_placeholder_img_src')) {
            $thumb = wc_placeholder_img_src();
        }

        $descripcion_base = $post->post_excerpt ?: $post->post_content;
        $descripcion_base = wp_kses_post(wpautop($descripcion_base));

        // Config del VDS
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tabla 
                 WHERE vds_id = %d AND product_id = %d 
                 LIMIT 1",
                $vds_id,
                $product_id
            )
        );

        $activo          = $row ? (bool) $row->activo : false;
        $precio_vds      = $row ? (float) $row->precio_final : (float) $precio_vendedor * 1.10;
        $descripcion_vds = $row ? $row->descripcion : '';
        $orden           = $row ? intval($row->orden) : 0;

        // URLs externas
        $urls = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $tabla_urls
                 WHERE vds_id = %d AND product_id = %d
                 ORDER BY fecha_creado ASC",
                $vds_id,
                $product_id
            )
        );

        wp_send_json_success([
            'id'               => $product_id,
            'nombre'           => get_the_title($product_id),
            'imagen'           => $thumb,
            'proveedor_nombre' => $proveedor_nombre,

            'precio_vendedor' => $precio_vendedor,
            'precio_sugerido'  => $precio_sugerido,

            'descripcion_base' => $descripcion_base,

            'activo'           => $activo,
            'precio_vds'       => $precio_vds,
            'descripcion_vds'  => $descripcion_vds,
            'orden'            => $orden,

            'urls'             => $urls,
        ]);
    }
}

new Driftly_VDS_Ajax();
