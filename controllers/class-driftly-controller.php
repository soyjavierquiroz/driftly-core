<?php
if (!defined('ABSPATH')) exit;

abstract class Driftly_Controller {

    /**
     * Renderiza un template dentro del tema driftly-dashboard.
     *
     * Ahora incluye soporte modular:
     *  - Primero busca en el módulo correspondiente.
     *  - Luego fallback a /templates/ original.
     */
    protected function render($template, $data = []) {

        $template_file = '';

        /**
         * 1. Intentar cargar desde módulos
         * Determina qué módulo usar según el prefijo del template:
         * 'vds-dashboard' → módulo 'vds'
         */
        if (strpos($template, 'vds-') === 0) {

            // el template real es: modules/vds/templates/{template}.php
            $module_template = DRIFTLY_CORE_PATH . 'modules/vds/templates/' . $template . '.php';

            if (file_exists($module_template)) {
                $template_file = $module_template;
            }
        }

        /**
         * 2. Fallback: template clásico (sitio original)
         */
        if (!$template_file) {
            $fallback = DRIFTLY_CORE_PATH . 'templates/' . $template . '.php';

            if (file_exists($fallback)) {
                $template_file = $fallback;
            }
        }

        // Si llega aquí sin template, error crítico.
        if (!$template_file || !file_exists($template_file)) {
            wp_die("Template no encontrado: $template_file");
        }

        // Pasar datos a la vista
        extract($data);

        get_header();
        include $template_file;
        get_footer();
    }

    abstract public function handle($param);
}
