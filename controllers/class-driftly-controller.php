<?php
if (!defined('ABSPATH')) exit;

abstract class Driftly_Controller {

    /**
     * Render multmódulo:
     *  - Detecta módulo por prefijo del template (vds-, proveedor-, etc.)
     *  - Carga correctamente la plantilla desde su módulo
     *  - Envolver en el header/footer del theme Driftly
     *  - Evitar desbordamientos o mezclas entre módulos
     */
    protected function render($template, $data = []) {

        // Asegurar que no venga con ".php"
        $template = str_replace('.php', '', $template);

        $template_file = '';

        //--------------------------------------------------------------
        // 1. Detectar el módulo por prefijo:
        //    vds-dashboard     => módulo "vds"
        //    proveedor-product => módulo "proveedor"
        //--------------------------------------------------------------
        if (preg_match('/^(vds|proveedor)\-/', $template, $match)) {

            $slug = $match[1]; // vds o proveedor

            $module_template = DRIFTLY_CORE_PATH . "modules/$slug/templates/$template.php";

            if (file_exists($module_template)) {
                $template_file = $module_template;
            }
        }

        //--------------------------------------------------------------
        // 2. Si no existe → error claro
        //--------------------------------------------------------------
        if (!$template_file || !file_exists($template_file)) {
            wp_die("Template no encontrado: $template");
        }

        //--------------------------------------------------------------
        // 3. Pasar variables a la vista de forma segura
        //--------------------------------------------------------------
        if (!empty($data) && is_array($data)) {
            extract($data, EXTR_SKIP);
        }

        //--------------------------------------------------------------
        // 4. Layout global del panel Driftly
        //--------------------------------------------------------------
        get_header();  // Header del theme Driftly
        include $template_file; // La vista del módulo correspondiente
        get_footer();  // Footer del theme Driftly

        //--------------------------------------------------------------
        // 5. Muy importante: evitar que WP siga procesando otras plantillas
        //--------------------------------------------------------------
        exit;
    }

    abstract public function handle($param);
}
