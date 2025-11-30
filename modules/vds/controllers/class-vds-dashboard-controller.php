<?php
if (!defined('ABSPATH')) exit;

class VDS_Dashboard_Controller extends Driftly_Controller {

    public function handle($param) {

        $data = [
            'titulo'   => 'Dashboard VDS',
            'subtitulo'=> 'Resumen y métricas básicas',
        ];

        $this->render('vds-dashboard', $data);
    }
}
