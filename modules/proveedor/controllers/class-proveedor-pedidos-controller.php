<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Proveedor_Pedidos_Controller extends Driftly_Controller {

	public function handle( $param = null ) {

		$data = [
			'title' => 'Pedidos del Proveedor',
			'role'  => driftly_get_role_label(),
		];

		$this->render( 'proveedor-pedidos', $data );
	}
}
