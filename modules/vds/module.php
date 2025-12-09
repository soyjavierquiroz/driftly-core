<?php
/**
 * Módulo VDS (Vendor Digital Seller)
 * Maneja catálogo maestro, productos VDS, dashboard, AJAX y su propia base de datos.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| 1. Registrar el módulo
|--------------------------------------------------------------------------
*/
driftly_load_module( 'vds', DRIFTLY_CORE_PATH . 'modules/vds' );

/*
|--------------------------------------------------------------------------
| 2. Registrar archivos internos (Controladores + AJAX)
|--------------------------------------------------------------------------
*/

// Controladores VDS
driftly_register_module_files( 'vds', 'controllers', [
	'controllers/class-vds-dashboard-controller.php',
	'controllers/class-vds-catalogo-controller.php',
	'controllers/class-vds-mis-productos-controller.php',
	'controllers/class-vds-producto-controller.php',
	'controllers/class-vds-configuracion-controller.php', // 👈 NUEVO
]);

// AJAX VDS
driftly_register_module_files( 'vds', 'ajax', [
	'ajax/ajax-vds.php',
]);

/*
|--------------------------------------------------------------------------
| 3. Creación / actualización de tablas propias del módulo VDS
|--------------------------------------------------------------------------
*/

function driftly_vds_ensure_tabla_productos() {
	global $wpdb;

	$tabla   = $wpdb->prefix . 'driftly_vds_productos';
	$charset = $wpdb->get_charset_collate();

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

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

function driftly_vds_ensure_tabla_urls() {
	global $wpdb;

	$tabla   = $wpdb->prefix . 'driftly_vds_urls';
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $tabla (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		vds_id BIGINT(20) UNSIGNED NOT NULL,
		product_id BIGINT(20) UNSIGNED NOT NULL,
		url TEXT NOT NULL,
		label VARCHAR(191) NULL,
		es_principal TINYINT(1) NOT NULL DEFAULT 0,
		fecha_creado DATETIME NOT NULL,
		fecha_actualizado DATETIME NOT NULL,
		PRIMARY KEY (id),
		KEY vds_prod_idx (vds_id, product_id),
		KEY principal_idx (vds_id, product_id, es_principal)
	) $charset;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}

function driftly_vds_ensure_tables() {
	driftly_vds_ensure_tabla_productos();
	driftly_vds_ensure_tabla_urls();
}

add_action( 'plugins_loaded', 'driftly_vds_ensure_tables', 5 );
