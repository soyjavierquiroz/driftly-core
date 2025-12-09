<?php
/**
 * Plantilla: Configuración de la tienda (VDS) — Versión estable, aislada y responsiva
 */

if (!defined('ABSPATH')) exit;

$nombre_tienda = $identidad['nombre_de_la_tienda'] ?? '';
$logo          = $identidad['logo_de_la_tienda'] ?? null;
$color         = $identidad['color_primario'] ?? '';

$dominio_txt   = $dominio['dominio'] ?? '';
$slug_tienda   = $dominio['slug_de_la_tienda'] ?? '';
$estado_vds    = $dominio['estado_del_vds'] ?? '';
$comision_vds  = $dominio['porcentaje_de_comision'] ?? '';

$whatsapp      = $contacto['whatsapp'] ?? '';

$pixel_meta    = $scripts['pixel_meta'] ?? '';
$gtm_id        = $scripts['google_tag_manager'] ?? '';
$header_scripts= $scripts['header_scripts'] ?? '';
$footer_scripts= $scripts['footer_scripts'] ?? '';

$is_pro = ( is_string($tipo_vds ?? '') && strtolower($tipo_vds) === 'pro' ) || current_user_can('manage_options');
?>

<div class="d-page-card vds-config">

    <div class="d-page-card__header">
        <div>
            <h1 class="d-page-card__title"><?php echo esc_html($titulo); ?></h1>
            <p class="d-page-card__subtitle"><?php echo esc_html($subtitulo); ?></p>
        </div>
    </div>

    <!-- NAV TABS -->
    <div class="vds-tabs">
        <div class="vds-tabs__nav">
            <button class="vds-tabs__button is-active" data-tab="identidad">Identidad</button>
            <button class="vds-tabs__button" data-tab="dominio">Dominio</button>
            <button class="vds-tabs__button" data-tab="contacto">Contacto</button>
            <button class="vds-tabs__button" data-tab="scripts">Scripts & tracking</button>
        </div>

        <form id="vds-config-form"
              class="vds-tabs__content"
              data-nonce="<?php echo esc_attr($nonce); ?>">

            <!-- TAB: IDENTIDAD -->
            <section class="vds-tab-panel is-active" data-tab-panel="identidad">
                <h2 class="vds-section-title">Identidad de la tienda</h2>

                <div class="vds-field">
                    <label for="nombre_de_la_tienda">Nombre de la tienda *</label>
                    <input type="text" id="nombre_de_la_tienda" name="nombre_de_la_tienda"
                           value="<?php echo esc_attr($nombre_tienda); ?>" required>
                </div>

                <div class="vds-field">
                    <label>Logo de la tienda</label>
                    <div class="vds-logo-box">
                        <?php if (!empty($logo['url'])) : ?>
                            <img src="<?php echo esc_url($logo['url']); ?>">
                        <?php else : ?>
                            <span>Sin logo</span>
                        <?php endif; ?>
                    </div>
                    <p class="vds-muted">* El uploader se agregará más adelante.</p>
                </div>

                <!-- COLOR PICKER NUEVO -->
                <div class="vds-field vds-colorpicker">
                    <label for="color_primario">Color primario</label>

                    <div class="vds-colorpicker__wrap">
                        <input type="color"
                            id="color_primario"
                            name="color_primario"
                            class="vds-colorpicker__input"
                            value="<?php echo esc_attr($color ?: '#ff6600'); ?>">

                        <input type="text"
                            id="color_primario_hex"
                            class="vds-colorpicker__hex"
                            maxlength="7"
                            value="<?php echo esc_attr($color ?: '#ff6600'); ?>">
                    </div>

                    <p class="vds-muted">Elige el color principal de tu tienda.</p>
                </div>

            </section>

            <!-- TAB: DOMINIO -->
            <section class="vds-tab-panel" data-tab-panel="dominio">
                <h2 class="vds-section-title">Dominio y URL pública</h2>

                <div class="vds-field">
                    <label for="dominio">Dominio o subdominio</label>
                    <input type="text" id="dominio" name="dominio"
                           placeholder="mitienda.com o vendedor.tienda.com"
                           value="<?php echo esc_attr($dominio_txt); ?>">
                </div>

                <div class="vds-field">
                    <label for="slug_de_la_tienda">Slug interno (opcional)</label>
                    <input type="text" id="slug_de_la_tienda" name="slug_de_la_tienda"
                           value="<?php echo esc_attr($slug_tienda); ?>">
                    <p class="vds-muted">Usado como ID interno de la tienda.</p>
                </div>

                <?php if ($estado_vds || $comision_vds !== '') : ?>
                    <div class="vds-field vds-field--readonly">
                        <label>Configuración administrada por Driftly</label>
                        <p class="vds-muted">
                            Estado: <strong><?php echo esc_html($estado_vds ?: 'N/D'); ?></strong><br>
                            Comisión: <strong><?php echo esc_html($comision_vds !== '' ? $comision_vds . '%' : 'N/D'); ?></strong>
                        </p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- TAB: CONTACTO -->
            <section class="vds-tab-panel" data-tab-panel="contacto">
                <h2 class="vds-section-title">Contacto</h2>

                <div class="vds-field">
                    <label for="whatsapp">WhatsApp de la tienda</label>
                    <input type="text" id="whatsapp" name="whatsapp"
                           placeholder="Ej: +591 70000000"
                           value="<?php echo esc_attr($whatsapp); ?>">
                    <p class="vds-muted">Se usa como soporte en las landings.</p>
                </div>
            </section>

            <!-- TAB: SCRIPTS -->
            <section class="vds-tab-panel" data-tab-panel="scripts">
                <h2 class="vds-section-title">Scripts & tracking</h2>

                <?php if (!$is_pro) : ?>
                    <div class="vds-pro-lock">
                        Disponible solo para cuentas <strong>VDS Pro</strong>.
                    </div>
                <?php endif; ?>

                <div class="vds-field">
                    <label for="pixel_meta">Pixel Meta (ID)</label>
                    <input type="text" id="pixel_meta" name="pixel_meta"
                        value="<?php echo esc_attr($pixel_meta); ?>"
                        <?php echo $is_pro ? '' : 'disabled'; ?>>
                </div>

                <div class="vds-field">
                    <label for="google_tag_manager">Google Tag Manager (ID)</label>
                    <input type="text" id="google_tag_manager" name="google_tag_manager"
                        value="<?php echo esc_attr($gtm_id); ?>"
                        <?php echo $is_pro ? '' : 'disabled'; ?>>
                </div>

                <div class="vds-field">
                    <label for="header_scripts">Scripts en &lt;head&gt;</label>
                    <textarea id="header_scripts" name="header_scripts" rows="4"
                        <?php echo $is_pro ? '' : 'disabled'; ?>><?php echo esc_textarea($header_scripts); ?></textarea>
                </div>

                <div class="vds-field">
                    <label for="footer_scripts">Scripts al final del body</label>
                    <textarea id="footer_scripts" name="footer_scripts" rows="4"
                        <?php echo $is_pro ? '' : 'disabled'; ?>><?php echo esc_textarea($footer_scripts); ?></textarea>
                </div>
            </section>

            <!-- FOOTER -->
            <div class="vds-footer">
                <button type="button" id="vds-config-save" class="d-btn d-btn--primary">
                    Guardar cambios
                </button>
                <span id="vds-config-status" class="vds-status"></span>
            </div>

        </form>
    </div>
</div>

<script>
// Ajax URL
window.DriftlyDashboard = window.DriftlyDashboard || {};
if (!DriftlyDashboard.ajaxUrl)
    DriftlyDashboard.ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";

(function() {
    // Tabs
    const buttons = document.querySelectorAll('.vds-tabs__button');
    const panels = document.querySelectorAll('.vds-tab-panel');

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;

            buttons.forEach(b => b.classList.remove('is-active'));
            btn.classList.add('is-active');

            panels.forEach(p =>
                p.classList.toggle('is-active', p.dataset.tabPanel === tab)
            );
        });
    });

    // COLOR PICKER → sincronización
    const inputColor = document.getElementById("color_primario");
    const inputHex   = document.getElementById("color_primario_hex");

    function normalizeHex(v) {
        v = v.trim();
        if (!v.startsWith("#")) v = "#" + v;
        return /^#[0-9A-Fa-f]{6}$/.test(v) ? v : null;
    }

    inputColor.addEventListener("input", e => {
        inputHex.value = e.target.value;
    });

    inputHex.addEventListener("input", e => {
        const n = normalizeHex(e.target.value);
        if (n) inputColor.value = n;
    });

    // Guardar datos
    const form = document.getElementById('vds-config-form');
    const saveBtn = document.getElementById('vds-config-save');
    const statusEl = document.getElementById('vds-config-status');

    saveBtn.addEventListener('click', () => {
        const fd = new FormData(form);
        fd.append('action', 'driftly_save_vds_configuracion');
        fd.append('nonce', form.dataset.nonce);

        saveBtn.disabled = true;
        saveBtn.textContent = 'Guardando...';
        statusEl.textContent = '';

        fetch(DriftlyDashboard.ajaxUrl, { method:'POST', body:fd })
            .then(r => r.json())
            .then(res => {
                statusEl.textContent = res.success ? 'Guardado.' : 'Error al guardar.';
            })
            .finally(() => {
                saveBtn.disabled = false;
                saveBtn.textContent = 'Guardar cambios';
            });
    });
})();
</script>

<style>
/* =========================================================
   ESTILOS AISLADOS PARA VDS CONFIG
   NO AFECTAN AL LAYOUT GLOBAL NI AL SIDEBAR
   ========================================================= */

.vds-config {
    padding: 26px 30px;
}

/* Contenedor del formulario */
.vds-tabs__content {
    max-width: 860px !important;
    width: 100%;
    margin: 0 auto;
}

/* Tabs */
.vds-tabs__nav {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    overflow-x: auto;
}

.vds-tabs__button {
    border: none;
    background: #f3f4f6;
    padding: 8px 16px;
    border-radius: 999px;
    cursor: pointer;
    font-size: 14px;
}

.vds-tabs__button.is-active {
    background: #ff7a18;
    color: #fff;
}

/* Panels */
.vds-tab-panel {
    display: none;
}

.vds-tab-panel.is-active {
    display: block;
}

.vds-section-title {
    font-size: 1.05rem;
    font-weight: 600;
    margin-bottom: 18px;
}

/* Campos */
.vds-field {
    margin-bottom: 18px;
}

.vds-field label {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 4px;
}

.vds-field input,
.vds-field textarea {
    width: 100%;
    border-radius: 12px;
    border: 1px solid #d1d5db;
    padding: 8px 12px;
    font-size: 0.9rem;
}

/* COLOR PICKER */
.vds-colorpicker__wrap {
    display: flex;
    align-items: center;
    gap: 12px;
}

.vds-colorpicker__input {
    width: 48px;
    height: 38px;
    padding: 0;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    cursor: pointer;
}

.vds-colorpicker__hex {
    flex: 1;
}

.vds-logo-box {
    width: 180px;
    height: 130px;
    border: 1px dashed #ccc;
    border-radius: 12px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #fafafa;
    overflow: hidden;
}

.vds-logo-box img {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

.vds-muted {
    font-size: 12px;
    color: #6b7280;
}

/* PRO lock */
.vds-pro-lock {
    padding: 14px;
    border-radius: 8px;
    background: #fff7ed;
    border: 1px solid #fdba74;
    margin-bottom: 20px;
}

/* Footer */
.vds-footer {
    margin-top: 20px;
    display: flex;
    align-items: center;
    gap: 14px;
}

/* Responsive */
@media (max-width: 768px) {
    .vds-tabs__content {
        max-width: 100% !important;
        padding-inline: 6px;
    }
}
</style>
