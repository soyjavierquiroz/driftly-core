<?php
/**
 * Plugin Name: Driftly Core
 * Description: Núcleo del sistema Driftly (router, controladores y vistas del Backoffice).
 * Author: Driftly
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
|--------------------------------------------------------------------------
| CONSTANTES
|--------------------------------------------------------------------------
*/
define( 'DRIFTLY_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'DRIFTLY_CORE_URL',  plugin_dir_url( __FILE__ ) );

// Loader modular
require_once DRIFTLY_CORE_PATH . 'core/modules-loader.php';

/*
|--------------------------------------------------------------------------
| HELPERS GLOBALES
|--------------------------------------------------------------------------
*/

/**
 * Rol lógico Driftly del usuario actual.
 */
if ( ! function_exists( 'driftly_get_user_role' ) ) {

    function driftly_get_user_role() {

        if ( ! is_user_logged_in() ) {
            return null;
        }

        $user = wp_get_current_user();

        if ( empty( $user->roles ) || ! is_array( $user->roles ) ) {
            return null;
        }

        $roles = $user->roles;

        if ( in_array( 'administrator', $roles, true ) ) {
            return 'admin';
        }
        if ( in_array( 'vds', $roles, true ) ) {
            return 'vds';
        }
        if ( in_array( 'proveedor', $roles, true ) ) {
            return 'proveedor';
        }

        return $roles[0];
    }
}

/**
 * Retorna el label legible del rol Driftly
 */
if ( ! function_exists( 'driftly_get_role_label' ) ) {

    function driftly_get_role_label() {

        $role = driftly_get_user_role();

        switch ( $role ) {

            case 'admin':
                return 'Admin Driftly';

            case 'vds':
                return 'Vendedor Digital';

            case 'proveedor':
                return 'Proveedor';

            default:
                return ucfirst( $role );
        }
    }
}

/**
 * Retorna el nombre corto del rol (estética)
 */
if ( ! function_exists( 'driftly_get_role_name' ) ) {

    function driftly_get_role_name() {

        $role = driftly_get_user_role();

        switch ( $role ) {

            case 'admin':
                return 'Admin';

            case 'vds':
                return 'Vendedor';

            case 'proveedor':
                return 'Proveedor';

            default:
                return ucfirst( $role );
        }
    }
}

/*
|--------------------------------------------------------------------------
| AUTOLOAD DE ARCHIVOS INTERNOS
|--------------------------------------------------------------------------
*/

// 1. Cargar el controlador base ANTES que los módulos
require_once DRIFTLY_CORE_PATH . 'controllers/class-driftly-controller.php';

// 2. Activar módulo VDS
require_once DRIFTLY_CORE_PATH . 'modules/vds/module.php';

// 3. Inicializar módulos muy temprano
add_action( 'plugins_loaded', function() {
    driftly_boot_modules();
}, 1 );

// 4. Router principal
require_once DRIFTLY_CORE_PATH . 'driftly-router.php';


/*
|--------------------------------------------------------------------------
| ACTIVACIÓN / DESACTIVACIÓN + CREACIÓN DE TABLA VDS
|--------------------------------------------------------------------------
*/

/**
 * Crea la tabla driftly_vds_productos si no existe
 */
function driftly_core_activate() {

    global $wpdb;

    $tabla   = $wpdb->prefix . 'driftly_vds_productos';
    $charset = $wpdb->get_charset_collate();

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql = "CREATE TABLE IF NOT EXISTS $tabla (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        vds_id BIGINT(20) UNSIGNED NOT NULL,
        product_id BIGINT(20) UNSIGNED NOT NULL,
        precio_final DECIMAL(10,2) NOT NULL DEFAULT 0,
        descripcion TEXT NULL,
        orden INT NOT NULL DEFAULT 0,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        fecha_creado DATETIME NOT NULL,
        fecha_actualizado DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY vds_product (vds_id, product_id)
    ) $charset;";

    dbDelta( $sql );

    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'driftly_core_activate' );

function driftly_core_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'driftly_core_deactivate' );
