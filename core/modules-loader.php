<?php
/**
 * Loader de módulos para Driftly Core.
 *
 * Este archivo NO altera la funcionalidad actual.
 * Solo prepara un sistema de registro y carga modular.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Array global donde se almacenan los módulos registrados.
 */
global $driftly_modules;
$driftly_modules = [];

/**
 * Registra un módulo interno.
 *
 * @param string $slug Identificador del módulo (ej: 'vds').
 * @param string $path Ruta absoluta del módulo en el plugin.
 */
function driftly_load_module( $slug, $path ) {
    global $driftly_modules;

    if ( isset( $driftly_modules[ $slug ] ) ) {
        return;
    }

    $driftly_modules[ $slug ] = [
        'slug'  => $slug,
        'path'  => rtrim( $path, '/' ),
        'files' => [
            'controllers' => [],
            'ajax'        => [],
            'templates'   => [],
            'other'       => [],
        ]
    ];
}

/**
 * Registra archivos específicos para un módulo.
 */
function driftly_register_module_files( $slug, $type, $files ) {
    global $driftly_modules;

    if ( ! isset( $driftly_modules[ $slug ] ) ) {
        return;
    }

    if ( ! isset( $driftly_modules[ $slug ]['files'][ $type ] ) ) {
        return;
    }

    foreach ( (array) $files as $file ) {
        $driftly_modules[ $slug ]['files'][ $type ][] = $file;
    }
}

/**
 * Carga todos los archivos registrados de todos los módulos.
 */
function driftly_boot_modules() {
    global $driftly_modules;

    foreach ( $driftly_modules as $module ) {
        $base = $module['path'];

        foreach ( $module['files'] as $type => $files ) {
            foreach ( $files as $file ) {

                $path = $base . '/' . ltrim( $file, '/' );

                if ( file_exists( $path ) ) {
                    require_once $path;
                }
            }
        }
    }
}

/**
 * Permite obtener todos los módulos registrados.
 */
function driftly_get_modules() {
    global $driftly_modules;
    return $driftly_modules;
}
