<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Proveedor_Productos_Controller extends Driftly_Controller {

    public function handle( $param = null ) {

        // =============================================================
        // 1. Validar rol proveedor
        // =============================================================
        if ( ! is_user_logged_in() ) {
            wp_die( 'Debes iniciar sesión.' );
        }

        $user = wp_get_current_user();

        if ( ! in_array( 'proveedor', $user->roles, true ) ) {
            if ( ! in_array( 'administrator', $user->roles, true ) ) {
                wp_die( 'No tienes permisos para ver esta sección.' );
            }
        }

        $proveedor_id = $user->ID;

        global $wpdb;
        $tabla_desact = $wpdb->prefix . 'driftly_proveedor_desactivaciones';

        // =============================================================
        // 2. Obtener productos WooCommerce de este proveedor
        // =============================================================
        $args = [
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'post_status'    => [ 'publish', 'pending', 'draft' ],
            'meta_query'     => [
                [
                    'key'     => 'proveedor_id',
                    'value'   => $proveedor_id,
                    'compare' => '=',
                ],
            ],
            'orderby'  => 'modified',
            'order'    => 'DESC',
        ];

        $query     = new WP_Query( $args );
        $productos = [];

        if ( $query->have_posts() ) {

            while ( $query->have_posts() ) {
                $query->the_post();

                $product_id = get_the_ID();
                $product    = wc_get_product( $product_id );

                if ( ! $product ) {
                    continue;
                }

                // Imagen
                $thumb = get_the_post_thumbnail_url( $product_id, 'medium' );
                if ( ! $thumb && function_exists( 'wc_placeholder_img_src' ) ) {
                    $thumb = wc_placeholder_img_src();
                }

                // SKU OFICIAL WooCommerce
                $sku_wc = $product->get_sku();

                // Metas proveedor
                $precio_proveedor = get_post_meta( $product_id, 'precio_proveedor', true );
                $notas_logisticas = get_post_meta( $product_id, 'notas_logisticas', true );

                // Stock y estado
                $stock  = $product->get_stock_quantity();
                $estado = $product->get_status(); // publish, pending, draft

                $es_publicado = ( $estado === 'publish' );
                $es_pendiente = ( $estado === 'pending' );
                $es_borrador  = ( $estado === 'draft' );

                // Categorías
                $terms = get_the_terms( $product_id, 'product_cat' );
                $categorias = ( ! empty( $terms ) && ! is_wp_error( $terms ) )
                    ? implode( ', ', wp_list_pluck( $terms, 'name' ) )
                    : '-';

                // Fecha
                $fecha_actualizado = get_post_modified_time( 'd/m/Y H:i', false, $product_id, true );

                // ---------------------------------------------------------
                // Solicitud de desactivación
                // ---------------------------------------------------------
                $solicitud = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM $tabla_desact 
                         WHERE product_id = %d AND proveedor_id = %d
                         ORDER BY fecha_solicitud DESC LIMIT 1",
                        $product_id,
                        $proveedor_id
                    )
                );

                $solicitud_pendiente     = $solicitud && $solicitud->estado === 'pendiente';
                $solicitud_desactivar_en = (
                    $solicitud && $solicitud->desactivar_en
                ) ? date_i18n( 'd/m/Y', strtotime( $solicitud->desactivar_en ) ) : null;

                $productos[] = [
                    'id'                 => $product_id,
                    'nombre'             => get_the_title(),
                    'imagen'             => $thumb,
                    'sku'                => $sku_wc, // 🔥 Ahora usamos SKU WooCommerce
                    'precio_proveedor'   => $precio_proveedor,
                    'stock'              => $stock,
                    'estado'             => $estado,

                    'es_publicado'       => $es_publicado,
                    'es_pendiente'       => $es_pendiente,
                    'es_borrador'        => $es_borrador,

                    'categoria'          => $categorias,
                    'fecha_actualizado'  => $fecha_actualizado,
                    'notas_logisticas'   => $notas_logisticas,

                    'solicitud_pendiente' => $solicitud_pendiente,
                    'solicitud_fecha'     => $solicitud_desactivar_en,

                    'estado_label'       => ucfirst( $estado ),
                ];
            }

            wp_reset_postdata();
        }

        // =============================================================
        // 3. Render
        // =============================================================
        $data = [
            'title'     => 'Mis Productos',
            'role'      => driftly_get_role_label(),
            'productos' => $productos,
        ];

        $this->render( 'proveedor-productos', $data );
    }
}
