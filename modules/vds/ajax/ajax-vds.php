<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Driftly_VDS_Ajax {

    public function __construct() {
        add_action( 'wp_ajax_driftly_toggle_product_vds', [ $this, 'toggle_product' ] );
        add_action( 'wp_ajax_driftly_update_product_vds', [ $this, 'update_product' ] );
        add_action( 'wp_ajax_driftly_get_product_details', [ $this, 'get_product_details' ] );

        // 👇 NUEVO: guardar configuración de la tienda VDS
        add_action( 'wp_ajax_driftly_save_vds_configuracion', [ $this, 'save_vds_configuracion' ] );
    }

    /**
     * ACTIVAR / DESACTIVAR producto para este VDS.
     */
    public function toggle_product() {

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'No autorizado' ] );
        }

        $vds_id     = get_current_user_id();
        $product_id = intval( $_POST['producto_id'] ?? 0 );

        if ( ! $product_id ) {
            wp_send_json_error( [ 'message' => 'Producto inválido' ] );
        }

        global $wpdb;
        $tabla = $wpdb->prefix . 'driftly_vds_productos';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tabla WHERE vds_id = %d AND product_id = %d LIMIT 1",
                $vds_id,
                $product_id
            )
        );

        if ( $row ) {

            $nuevo_estado = $row->activo ? 0 : 1;

            $wpdb->update(
                $tabla,
                [
                    'activo'            => $nuevo_estado,
                    'fecha_actualizado' => current_time( 'mysql' ),
                ],
                [
                    'vds_id'    => $vds_id,
                    'product_id'=> $product_id,
                ],
                [ '%d', '%s' ],
                [ '%d', '%d' ]
            );

            wp_send_json_success(
                [
                    'activo' => $nuevo_estado,
                ]
            );

        } else {

            $precio_vendedor = get_field( 'precio_vendedor', $product_id );
            $precio_inicial   = floatval( $precio_vendedor ) * 1.10;

            $wpdb->insert(
                $tabla,
                [
                    'vds_id'           => $vds_id,
                    'product_id'       => $product_id,
                    'precio_final'     => $precio_inicial,
                    'descripcion'      => '',
                    'orden'            => 0,
                    'activo'           => 1,
                    'fecha_creado'     => current_time( 'mysql' ),
                    'fecha_actualizado'=> current_time( 'mysql' ),
                ]
            );

            wp_send_json_success(
                [
                    'activo' => 1,
                ]
            );
        }
    }

    /**
     * ACTUALIZAR precio_final, descripción y orden.
     */
    public function update_product() {

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'No autorizado' ] );
        }

        $vds_id     = get_current_user_id();
        $product_id = intval( $_POST['product_id'] ?? 0 );

        $precio = floatval( $_POST['precio'] ?? 0 );
        $orden  = intval( $_POST['orden'] ?? 0 );
        $desc   = sanitize_textarea_field( $_POST['descripcion'] ?? '' );

        global $wpdb;
        $tabla = $wpdb->prefix . 'driftly_vds_productos';

        $wpdb->update(
            $tabla,
            [
                'precio_final'      => $precio,
                'descripcion'       => $desc,
                'orden'             => $orden,
                'fecha_actualizado' => current_time( 'mysql' ),
            ],
            [
                'vds_id'    => $vds_id,
                'product_id'=> $product_id,
            ],
            [ '%f', '%s', '%d', '%s' ],
            [ '%d', '%d' ]
        );

        wp_send_json_success( [ 'ok' => true ] );
    }

    /**
     * OBTENER detalle completo de un producto para el modal.
     */
    public function get_product_details() {

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'No autorizado' ] );
        }

        $vds_id     = get_current_user_id();
        $product_id = intval( $_POST['product_id'] ?? 0 );

        if ( ! $product_id ) {
            wp_send_json_error( [ 'message' => 'Producto inválido' ] );
        }

        $post = get_post( $product_id );

        if ( ! $post || 'product' !== $post->post_type ) {
            wp_send_json_error( [ 'message' => 'Producto no encontrado' ] );
        }

        global $wpdb;
        $tabla = $wpdb->prefix . 'driftly_vds_productos';

        // Datos base Woo
        $precio_vendedor = get_field( 'precio_vendedor', $product_id );
        $precio_sugerido  = get_field( 'precio_sugerido', $product_id );
        $proveedor_id     = get_field( 'proveedor_id', $product_id );

        $proveedor_nombre = $proveedor_id
            ? get_user_meta( $proveedor_id, 'nombre_comercial', true )
            : 'Sin proveedor';

        $thumb = get_the_post_thumbnail_url( $product_id, 'large' );
        if ( ! $thumb && function_exists( 'wc_placeholder_img_src' ) ) {
            $thumb = wc_placeholder_img_src();
        }

        $descripcion_base = $post->post_excerpt ?: $post->post_content;
        $descripcion_base = wp_kses_post( wpautop( $descripcion_base ) );

        // Config del VDS
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $tabla WHERE vds_id = %d AND product_id = %d LIMIT 1",
                $vds_id,
                $product_id
            )
        );

        $activo          = $row ? (bool) $row->activo : false;
        $precio_vds      = $row ? (float) $row->precio_final : (float) $precio_vendedor * 1.10;
        $descripcion_vds = $row ? $row->descripcion : '';
        $orden           = $row ? intval( $row->orden ) : 0;

        wp_send_json_success(
            [
                'id'               => $product_id,
                'nombre'           => get_the_title( $product_id ),
                'imagen'           => $thumb,
                'proveedor_nombre' => $proveedor_nombre,
                'precio_vendedor'  => $precio_vendedor,
                'precio_sugerido'  => $precio_sugerido,
                'descripcion_base' => $descripcion_base,
                'activo'           => $activo,
                'precio_vds'       => $precio_vds,
                'descripcion_vds'  => $descripcion_vds,
                'orden'            => $orden,
            ]
        );
    }

    /**
     * Guardar configuración de la tienda VDS (ACF en el usuario).
     */
    public function save_vds_configuracion() {

        if ( ! is_user_logged_in() ) {
            wp_send_json_error( [ 'message' => 'No autorizado' ] );
        }

        check_ajax_referer( 'driftly_vds_config', 'nonce' );

        if ( ! function_exists( 'update_field' ) ) {
            wp_send_json_error( [ 'message' => 'ACF no está activo.' ] );
        }

        $vds_id  = get_current_user_id();
        $acf_key = 'user_' . $vds_id;

        // Campos básicos
        $nombre_tienda = sanitize_text_field( $_POST['nombre_de_la_tienda'] ?? '' );
        $color         = sanitize_hex_color( $_POST['color_primario'] ?? '' );
        $dominio       = sanitize_text_field( $_POST['dominio'] ?? '' );
        $slug_tienda   = sanitize_title( $_POST['slug_de_la_tienda'] ?? '' );
        $whatsapp      = sanitize_text_field( $_POST['whatsapp'] ?? '' );

        // Scripts / tracking
        $pixel_meta   = sanitize_text_field( $_POST['pixel_meta'] ?? '' );
        $gtm_id       = sanitize_text_field( $_POST['google_tag_manager'] ?? '' );
        $header_raw   = isset( $_POST['header_scripts'] ) ? wp_unslash( $_POST['header_scripts'] ) : '';
        $footer_raw   = isset( $_POST['footer_scripts'] ) ? wp_unslash( $_POST['footer_scripts'] ) : '';

        if ( ! current_user_can( 'unfiltered_html' ) ) {
            $header_raw = wp_kses_post( $header_raw );
            $footer_raw = wp_kses_post( $footer_raw );
        }

        // Guardar en ACF (user meta)
        update_field( 'nombre_de_la_tienda', $nombre_tienda, $acf_key );
        update_field( 'color_primario',      $color,         $acf_key );
        update_field( 'dominio',             $dominio,       $acf_key );
        update_field( 'slug_de_la_tienda',   $slug_tienda,   $acf_key );
        update_field( 'whatsapp',            $whatsapp,      $acf_key );

        update_field( 'pixel_meta',          $pixel_meta,    $acf_key );
        update_field( 'google_tag_manager',  $gtm_id,        $acf_key );
        update_field( 'header_scripts',      $header_raw,    $acf_key );
        update_field( 'footer_scripts',      $footer_raw,    $acf_key );

        wp_send_json_success( [
            'message' => 'Configuración guardada correctamente.',
        ] );
    }
}

new Driftly_VDS_Ajax();
