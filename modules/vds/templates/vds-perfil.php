<?php
/**
 * Plantilla: Perfil del Vendedor (VDS)
 *
 * Variables recibidas:
 * $titulo
 * $subtitulo
 * $vds_id
 * $nombre_de_la_tienda
 * $logo_de_la_tienda
 * $color_primario
 * $whatsapp
 * $porcentaje_comision
 * $estado_vds
 */

if (!defined('ABSPATH')) exit;
?>

<div class="d-page-card">

    <!-- HEADER -->
    <div class="d-page-card__header">
        <div>
            <h1 class="d-page-card__title"><?php echo esc_html($titulo); ?></h1>
            <p class="d-page-card__subtitle"><?php echo esc_html($subtitulo); ?></p>
        </div>
    </div>

    <div class="d-vds-profile">

        <!-- ===========================
             SECCIÓN 1: Identidad de la Tienda
        ============================ -->
        <section class="d-section">
            <h3>Identidad de la tienda</h3>

            <div class="d-edit-grid">

                <div class="d-field d-field--full">
                    <label for="vds-nombre">Nombre de la tienda *</label>
                    <input type="text" id="vds-nombre" value="<?php echo esc_attr($nombre_de_la_tienda); ?>">
                </div>

                <div class="d-field d-field--full">
                    <label>Logo de la tienda</label>
                    <div class="d-logo-upload">
                        <?php if (!empty($logo_de_la_tienda['url'])) : ?>
                            <img src="<?php echo esc_url($logo_de_la_tienda['url']); ?>" class="d-logo-preview">
                        <?php else : ?>
                            <div class="d-logo-placeholder">Sin logo</div>
                        <?php endif; ?>
                    </div>
                    <p class="d-text-muted">* Subiremos el uploader en un paso posterior.</p>
                </div>

            </div>
        </section>


        <!-- ===========================
             SECCIÓN 2: Branding
        ============================ -->
        <section class="d-section">
            <h3>Branding</h3>

            <div class="d-edit-grid">
                <div class="d-field d-field--full">
                    <label for="vds-color">Color primario</label>
                    <input type="color" id="vds-color" value="<?php echo esc_attr($color_primario ?: '#000000'); ?>">
                </div>
            </div>
        </section>


        <!-- ===========================
             SECCIÓN 3: Configuración del VDS
        ============================ -->
        <section class="d-section">
            <h3>Configuración del Vendedor</h3>

            <div class="d-edit-grid">

                <div class="d-field">
                    <label for="vds-comision">Comisión (%)</label>
                    <input type="number" id="vds-comision"
                           step="0.1"
                           value="<?php echo esc_attr($porcentaje_comision); ?>">
                </div>

                <div class="d-field">
                    <label for="vds-estado">Estado</label>
                    <select id="vds-estado">
                        <option value="Activo" <?php selected($estado_vds, 'Activo'); ?>>Activo</option>
                        <option value="Inactivo" <?php selected($estado_vds, 'Inactivo'); ?>>Inactivo</option>
                    </select>
                </div>

            </div>
        </section>


        <!-- ===========================
             SECCIÓN 4: Contacto
        ============================ -->
        <section class="d-section">
            <h3>Contacto</h3>

            <div class="d-edit-grid">
                <div class="d-field d-field--full">
                    <label for="vds-whatsapp">WhatsApp</label>
                    <input type="text" id="vds-whatsapp" value="<?php echo esc_attr($whatsapp); ?>">
                </div>
            </div>
        </section>


        <!-- ===========================
             BOTÓN GUARDAR
        ============================ -->
        <div class="d-section">
            <button class="d-btn d-btn--primary" id="vds-guardar-perfil">
                Guardar cambios
            </button>
            <span id="vds-save-status" class="d-save-status"></span>
        </div>

    </div>

</div>


<!-- ==========================================
     JAVASCRIPT – AJAX GUARDAR PERFIL
=========================================== -->
<script>
document.getElementById('vds-guardar-perfil').addEventListener('click', () => {

    const data = new FormData();
    data.append('action', 'vds_guardar_perfil');
    data.append('nombre', document.getElementById('vds-nombre').value);
    data.append('color', document.getElementById('vds-color').value);
    data.append('whatsapp', document.getElementById('vds-whatsapp').value);
    data.append('comision', document.getElementById('vds-comision').value);
    data.append('estado', document.getElementById('vds-estado').value);

    const status = document.getElementById('vds-save-status');
    status.textContent = 'Guardando…';

    fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
        method: 'POST',
        body: data
    })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                status.textContent = 'Guardado ✓';
                status.style.color = 'green';
            } else {
                status.textContent = 'Error al guardar';
                status.style.color = 'red';
            }

            setTimeout(() => status.textContent = '', 2000);
        });
});
</script>


<!-- ==========================================
     ESTILOS LOCALIZADOS
=========================================== -->
<style>
.d-vds-profile {
    padding: 5px;
}

.d-logo-upload {
    margin-top: 8px;
}

.d-logo-preview {
    width: 120px;
    height: auto;
    border-radius: 8px;
}

.d-logo-placeholder {
    width: 120px;
    height: 120px;
    background: #eee;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
}

.d-save-status {
    margin-left: 12px;
    font-size: 14px;
}
</style>
