<?php
/**
 * Módulo PROVEEDOR
 * Maneja dashboard, productos del proveedor, pedidos y AJAX.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
|--------------------------------------------------------------------------
| 1. Registrar el módulo
|--------------------------------------------------------------------------
|
| Este registro avisa al Loader que existe un nuevo módulo llamado "proveedor".
| NO se cargan archivos aún — el loader solo registra paths y archivos.
|
*/
driftly_load_module( 'proveedor', DRIFTLY_CORE_PATH . 'modules/proveedor' );


/*
|--------------------------------------------------------------------------
| 2. Registrar archivos internos (Controladores + AJAX)
|--------------------------------------------------------------------------
|
| IMPORTANTE: NO registramos templates aquí.
| Las templates se cargan solo mediante Driftly_Controller::render().
|
*/
driftly_register_module_files( 'proveedor', 'controllers', [
	'controllers/class-proveedor-dashboard-controller.php',
	'controllers/class-proveedor-productos-controller.php',
	'controllers/class-proveedor-producto-controller.php',
	'controllers/class-proveedor-pedidos-controller.php',
]);

driftly_register_module_files( 'proveedor', 'ajax', [
	'ajax/ajax-proveedor.php',
]);


/*
|--------------------------------------------------------------------------
| 3. Creación / actualización de tablas propias
|--------------------------------------------------------------------------
|
| El módulo proveedor manejará productos propios, pedidos y solicitudes.
| Por ahora solo creamos una función vacía para el MVP.
|
*/

function driftly_proveedor_ensure_tables() {
	// Se implementará más adelante.
	// dbDelta() irá aquí si se necesitan tablas.
}

// Ejecutar después que carguen módulos pero antes del router.
add_action( 'plugins_loaded', 'driftly_proveedor_ensure_tables', 5 );
