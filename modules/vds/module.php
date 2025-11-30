<?php
/**
 * Módulo VDS cargado desde la carpeta modules/vds
 * Ahora con rutas reales (ya movidas).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registrar el módulo
 */
driftly_load_module( 'vds', DRIFTLY_CORE_PATH . 'modules/vds' );

/**
 * Registrar CONTROLADORES y AJAX (ya movidos)
 */

// Controladores VDS
driftly_register_module_files( 'vds', 'controllers', [
	'controllers/class-vds-dashboard-controller.php',
	'controllers/class-vds-catalogo-controller.php',
]);

// AJAX VDS
driftly_register_module_files( 'vds', 'ajax', [
	'ajax/ajax-vds.php',
]);

// ❗ No registrar plantillas aquí
// Las plantillas se cargan únicamente via Driftly_Controller::render()
