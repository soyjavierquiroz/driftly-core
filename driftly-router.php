<?php
/**
 * Driftly Router – Sistema interno de rutas
 */

if (!defined('ABSPATH')) exit;

class Driftly_Router {

    public function __construct() {
        add_action('init', [ $this, 'add_rewrite_rules' ]);
        add_filter('query_vars', [ $this, 'register_query_vars' ]);
        add_action('wp', [ $this, 'dispatch_route' ]);
    }

    public function add_rewrite_rules() {

        /*
        |--------------------------------------------------------------------------
        | VDS
        |--------------------------------------------------------------------------
        */
        add_rewrite_rule(
            '^vds/([^/]+)/([^/]+)/([^/]+)/?',
            'index.php?driftly_role=vds&driftly_page=$matches[1]&driftly_param=$matches[2]&driftly_param2=$matches[3]',
            'top'
        );

        add_rewrite_rule(
            '^vds/([^/]+)/([^/]+)/?',
            'index.php?driftly_role=vds&driftly_page=$matches[1]&driftly_param=$matches[2]',
            'top'
        );

        add_rewrite_rule(
            '^vds/([^/]+)/?',
            'index.php?driftly_role=vds&driftly_page=$matches[1]',
            'top'
        );

        /*
        |--------------------------------------------------------------------------
        | PROVEEDOR
        |--------------------------------------------------------------------------
        */
        add_rewrite_rule(
            '^proveedor/([^/]+)/([^/]+)/([^/]+)/?',
            'index.php?driftly_role=proveedor&driftly_page=$matches[1]&driftly_param=$matches[2]&driftly_param2=$matches[3]',
            'top'
        );

        add_rewrite_rule(
            '^proveedor/([^/]+)/([^/]+)/?',
            'index.php?driftly_role=proveedor&driftly_page=$matches[1]&driftly_param=$matches[2]',
            'top'
        );

        add_rewrite_rule(
            '^proveedor/([^/]+)/?',
            'index.php?driftly_role=proveedor&driftly_page=$matches[1]',
            'top'
        );

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */
        add_rewrite_rule(
            '^admin-driftly/([^/]+)/([^/]+)/?',
            'index.php?driftly_role=admin&driftly_page=$matches[1]&driftly_param=$matches[2]',
            'top'
        );

        add_rewrite_rule(
            '^admin-driftly/([^/]+)/?',
            'index.php?driftly_role=admin&driftly_page=$matches[1]',
            'top'
        );
    }

    public function register_query_vars($vars) {
        $vars[] = 'driftly_role';
        $vars[] = 'driftly_page';
        $vars[] = 'driftly_param';
        $vars[] = 'driftly_param2';
        return $vars;
    }

    public function dispatch_route() {

        $role   = get_query_var('driftly_role');
        $page   = get_query_var('driftly_page');
        $param1 = get_query_var('driftly_param');
        $param2 = get_query_var('driftly_param2');

        if (!$role || !$page) return;

        // Validar rol
        $user_role = driftly_get_user_role();
        if ($user_role !== $role && $user_role !== 'admin') {
            wp_die('No autorizado.');
        }

        /*
        |--------------------------------------------------------------------------
        | MAPA DE CONTROLADORES
        |--------------------------------------------------------------------------
        */
        $controller_map = [
            'proveedor' => [
                'dashboard' => 'Proveedor_Dashboard_Controller',
                'productos' => 'Proveedor_Productos_Controller',
                'producto'  => 'Proveedor_Producto_Controller',
                'pedidos'   => 'Proveedor_Pedidos_Controller',
            ],

            'vds' => [
                'dashboard'     => 'VDS_Dashboard_Controller',
                'catalogo'      => 'VDS_Catalogo_Controller',
                'mis-productos' => 'VDS_Mis_Productos_Controller',
                'producto'      => 'VDS_Producto_Controller',
                'configuracion' => 'VDS_Configuracion_Controller', // 👈 NUEVO
            ],
        ];

        if (!isset($controller_map[$role][$page])) {
            wp_die('Página no implementada.');
        }

        $class = $controller_map[$role][$page];
        if (!class_exists($class)) {
            wp_die('Controlador no disponible.');
        }

        $controller = new $class();

        /*
        |--------------------------------------------------------------------------
        | Pasar parámetros correctamente
        |--------------------------------------------------------------------------
        */
        if ($param1 && $param2) {
            $controller->handle([ $param1, $param2 ]);
        } else {
            $controller->handle($param1);
        }

        exit;
    }
}

new Driftly_Router();
