<?php
if (!defined('ABSPATH')) exit;

class VDS_Producto_Controller extends Driftly_Controller {

    public function handle($product_id) {

        $vds_id = get_current_user_id();
        $product_id = intval($product_id);

        if (!$product_id) {
            wp_die('Producto inválido.');
        }

        $post = get_post($product_id);

        if (!$post || $post->post_type !== 'product') {
            wp_die('Producto no encontrado.');
        }

        // ---------------------------------------------------
        // VALIDAR QUE EL PRODUCTO EXISTA EN LA CONFIG VDS
        // ---------------------------------------------------
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

        if (!$row) {
            wp_die('Este producto no pertenece a tu catálogo.');
        }

        // --------------------------------------------
        // DATOS BASE DE WOOCOMMERCE
        // --------------------------------------------
        $precio_mayorista = get_field('precio_mayorista', $product_id);
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
        $descripcion_base = wpautop(wp_kses_post($descripcion_base));

        // --------------------------------------------
        // DATOS PERSONALIZADOS DEL VDS
        // --------------------------------------------
        $precio_vds      = (float) $row->precio_final;
        $descripcion_vds = $row->descripcion;
        $orden           = intval($row->orden);

        // --------------------------------------------
        // URLs externas
        // --------------------------------------------
        $urls = $this->get_product_urls($product_id, $vds_id);

        // --------------------------------------------
        // PASAR A LA VISTA
        // --------------------------------------------
        $data = [
            'titulo'          => 'Editar producto',
            'subtitulo'       => $post->post_title,
            'product_id'      => $product_id,
            'nombre'          => $post->post_title,
            'imagen'          => $thumb,
            'descripcion_base'=> $descripcion_base,
            'proveedor_nombre'=> $proveedor_nombre,

            'precio_mayorista'=> $precio_mayorista,
            'precio_sugerido' => $precio_sugerido,

            'precio_vds'      => $precio_vds,
            'descripcion_vds' => $descripcion_vds,
            'orden'           => $orden,

            'urls'            => $urls,
        ];

        $this->render('vds-producto', $data);
    }

    /**
     * Obtener URLs externas del producto
     */
    private function get_product_urls($product_id, $vds_id) {
        global $wpdb;
        $tabla_urls = $wpdb->prefix . 'driftly_vds_urls';

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $tabla_urls 
                 WHERE vds_id = %d AND product_id = %d
                 ORDER BY fecha_creado ASC",
                $vds_id,
                $product_id
            )
        );

        $list = [];

        foreach ($rows as $r) {
            $list[] = [
                'id'     => $r->id,
                'url'    => $r->url,
                'label'  => $r->label,
                'fecha'  => $r->fecha_creado,
                'es_principal' => $r->es_principal,
            ];
        }

        return $list;
    }
}
