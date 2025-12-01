<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Namespace interno de AJAX: proveedor
 *
 * Todas las acciones deben comenzar con:
 *  - wp_ajax_proveedor_*
 *  - wp_ajax_nopriv_proveedor_* (si fuera necesario)
 */

class Proveedor_AJAX {

	public function __construct() {

		add_action( 'wp_ajax_proveedor_test', [ $this, 'test' ] );
	}

	public function test() {
		wp_send_json_success([
			'message' => 'AJAX proveedor funcionando correctamente.',
		]);
	}
}

new Proveedor_AJAX();
