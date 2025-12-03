<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Proveedor_AJAX {

    public function __construct() {
        add_action( 'wp_ajax_proveedor_guardar_producto', [ $this, 'guardar_producto' ] );
        add_action( 'wp_ajax_proveedor_solicitar_desactivacion', [ $this, 'solicitar_desactivacion' ] );
        add_action( 'wp_ajax_proveedor_test', [ $this, 'test' ] );
    }

    public function test() {
        wp_send_json_success([
            'message' => 'AJAX proveedor funcionando correctamente.'
        ]);
    }

    public function guardar_producto() {

        if ( ! is_user_logged_in() ) {
            wp_send_json_error([ 'msg' => 'Debes iniciar sesión.' ]);
        }

        $user = wp_get_current_user();

        if ( ! in_array( 'proveedor', $user->roles, true ) ) {
            wp_send_json_error([ 'msg' => 'No tienes permisos para operar como proveedor.' ]);
        }

        if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'guardar_producto_proveedor_nonce') ) {
            wp_send_json_error([ 'msg' => 'Nonce inválido.' ]);
        }

        $product_id        = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $titulo            = sanitize_text_field($_POST['titulo'] ?? '');
        $desc_corta        = sanitize_textarea_field($_POST['descripcion_corta'] ?? '');
        $desc_larga        = wp_kses_post($_POST['descripcion_larga'] ?? '');
        $precio_proveedor  = floatval($_POST['precio_proveedor'] ?? 0);
        $sku               = sanitize_text_field($_POST['sku_del_proveedor'] ?? '');
        $notas             = sanitize_textarea_field($_POST['notas_logisticas'] ?? '');
        $stock             = intval($_POST['stock'] ?? 0);
        $categoria         = intval($_POST['categoria'] ?? 0);

        if ( empty($titulo) ) {
            wp_send_json_error([ 'msg' => 'El título es obligatorio' ]);
        }

        if ( $precio_proveedor <= 0 ) {
            wp_send_json_error([ 'msg' => 'El precio del proveedor debe ser mayor a 0' ]);
        }

        $config = driftly_proveedor_get_config();

        $ganancia_sugerida = isset($config['ganancia_sugerida']) 
            ? floatval($config['ganancia_sugerida']) 
            : 20.0;

        // ============================
        //   REDONDEO A DECENAS
        // ============================
        $precio_vendedor = $precio_proveedor
                         + ($precio_proveedor * ($config['margen_proveedor'] / 100))
                         + $config['costo_envio_base'];

        $precio_sugerido = $precio_vendedor + ($precio_vendedor * ($ganancia_sugerida / 100));

        $precio_vendedor = round($precio_vendedor / 10) * 10;
        $precio_sugerido = round($precio_sugerido / 10) * 10;

        if ( $product_id > 0 ) {
            $product = wc_get_product( $product_id );
            if ( ! $product ) {
                wp_send_json_error([ 'msg' => 'Producto no encontrado.' ]);
            }
        } else {
            $product = new WC_Product_Simple();
        }

        $product->set_name( $titulo );
        $product->set_description( $desc_larga );
        $product->set_short_description( $desc_corta );
        $product->set_sku( $sku );
        $product->set_manage_stock( true );
        $product->set_stock_quantity( $stock );
        $product->set_status('publish');
        $product->set_category_ids([ $categoria ]);

        $product->set_regular_price( $precio_vendedor );
        $product->set_price( $precio_vendedor );

        if ( ! empty($_FILES['imagen_destacada']['name']) ) {
            $img_id = media_handle_upload( 'imagen_destacada', 0 );

            if ( is_wp_error($img_id ) ) {
                wp_send_json_error([ 'msg' => 'Error subiendo imagen destacada.' ]);
            }

            $product->set_image_id( $img_id );
        }

        $gallery_ids = [];

        if ( ! empty($_FILES['galeria']['name'][0]) ) {

            foreach ( $_FILES['galeria']['name'] as $index => $value ) {

                $tmp = [
                    'name'     => $_FILES['galeria']['name'][$index],
                    'type'     => $_FILES['galeria']['type'][$index],
                    'tmp_name' => $_FILES['galeria']['tmp_name'][$index],
                    'error'    => $_FILES['galeria']['error'][$index],
                    'size'     => $_FILES['galeria']['size'][$index],
                ];

                $_FILES['single_gal'] = $tmp;

                $img_id = media_handle_upload( 'single_gal', 0 );

                if ( ! is_wp_error( $img_id ) ) {
                    $gallery_ids[] = $img_id;
                }
            }
        }

        if ( ! empty($gallery_ids ) ) {
            $product->set_gallery_image_ids( $gallery_ids );
        }

        $product_id = $product->save();

        update_field( 'proveedor_id', $user->ID, $product_id );
        update_field( 'precio_proveedor', $precio_proveedor, $product_id );
        update_field( 'precio_vendedor', $precio_vendedor, $product_id );
        update_field( 'precio_sugerido', $precio_sugerido, $product_id );
        update_field( 'sku_del_proveedor', $sku, $product_id );
        update_field( 'notas_logisticas', $notas, $product_id );

        wp_send_json_success([
            'msg'            => $product_id ? 'Producto guardado correctamente.' : 'Producto creado.',
            'product_id'     => $product_id,
            'precio_vendedor'=> $precio_vendedor,
            'precio_sugerido'=> $precio_sugerido
        ]);
    }

    public function solicitar_desactivacion() {
        /* ... sin cambios ... */
    }

}

new Proveedor_AJAX();
