<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Proveedor_Producto_Controller extends Driftly_Controller {

	public function handle( $product_id = null ) {

		if ( empty( $product_id ) || ! is_numeric( $product_id ) ) {
			wp_die( 'Producto no especificado.' );
		}

		$data = [
			'title'      => "Editar Producto #{$product_id}",
			'product_id' => intval( $product_id ),
		];

		$this->render( 'proveedor-producto', $data );
	}
}
