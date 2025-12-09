<?php
if (!defined('ABSPATH')) exit;

class VDS_Perfil_Controller extends Driftly_Controller {

    public function handle($param) {

        $vds_id = get_current_user_id();

        if (!$vds_id) {
            wp_die('No autorizado.');
        }

        // Cargar datos del usuario desde ACF
        $data = [
            'titulo'              => 'Mi Perfil',
            'subtitulo'           => 'Configura la información de tu tienda',
            'vds_id'              => $vds_id,
            'nombre_de_la_tienda' => get_field('nombre_de_la_tienda', 'user_' . $vds_id),
            'logo_de_la_tienda'   => get_field('logo_de_la_tienda', 'user_' . $vds_id),
            'color_primario'      => get_field('color_primario', 'user_' . $vds_id),
            'whatsapp'            => get_field('whatsapp', 'user_' . $vds_id),
            'porcentaje_comision' => get_field('porcentaje_de_comision_del_vds', 'user_' . $vds_id),
            'estado_vds'          => get_field('estado_del_vds', 'user_' . $vds_id),
        ];

        $this->render('vds-perfil', $data);
    }
}


/*
|--------------------------------------------------------------------------
| AJAX: Guardar el perfil
|--------------------------------------------------------------------------
*/
add_action('wp_ajax_vds_guardar_perfil', 'driftly_vds_guardar_perfil');

function driftly_vds_guardar_perfil() {

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'No autorizado']);
    }

    $vds_id = get_current_user_id();
    $user_key = 'user_' . $vds_id;

    // Sanitizar datos recibidos
    $nombre      = sanitize_text_field($_POST['nombre'] ?? '');
    $color       = sanitize_hex_color($_POST['color'] ?? '');
    $whatsapp    = sanitize_text_field($_POST['whatsapp'] ?? '');
    $comision    = floatval($_POST['comision'] ?? 0);
    $estado      = sanitize_text_field($_POST['estado'] ?? '');

    // Guardar con update_field()
    update_field('nombre_de_la_tienda', $nombre, $user_key);
    update_field('color_primario', $color, $user_key);
    update_field('whatsapp', $whatsapp, $user_key);
    update_field('porcentaje_de_comision_del_vds', $comision, $user_key);
    update_field('estado_del_vds', $estado, $user_key);

    wp_send_json_success([
        'message' => 'Perfil actualizado correctamente.'
    ]);
}
