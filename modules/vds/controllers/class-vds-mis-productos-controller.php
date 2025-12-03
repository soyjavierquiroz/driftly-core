<?php
if (!defined('ABSPATH')) exit;

class VDS_Mis_Productos_Controller extends Driftly_Controller {

    public function handle($param) {

        $vds_id = get_current_user_id();

        global $wpdb;
        $tabla = $wpdb->prefix . 'driftly_vds_productos';

        // Obtener SOLO productos activos del VDS
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $tabla 
                 WHERE vds_id = %d AND activo = 1 
                 ORDER BY orden ASC, fecha_actualizado DESC",
                $vds_id
            )
        );

        $productos = [];

        foreach ($rows as $row) {

            $product_id = intval($row->product_id);
            $post = get_post($product_id);

            if (!$post || $post->post_type !== 'product') {
                continue;
            }

            // Datos WooCommerce
            $precio_vendedor = get_field('precio_vendedor', $product_id);
            $precio_sugerido  = get_field('precio_sugerido', $product_id);
            $proveedor_id     = get_field('proveedor_id', $product_id);

            $proveedor_nombre = $proveedor_id
                ? get_user_meta($proveedor_id, 'nombre_comercial', true)
                : 'Sin proveedor';

            $thumb = get_the_post_thumbnail_url($product_id, 'medium');
            if (!$thumb && function_exists('wc_placeholder_img_src')) {
                $thumb = wc_placeholder_img_src();
            }

            // URLs externas (tabla nueva)
            $urls = $this->get_product_urls($product_id, $vds_id);

            $productos[] = [
                'id'               => $product_id,
                'nombre'           => $post->post_title,
                'imagen'           => $thumb,
                'proveedor_nombre' => $proveedor_nombre,

                'precio_vendedor' => $precio_vendedor,
                'precio_sugerido'  => $precio_sugerido,

                // Datos VDS personalizados
                'precio_vds'       => (float) $row->precio_final,
                'descripcion_vds'  => $row->descripcion,
                'orden'            => intval($row->orden),

                'urls'             => $urls,
            ];
        }

        // DATA PARA LA PLANTILLA
        $data = [
            'titulo'      => 'Mis Productos',
            'subtitulo'   => 'Productos activos en tu tienda',
            'productos'   => $productos,
        ];

        $this->render('vds-mis-productos', $data);
    }

    /**
     * Obtener URLs externas del producto desde la tabla driftly_vds_urls
     */
    private function get_product_urls($product_id, $vds_id) {
        global $wpdb;
        $tabla_urls = $wpdb->prefix . 'driftly_vds_urls';

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $tabla_urls 
                 WHERE vds_id = %d AND product_id = %d
                 ORDER BY fecha_creado ASC",
                $vds_id,
                $product_id
            )
        );

        $list = [];
        foreach ($rows as $r) {
            $list[] = [
                'id'     => $r->id,
                'url'    => $r->url,
                'label'  => $r->label,
                'fecha'  => $r->fecha_creado,
            ];
        }

        return $list;
    }
}
