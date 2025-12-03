<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Proveedor_Producto_Controller extends Driftly_Controller {

    public function handle( $product_id = null ) {

        // =========================================
        // 0. Validación de sesión y rol
        // =========================================
        if ( ! is_user_logged_in() ) {
            wp_die( 'Debes iniciar sesión.' );
        }

        $user = wp_get_current_user();

        if ( ! in_array( 'proveedor', $user->roles, true ) && ! in_array( 'administrator', $user->roles, true ) ) {
            wp_die( 'No tienes permisos para ver esta sección.' );
        }

        // =========================================
        // 1. Si es POST → procesar guardado
        // =========================================
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
            $this->procesar_guardado_post( $user );
        }

        // =========================================
        // 2. CREAR o EDITAR (GET)
        // =========================================
        $es_nuevo = ( $product_id === 'nuevo' );

        if ( $es_nuevo ) {

            $data = [
                'title'       => 'Crear nuevo producto',
                'is_new'      => true,
                'product_id'  => null,
                'product'     => null,
                'acf_fields'  => [],
            ];

        } else {

            if ( empty( $product_id ) || ! is_numeric( $product_id ) ) {
                wp_die( 'Producto no especificado.' );
            }

            $product_id = intval( $product_id );
            $product    = wc_get_product( $product_id );

            if ( ! $product ) {
                wp_die( 'Producto no encontrado.' );
            }

            $acf_fields = [
                'precio_proveedor'   => get_post_meta( $product_id, 'precio_proveedor', true ),
                'precio_vendedor'    => get_post_meta( $product_id, 'precio_vendedor', true ),
                'precio_sugerido'    => get_post_meta( $product_id, 'precio_sugerido', true ),
                'notas_logisticas'   => get_post_meta( $product_id, 'notas_logisticas', true ),
                'proveedor_id'       => get_post_meta( $product_id, 'proveedor_id', true ),
            ];

            $data = [
                'title'       => 'Editar producto',
                'is_new'      => false,
                'product_id'  => $product_id,
                'product'     => $product,
                'acf_fields'  => $acf_fields,
            ];
        }

        $this->render( 'proveedor-producto', $data );
    }

    /**
     * Genera SKU según la regla:
     *   P{proveedorID padded}-{productID padded}
     *   Ej: P012-000345
     */
    private function generar_sku_oficial( $proveedor_id, $product_id ) {
        $proveedor_id = (int) $proveedor_id;
        $product_id   = (int) $product_id;

        return sprintf(
            'P%03d-%06d',
            $proveedor_id,
            $product_id
        );
    }

    /**
     * Procesa el POST del formulario (crear/editar).
     */
    private function procesar_guardado_post( $user ) {

        // =========================================
        // 1. NONCE
        // =========================================
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'guardar_producto_proveedor_nonce' ) ) {
            wp_die( 'Nonce inválido.' );
        }

        // =========================================
        // 2. Sanitización
        // =========================================
        $product_id        = intval( $_POST['product_id'] ?? 0 );
        $titulo            = sanitize_text_field( $_POST['titulo'] ?? '' );
        $desc_corta        = sanitize_textarea_field( $_POST['descripcion_corta'] ?? '' );
        $desc_larga        = wp_kses_post( $_POST['descripcion_larga'] ?? '' );
        $precio_proveedor  = floatval( $_POST['precio_proveedor'] ?? 0 );
        $notas             = sanitize_textarea_field( $_POST['notas_logisticas'] ?? '' );
        $stock             = intval( $_POST['stock'] ?? 0 );
        $categoria         = intval( $_POST['categoria'] ?? 0 );

        if ( empty( $titulo ) ) {
            wp_die( 'El título es obligatorio.' );
        }

        if ( $precio_proveedor <= 0 ) {
            wp_die( 'El precio del proveedor debe ser mayor a 0.' );
        }

        // =========================================
        // 3. Config proveedor (para márgenes)
        // =========================================
        $config = driftly_proveedor_get_config();
        $ganancia_sugerida = floatval( $config['ganancia_sugerida'] ?? 20 );

        // =========================================
        // 4. Calcular precios + redondear a decenas
        // =========================================
        $precio_vendedor = $precio_proveedor
                         + ( $precio_proveedor * ( $config['margen_proveedor'] / 100 ) )
                         + $config['costo_envio_base'];

        $precio_sugerido = $precio_vendedor
                         + ( $precio_vendedor * ( $ganancia_sugerida / 100 ) );

        // Redondeo a decenas
        $precio_vendedor = round( $precio_vendedor / 10 ) * 10;
        $precio_sugerido = round( $precio_sugerido / 10 ) * 10;

        // =========================================
        // 5. Crear o cargar producto WooCommerce
        // =========================================
        if ( $product_id > 0 ) {

            $product = wc_get_product( $product_id );
            if ( ! $product ) {
                wp_die( 'Producto no encontrado.' );
            }

            $status_anterior = $product->get_status();

        } else {
            $product = new WC_Product_Simple();
            $status_anterior = 'pending'; // nuevos → pending
        }

        // =========================================
        // 6. Datos generales (sin SKU aún)
        // =========================================
        $product->set_name( $titulo );
        $product->set_description( $desc_larga );
        $product->set_short_description( $desc_corta );
        $product->set_manage_stock( true );
        $product->set_stock_quantity( $stock );

        if ( $categoria ) {
            $product->set_category_ids( [ $categoria ] );
        }

        // Precios (sin decimales)
        $product->set_regular_price( $precio_vendedor );
        $product->set_price( $precio_vendedor );

        // STATUS CORRECTO
        if ( $product_id > 0 && $status_anterior === 'publish' ) {
            // Producto ya aprobado → se mantiene publish
            $product->set_status( 'publish' );
        } else {
            // Nuevos o que aún estaban pending → pending
            $product->set_status( 'pending' );
        }

        // =========================================
        // 7. Manejo de imágenes
        // =========================================
        if ( ! function_exists( 'media_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }

        // Imagen destacada
        if ( ! empty( $_FILES['imagen_destacada']['name'] ) ) {
            $img_id = media_handle_upload( 'imagen_destacada', 0 );
            if ( ! is_wp_error( $img_id ) ) {
                $product->set_image_id( $img_id );
            }
        }

        // Galería
        if ( ! empty( $_FILES['galeria']['name'][0] ) ) {

            $gallery_ids = [];

            foreach ( $_FILES['galeria']['name'] as $i => $name ) {
                $_FILES['tmp_gal'] = [
                    'name'     => $_FILES['galeria']['name'][ $i ],
                    'type'     => $_FILES['galeria']['type'][ $i ],
                    'tmp_name' => $_FILES['galeria']['tmp_name'][ $i ],
                    'error'    => $_FILES['galeria']['error'][ $i ],
                    'size'     => $_FILES['galeria']['size'][ $i ],
                ];

                $id = media_handle_upload( 'tmp_gal', 0 );
                if ( ! is_wp_error( $id ) ) {
                    $gallery_ids[] = $id;
                }
            }

            if ( ! empty( $gallery_ids ) ) {
                $product->set_gallery_image_ids( $gallery_ids );
            }
        }

        // =========================================
        // 8. Guardar producto (primera vez, aún sin SKU)
        // =========================================
        $product_id = $product->save();

        // Asegurar que el objeto tenga el ID
        if ( method_exists( $product, 'set_id' ) && ! $product->get_id() ) {
            $product->set_id( $product_id );
        }

        // =========================================
        // 9. Determinar proveedor_id para SKU
        //    - Si ya existe meta proveedor_id → usarlo
        //    - Si no, usar usuario actual y guardarlo
        // =========================================
        $proveedor_id_meta = intval( get_post_meta( $product_id, 'proveedor_id', true ) );

        if ( $proveedor_id_meta > 0 ) {
            $proveedor_id_para_sku = $proveedor_id_meta;
        } else {
            $proveedor_id_para_sku = (int) $user->ID;
            update_post_meta( $product_id, 'proveedor_id', $proveedor_id_para_sku );
        }

        // =========================================
        // 10. Generar SKU oficial si el producto no tiene uno
        // =========================================
        $sku_actual = $product->get_sku();

        if ( ! $sku_actual ) {
            $sku_nuevo = $this->generar_sku_oficial( $proveedor_id_para_sku, $product_id );
            $product->set_sku( $sku_nuevo );
            $product->save(); // guardar solo el cambio de SKU
        }

        // =========================================
        // 11. Guardar metas de precios / notas
        // =========================================
        update_post_meta( $product_id, 'precio_proveedor',  $precio_proveedor );
        update_post_meta( $product_id, 'precio_vendedor',   $precio_vendedor );
        update_post_meta( $product_id, 'precio_sugerido',   $precio_sugerido );
        update_post_meta( $product_id, 'notas_logisticas',  $notas );

        // IMPORTANTE:
        // Ya NO guardamos 'sku_del_proveedor' porque el SKU oficial es el de WooCommerce.

        // =========================================
        // 12. Redirigir al listado
        // =========================================
        wp_safe_redirect( home_url( '/proveedor/productos?saved=1' ) );
        exit;
    }
}
