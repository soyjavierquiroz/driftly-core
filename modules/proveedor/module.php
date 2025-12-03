<?php
/**
 * Módulo PROVEEDOR
 * Maneja dashboard, productos del proveedor, pedidos, AJAX y configuración global.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
|--------------------------------------------------------------------------
| 1. Registrar el módulo
|--------------------------------------------------------------------------
*/
driftly_load_module( 'proveedor', DRIFTLY_CORE_PATH . 'modules/proveedor' );

/*
|--------------------------------------------------------------------------
| 2. Registrar archivos internos
|--------------------------------------------------------------------------
*/
driftly_register_module_files( 'proveedor', 'controllers', [
    'controllers/class-proveedor-dashboard-controller.php',
    'controllers/class-proveedor-productos-controller.php',
    'controllers/class-proveedor-producto-controller.php',
    'controllers/class-proveedor-producto-desactivar-controller.php', // 👈 NUEVO
    'controllers/class-proveedor-pedidos-controller.php',
]);

driftly_register_module_files( 'proveedor', 'ajax', [
    'ajax/ajax-proveedor.php',
]);

driftly_register_module_files( 'proveedor', 'other', [
    'includes/proveedor-config-helpers.php',
]);

/*
|--------------------------------------------------------------------------
| 3. Creación / actualización de tablas propias
|--------------------------------------------------------------------------
| - wp_driftly_proveedor_config
| - wp_driftly_proveedor_desactivaciones
|--------------------------------------------------------------------------
*/
function driftly_proveedor_ensure_tables() {
    global $wpdb;

    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    /*
    |--------------------------------------------------------------------------
    | 3.1 Tabla configuración proveedor
    |--------------------------------------------------------------------------
    */

    $tabla_config = $wpdb->prefix . 'driftly_proveedor_config';

    $sql_config = "CREATE TABLE $tabla_config (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        margen_proveedor DECIMAL(5,2) NOT NULL DEFAULT 10.00,
        costo_envio_base DECIMAL(10,2) NOT NULL DEFAULT 20.00,
        margen_vendedor DECIMAL(5,2) NOT NULL DEFAULT 10.00,
        ganancia_sugerida DECIMAL(5,2) NOT NULL DEFAULT 20.00,
        fecha_creado DATETIME NOT NULL,
        fecha_actualizado DATETIME NOT NULL,
        PRIMARY KEY (id)
    ) $charset;";

    dbDelta( $sql_config );

    // Insert default row if not exists
    $exists = $wpdb->get_var( "SELECT COUNT(*) FROM $tabla_config WHERE id = 1" );
    if ( ! $exists ) {
        $wpdb->insert( $tabla_config, [
            'id'                => 1,
            'margen_proveedor'  => 10.00,
            'costo_envio_base'  => 20.00,
            'margen_vendedor'   => 10.00,
            'ganancia_sugerida' => 20.00,
            'fecha_creado'      => current_time( 'mysql' ),
            'fecha_actualizado' => current_time( 'mysql' ),
        ] );
    }

    /*
    |--------------------------------------------------------------------------
    | 3.2 Tabla solicitudes desactivación
    |--------------------------------------------------------------------------
    */

    $tabla_desact = $wpdb->prefix . 'driftly_proveedor_desactivaciones';

    $sql_desact = "CREATE TABLE $tabla_desact (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        product_id BIGINT(20) UNSIGNED NOT NULL,
        proveedor_id BIGINT(20) UNSIGNED NOT NULL,
        motivo TEXT NOT NULL,
        desactivar_en DATE NULL,
        estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
        fecha_solicitud DATETIME NOT NULL,
        fecha_respuesta DATETIME NULL,
        admin_comentario TEXT NULL,
        PRIMARY KEY (id),
        KEY prod_idx (product_id),
        KEY prov_idx (proveedor_id),
        KEY estado_idx (estado)
    ) $charset;";

    dbDelta( $sql_desact );
}

add_action( 'plugins_loaded', 'driftly_proveedor_ensure_tables', 5 );
