<?php
/**
 * Driftly Router – Sistema interno de rutas
 */

if (!defined('ABSPATH')) exit;

class Driftly_Router {

    public function __construct() {
        add_action('init', [ $this, 'add_rewrite_rules' ]);
        add_filter('query_vars', [ $this, 'register_query_vars' ]);

        // IMPORTANTE: reemplaza template_redirect por wp
        add_action('wp', [ $this, 'dispatch_route' ]);
    }

    /**
     * Crear rutas base:
     * /vds/*
     * /proveedor/*
     * /admin-driftly/*
     */
    public function add_rewrite_rules() {

        add_rewrite_rule(
            '^vds/([^/]+)/?([^/]*)/?',
            'index.php?driftly_role=vds&driftly_page=$matches[1]&driftly_param=$matches[2]',
            'top'
        );

        add_rewrite_rule(
            '^proveedor/([^/]+)/?([^/]*)/?',
            'index.php?driftly_role=proveedor&driftly_page=$matches[1]&driftly_param=$matches[2]',
            'top'
        );

        add_rewrite_rule(
            '^admin-driftly/([^/]+)/?([^/]*)/?',
            'index.php?driftly_role=admin&driftly_page=$matches[1]&driftly_param=$matches[2]',
            'top'
        );
    }

    /**
     * Query vars personalizadas
     */
    public function register_query_vars($vars) {
        $vars[] = 'driftly_role';
        $vars[] = 'driftly_page';
        $vars[] = 'driftly_param';
        return $vars;
    }

    /**
     * Resolver ruta → llamar controlador correspondiente
     */
    public function dispatch_route() {

        $role  = get_query_var('driftly_role');
        $page  = get_query_var('driftly_page');
        $param = get_query_var('driftly_param');

        // No es una ruta Driftly
        if (!$role || !$page) {
            return;
        }

        // Validar rol del usuario
        $user_role = driftly_get_user_role();

        if ($user_role !== $role && $user_role !== 'admin') {
            wp_die('No autorizado.');
        }

        // ----------------------------------------------------------
        // CONTROLADORES IMPLEMENTADOS
        // ----------------------------------------------------------

        $controller_map = [
            'vds' => [
                'dashboard' => 'VDS_Dashboard_Controller',
                'catalogo'  => 'VDS_Catalogo_Controller',
            ],
        ];

        if (!isset($controller_map[$role])) {
            wp_die('Ruta no implementada.');
        }

        if (!isset($controller_map[$role][$page])) {
            wp_die('Página no implementada para este rol.');
        }

        $class_name = $controller_map[$role][$page];

        if (!class_exists($class_name)) {
            wp_die('Controlador no disponible: ' . $class_name);
        }

        $controller = new $class_name();

        // Ejecutar controlador y detener WordPress
        $controller->handle($param);
        exit;
    }
}

new Driftly_Router();
