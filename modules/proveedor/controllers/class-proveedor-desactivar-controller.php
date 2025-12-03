<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Proveedor_Producto_Desactivar_Controller extends Driftly_Controller {

    public function handle( $product_id = null ) {

        // 1. Verificar login + rol
        if ( ! is_user_logged_in() ) {
            wp_die( 'Debes iniciar sesión.' );
        }

        $user = wp_get_current_user();

        if ( ! in_array( 'proveedor', $user->roles, true ) && ! in_array( 'administrator', $user->roles, true ) ) {
            wp_die( 'No tienes permisos para operar como proveedor.' );
        }

        // 2. Validar ID de producto
        if ( empty( $product_id ) || ! is_numeric( $product_id ) ) {
            wp_die( 'Producto no especificado.' );
        }

        $product_id = intval( $product_id );
        $product    = wc_get_product( $product_id );

        if ( ! $product ) {
            wp_die( 'Producto no encontrado.' );
        }

        $success       = false;
        $error         = '';
        $desactivar_en = null;

        // 3. Procesar POST (envío normal del formulario)
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {

            if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'proveedor_desactivar_producto' ) ) {
                $error = 'Nonce inválido. Recarga la página e inténtalo de nuevo.';
            } else {

                $motivo       = isset( $_POST['motivo'] ) ? sanitize_textarea_field( $_POST['motivo'] ) : '';
                $tipo         = isset( $_POST['tipo_desactivacion'] ) ? sanitize_text_field( $_POST['tipo_desactivacion'] ) : 'inmediato';
                $fecha_custom = isset( $_POST['fecha_custom'] ) ? sanitize_text_field( $_POST['fecha_custom'] ) : '';

                if ( empty( $motivo ) ) {
                    $error = 'Debes indicar un motivo para la desactivación.';
                } else {

                    // Calcular fecha de desactivación
                    $timestamp_hoy = current_time( 'timestamp' );
                    $fecha_en      = null;

                    switch ( $tipo ) {

                        case 'inmediato':
                            $fecha_en = date( 'Y-m-d', $timestamp_hoy );
                            break;

                        case '1':
                        case '3':
                        case '7':
                            $dias     = intval( $tipo );
                            $fecha_en = date( 'Y-m-d', strtotime( "+$dias days", $timestamp_hoy ) );
                            break;

                        case 'custom':
                            if ( ! empty( $fecha_custom ) ) {
                                $fecha_en = $fecha_custom;
                            }
                            break;
                    }

                    global $wpdb;
                    $tabla_desact = $wpdb->prefix . 'driftly_proveedor_desactivaciones';

                    $insert_ok = $wpdb->insert(
                        $tabla_desact,
                        [
                            'product_id'       => $product_id,
                            'proveedor_id'     => $user->ID,
                            'motivo'           => $motivo,
                            'desactivar_en'    => $fecha_en,
                            'estado'           => 'pendiente',
                            'fecha_solicitud'  => current_time( 'mysql' ),
                            'fecha_respuesta'  => null,
                            'admin_comentario' => null,
                        ],
                        [
                            '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s',
                        ]
                    );

                    if ( ! $insert_ok ) {
                        $error = 'No se pudo registrar la solicitud. Intenta nuevamente.';
                    } else {
                        $success       = true;
                        $desactivar_en = $fecha_en;
                    }
                }
            }
        }

        // Datos básicos del producto (ACF, stock, categoría)
        $precio_proveedor = get_post_meta( $product_id, 'precio_proveedor', true );
        $sku_proveedor    = get_post_meta( $product_id, 'sku_del_proveedor', true );
        $stock            = $product->get_stock_quantity();
        $thumb            = get_the_post_thumbnail_url( $product_id, 'medium' );

        $terms = get_the_terms( $product_id, 'product_cat' );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $categorias = implode( ', ', wp_list_pluck( $terms, 'name' ) );
        } else {
            $categorias = '-';
        }

        $data = [
            'title'          => 'Solicitar desactivación',
            'product_id'     => $product_id,
            'product'        => $product,
            'precio_proveedor'=> $precio_proveedor,
            'sku_proveedor'  => $sku_proveedor,
            'stock'          => $stock,
            'categoria'      => $categorias,
            'thumb'          => $thumb,
            'success'        => $success,
            'error'          => $error,
            'desactivar_en'  => $desactivar_en,
        ];

        $this->render( 'proveedor-producto-desactivar', $data );
    }
}
