<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Proveedor_Productos_Controller extends Driftly_Controller {

	public function handle( $param = null ) {

		$data = [
			'title' => 'Mis Productos',
			'role'  => driftly_get_role_label(),
		];

		$this->render( 'proveedor-productos', $data );
	}
}
