<?php
/**
 * Helpers del módulo PROVEEDOR
 * Configuración global y cálculos de precios.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retorna la configuración global del módulo proveedor.
 *
 * @return array {
 *   @type float margen_proveedor
 *   @type float costo_envio_base
 *   @type float margen_vendedor
 *   @type float ganancia_sugerida
 * }
 */
function driftly_proveedor_get_config() {
	global $wpdb;

	$tabla = $wpdb->prefix . 'driftly_proveedor_config';

	$row = $wpdb->get_row( "SELECT * FROM $tabla WHERE id = 1", ARRAY_A );

	// Si por alguna razón no existe, devolvemos defaults seguros
	if ( ! $row ) {
		return [
			'margen_proveedor'  => 10.00,
			'costo_envio_base'  => 20.00,
			'margen_vendedor'   => 10.00,
			'ganancia_sugerida' => 20.00, // default seguro
		];
	}

	return [
		'margen_proveedor'  => floatval( $row['margen_proveedor'] ?? 10.00 ),
		'costo_envio_base'  => floatval( $row['costo_envio_base'] ?? 20.00 ),
		'margen_vendedor'   => floatval( $row['margen_vendedor'] ?? 10.00 ),
		'ganancia_sugerida' => floatval( $row['ganancia_sugerida'] ?? 20.00 ),
	];
}


/**
 * Permite que el administrador actualice la configuración global.
 *
 * @param array $data {
 *   @type float margen_proveedor
 *   @type float costo_envio_base
 *   @type float margen_vendedor
 *   @type float ganancia_sugerida
 * }
 *
 * @return bool
 */
function driftly_proveedor_update_config( $data ) {
	global $wpdb;

	$tabla = $wpdb->prefix . 'driftly_proveedor_config';

	$clean = [
		'margen_proveedor'  => isset($data['margen_proveedor'])  ? floatval($data['margen_proveedor'])  : 10.00,
		'costo_envio_base'  => isset($data['costo_envio_base'])  ? floatval($data['costo_envio_base'])  : 20.00,
		'margen_vendedor'   => isset($data['margen_vendedor'])   ? floatval($data['margen_vendedor'])   : 10.00,
		'ganancia_sugerida' => isset($data['ganancia_sugerida']) ? floatval($data['ganancia_sugerida']) : 20.00,
		'fecha_actualizado' => current_time( 'mysql' ),
	];

	return $wpdb->update(
		$tabla,
		$clean,
		[ 'id' => 1 ]
	);
}


/**
 * Cálculo de precios:
 *
 * precio_vendedor = precio_proveedor
 *                 + (precio_proveedor * margen_proveedor%)
 *                 + costo_envio_base
 *
 * precio_sugerido = precio_vendedor
 *                 + (precio_vendedor * ganancia_sugerida%)
 *
 * @param float $precio_proveedor
 *
 * @return array {
 *   @type float precio_vendedor
 *   @type float precio_sugerido
 * }
 */
function driftly_proveedor_calcular_precios( $precio_proveedor ) {

	$config = driftly_proveedor_get_config();

	$precio_proveedor = floatval($precio_proveedor);

	// Precio al vendedor
	$precio_vendedor = $precio_proveedor
	                 + ( $precio_proveedor * ($config['margen_proveedor'] / 100 ) )
	                 + $config['costo_envio_base'];

	// Precio sugerido dinámico según configuración
	$precio_sugerido = $precio_vendedor
	                 + ( $precio_vendedor * ($config['ganancia_sugerida'] / 100 ) );

	return [
		'precio_vendedor' => round($precio_vendedor, 2),
		'precio_sugerido' => round($precio_sugerido, 2),
	];
}
