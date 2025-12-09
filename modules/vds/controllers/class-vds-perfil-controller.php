<?php
if (!defined('ABSPATH')) exit;

class VDS_Perfil_Controller extends Driftly_Controller {

    public function handle($param) {

        if (!is_user_logged_in()) {
            wp_die('No autorizado.');
        }

        $vds_id   = get_current_user_id();
        $user_key = 'user_' . $vds_id;
        $user     = wp_get_current_user();

        // WP core
        $first_name = get_user_meta($vds_id, 'first_name', true);
        $last_name  = get_user_meta($vds_id, 'last_name', true);
        $email      = $user ? $user->user_email : '';

        // Helper ACF
        $acf_get = function($field) use ($user_key) {
            return function_exists('get_field') ? get_field($field, $user_key) : '';
        };

        // ACF – datos personales extra
        $telefono_personal = $acf_get('telefono_personal');
        $foto_perfil       = $acf_get('foto_perfil');
        $tipo_documento    = $acf_get('tipo_documento');
        $numero_documento  = $acf_get('numero_documento');
        $pais              = $acf_get('pais');
        $ciudad            = $acf_get('ciudad');
        $direccion         = $acf_get('direccion');
        $red_facebook      = $acf_get('red_facebook');
        $red_instagram     = $acf_get('red_instagram');
        $red_tiktok        = $acf_get('red_tiktok');
        $red_x             = $acf_get('red_x');
        $fecha_nacimiento  = $acf_get('fecha_nacimiento');

        $data = [
            'titulo'            => 'Mi perfil',
            'subtitulo'         => 'Gestiona tus datos personales como vendedor.',
            'vds_id'            => $vds_id,

            'first_name'        => $first_name,
            'last_name'         => $last_name,
            'email'             => $email,

            'telefono_personal' => $telefono_personal,
            'foto_perfil'       => $foto_perfil,
            'tipo_documento'    => $tipo_documento,
            'numero_documento'  => $numero_documento,
            'pais'              => $pais,
            'ciudad'            => $ciudad,
            'direccion'         => $direccion,
            'red_facebook'      => $red_facebook,
            'red_instagram'     => $red_instagram,
            'red_tiktok'        => $red_tiktok,
            'red_x'             => $red_x,
            'fecha_nacimiento'  => $fecha_nacimiento,

            'nonce'             => wp_create_nonce('vds_perfil_guardar'),
        ];

        $this->render('vds-perfil', $data);
    }
}

/*
|--------------------------------------------------------------------------
| AJAX: Guardar el perfil personal del VDS
|--------------------------------------------------------------------------
*/
add_action('wp_ajax_vds_guardar_perfil', 'driftly_vds_guardar_perfil');

function driftly_vds_guardar_perfil() {

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'No autorizado']);
    }

    check_ajax_referer('vds_perfil_guardar', 'nonce');

    $vds_id   = get_current_user_id();
    $user_key = 'user_' . $vds_id;

    // Sanitizar datos
    $first_name       = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name        = sanitize_text_field($_POST['last_name'] ?? '');
    $telefono         = sanitize_text_field($_POST['telefono_personal'] ?? '');
    $tipo_documento   = sanitize_text_field($_POST['tipo_documento'] ?? '');
    $numero_documento = sanitize_text_field($_POST['numero_documento'] ?? '');
    $pais             = sanitize_text_field($_POST['pais'] ?? '');
    $ciudad           = sanitize_text_field($_POST['ciudad'] ?? '');
    $direccion        = sanitize_textarea_field($_POST['direccion'] ?? '');
    $red_facebook     = sanitize_text_field($_POST['red_facebook'] ?? '');
    $red_instagram    = sanitize_text_field($_POST['red_instagram'] ?? '');
    $red_tiktok       = sanitize_text_field($_POST['red_tiktok'] ?? '');
    $red_x            = sanitize_text_field($_POST['red_x'] ?? '');
    $fecha_nacimiento = sanitize_text_field($_POST['fecha_nacimiento'] ?? '');

    // Actualizar campos WP core
    update_user_meta($vds_id, 'first_name', $first_name);
    update_user_meta($vds_id, 'last_name',  $last_name);

    // Guardar en ACF
    if (function_exists('update_field')) {
        update_field('telefono_personal', $telefono,         $user_key);
        update_field('tipo_documento',    $tipo_documento,   $user_key);
        update_field('numero_documento',  $numero_documento, $user_key);
        update_field('pais',              $pais,             $user_key);
        update_field('ciudad',            $ciudad,           $user_key);
        update_field('direccion',         $direccion,        $user_key);
        update_field('red_facebook',      $red_facebook,     $user_key);
        update_field('red_instagram',     $red_instagram,    $user_key);
        update_field('red_tiktok',        $red_tiktok,       $user_key);
        update_field('red_x',             $red_x,            $user_key);
        update_field('fecha_nacimiento',  $fecha_nacimiento, $user_key);
    }

    wp_send_json_success([
        'message' => 'Perfil actualizado correctamente.',
    ]);
}

