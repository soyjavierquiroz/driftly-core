<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class VDS_Catalogo_Controller extends Driftly_Controller {

    public function handle( $param ) {

        $current_vds_id = get_current_user_id();

        global $wpdb;
        $tabla = $wpdb->prefix . 'driftly_vds_productos';

        // -----------------------------
        // Filtros desde la URL (?s, ?cat, ?proveedor, ?paged)
        // -----------------------------
        $search    = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
        $cat       = isset( $_GET['cat'] ) ? intval( $_GET['cat'] ) : 0;
        $prov_flt  = isset( $_GET['proveedor'] ) ? intval( $_GET['proveedor'] ) : 0;
        $paged     = isset( $_GET['paged'] ) ? max( 1, intval( $_GET['paged'] ) ) : 1;
        $per_page  = 12;

        // -----------------------------
        // Query de productos WooCommerce
        // -----------------------------
        $args = [
            'post_type'      => 'product',
            'posts_per_page' => $per_page,
            'post_status'    => 'publish',
            'paged'          => $paged,
        ];

        if ( $search ) {
            $args['s'] = $search;
        }

        $tax_query = [];
        if ( $cat ) {
            $tax_query[] = [
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $cat,
            ];
        }
        if ( ! empty( $tax_query ) ) {
            $args['tax_query'] = $tax_query;
        }

        $meta_query = [];
        if ( $prov_flt ) {
            $meta_query[] = [
                'key'     => 'proveedor_id',
                'value'   => $prov_flt,
                'compare' => '=',
            ];
        }
        if ( ! empty( $meta_query ) ) {
            $args['meta_query'] = $meta_query;
        }

        $query = new WP_Query( $args );

        $productos = [];

        foreach ( $query->posts as $p ) {
            $product_id = $p->ID;

            // Datos ACF del producto
            $precio_mayorista = get_field( 'precio_mayorista', $product_id );
            $precio_sugerido  = get_field( 'precio_sugerido', $product_id );
            $proveedor_id     = get_field( 'proveedor_id', $product_id );

            $proveedor_nombre = $proveedor_id
                ? get_user_meta( $proveedor_id, 'nombre_comercial', true )
                : 'Sin proveedor';

            // Imagen
            $thumb = get_the_post_thumbnail_url( $product_id, 'medium' );
            if ( ! $thumb && function_exists( 'wc_placeholder_img_src' ) ) {
                $thumb = wc_placeholder_img_src();
            }

            // Config del VDS (tabla personalizada)
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $tabla WHERE vds_id = %d AND product_id = %d LIMIT 1",
                    $current_vds_id,
                    $product_id
                )
            );

            $activo          = $row ? (bool) $row->activo : false;
            $precio_vds      = $row ? (float) $row->precio_final : (float) $precio_mayorista * 1.10;
            $descripcion_vds = $row ? $row->descripcion : '';
            $orden           = $row ? intval( $row->orden ) : 0;

            $productos[] = [
                'id'               => $product_id,
                'nombre'           => $p->post_title,
                'imagen'           => $thumb,
                'proveedor_nombre' => $proveedor_nombre,
                'proveedor_id'     => $proveedor_id,
                'precio_mayorista' => $precio_mayorista,
                'precio_sugerido'  => $precio_sugerido,
                'precio_vds'       => $precio_vds,
                'descripcion_vds'  => $descripcion_vds,
                'orden'            => $orden,
                'activo'           => $activo,
            ];
        }

        wp_reset_postdata();

        // Filtros: categorías y proveedores
        $categorias = get_terms(
            [
                'taxonomy'   => 'product_cat',
                'hide_empty' => false,
            ]
        );

        $proveedores = get_users(
            [
                'role'    => 'proveedor',
                'orderby' => 'display_name',
                'number'  => 200,
            ]
        );

        $data = [
            'titulo'      => 'Catálogo maestro',
            'subtitulo'   => 'Selecciona productos del marketplace para agregarlos a tu tienda.',
            'productos'   => $productos,
            'categorias'  => $categorias,
            'proveedores' => $proveedores,
            'filtros'     => [
                'search'    => $search,
                'cat'       => $cat,
                'proveedor' => $prov_flt,
            ],
            'paginacion'  => [
                'current' => $paged,
                'total'   => max( 1, (int) $query->max_num_pages ),
            ],
        ];

        $this->render( 'vds-catalogo', $data );
    }
}
