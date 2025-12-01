<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Proveedor_Dashboard_Controller extends Driftly_Controller {

	public function handle( $param = null ) {

		$data = [
			'title' => 'Dashboard del Proveedor',
			'role'  => driftly_get_role_label(),
		];

		$this->render( 'proveedor-dashboard', $data );
	}
}
