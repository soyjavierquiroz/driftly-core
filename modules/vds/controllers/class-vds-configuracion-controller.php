<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VDS_Configuracion_Controller extends Driftly_Controller {

	public function handle( $param ) {

		if ( ! is_user_logged_in() ) {
			wp_die( 'Debes iniciar sesión para ver esta página.' );
		}

		$vds_id = get_current_user_id();
		$acf_key = 'user_' . $vds_id;

		$get = function( $field ) use ( $acf_key ) {
			if ( ! function_exists( 'get_field' ) ) {
				return '';
			}
			return get_field( $field, $acf_key );
		};

		$identidad = [
			'nombre_de_la_tienda' => $get( 'nombre_de_la_tienda' ),
			'logo_de_la_tienda'   => $get( 'logo_de_la_tienda' ),
			'color_primario'      => $get( 'color_primario' ),
		];

		$dominio = [
			'dominio'               => $get( 'dominio' ),
			'slug_de_la_tienda'     => $get( 'slug_de_la_tienda' ),
			'estado_del_vds'        => $get( 'estado_del_vds' ),
			'porcentaje_de_comision'=> $get( 'porcentaje_de_comision_del_vds' ),
		];

		$contacto = [
			'whatsapp' => $get( 'whatsapp' ),
		];

		$scripts = [
			'pixel_meta'         => $get( 'pixel_meta' ),
			'google_tag_manager' => $get( 'google_tag_manager' ),
			'header_scripts'     => $get( 'header_scripts' ),
			'footer_scripts'     => $get( 'footer_scripts' ),
		];

		$tipo_vds = $get( 'tipo_vds' ); // "Basico" / "Pro" (opcional)

		$data = [
			'titulo'     => 'Mi tienda',
			'subtitulo'  => 'Configura cómo se ve y se comporta tu tienda pública.',
			'identidad'  => $identidad,
			'dominio'    => $dominio,
			'contacto'   => $contacto,
			'scripts'    => $scripts,
			'tipo_vds'   => $tipo_vds,
			'nonce'      => wp_create_nonce( 'driftly_vds_config' ),
		];

		$this->render( 'vds-configuracion', $data );
	}
}
