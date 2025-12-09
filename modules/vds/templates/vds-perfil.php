<?php
/**
 * Plantilla: Perfil personal del Vendedor (VDS)
 * FULL WIDTH EN MÓVIL SIN DEPENDER DE .d-page-card
 */

if (!defined('ABSPATH')) exit;

// Lista de países LATAM + USA (Bolivia primero)
$paises_latam = [
    'Bolivia',
    'Argentina',
    'Chile',
    'Colombia',
    'Costa Rica',
    'Cuba',
    'Ecuador',
    'El Salvador',
    'Guatemala',
    'Honduras',
    'México',
    'Nicaragua',
    'Panamá',
    'Paraguay',
    'Perú',
    'Puerto Rico',
    'República Dominicana',
    'Uruguay',
    'Venezuela',
    'Estados Unidos',
];

// Si no hay país, por defecto Bolivia
$selected_country = !empty($pais) ? $pais : 'Bolivia';
?>

<div class="d-vds-perfil-page">

    <!-- HEADER LOCAL (no usa d-page-card__header) -->
    <div class="d-vds-perfil-page__header">
        <div>
            <h1 class="d-vds-perfil-page__title">
                <?php echo esc_html($titulo); ?>
            </h1>
            <p class="d-vds-perfil-page__subtitle">
                <?php echo esc_html($subtitulo); ?>
            </p>
        </div>
    </div>

    <div class="vds-perfil-layout">

        <!-- Columna izquierda: foto + resumen -->
        <aside class="vds-perfil-aside">

            <div class="vds-avatar-box">
                <?php if (!empty($foto_perfil['url'])) : ?>
                    <img src="<?php echo esc_url($foto_perfil['url']); ?>" alt="Foto de perfil">
                <?php else : ?>
                    <div class="vds-avatar-placeholder">
                        <?php
                        $inicial = mb_substr(trim($first_name ?: 'V'), 0, 1);
                        echo esc_html(mb_strtoupper($inicial));
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="vds-perfil-summary">
                <div class="vds-perfil-name">
                    <?php echo esc_html(trim($first_name . ' ' . $last_name)); ?>
                </div>
                <div class="vds-perfil-email">
                    <?php echo esc_html($email); ?>
                </div>
            </div>
        </aside>

        <!-- Columna derecha: formulario -->
        <section class="vds-perfil-main">

            <form id="vds-perfil-form"
                  data-nonce="<?php echo esc_attr($nonce); ?>">

                <!-- DATOS PERSONALES -->
                <section class="vds-block">
                    <h3 class="vds-block-title">Datos personales del Vendedor</h3>
                     <p class="vds-muted">
                        Estos datos son personales y no se muestran públicamente en las landings.
                    </p>

                    <div class="vds-grid">
                        <div class="vds-field">
                            <label for="first_name">Nombre</label>
                            <input type="text" id="first_name"
                                   value="<?php echo esc_attr($first_name); ?>">
                        </div>

                        <div class="vds-field">
                            <label for="last_name">Apellidos</label>
                            <input type="text" id="last_name"
                                   value="<?php echo esc_attr($last_name); ?>">
                        </div>

                        <div class="vds-field vds-field--full">
                            <label for="email">Email (solo lectura)</label>
                            <input type="email" id="email"
                                   value="<?php echo esc_attr($email); ?>"
                                   readonly>
                        </div>

                        <div class="vds-field vds-field--half">
                            <label for="fecha_nacimiento">Fecha de nacimiento</label>
                            <input type="date" id="fecha_nacimiento"
                                   value="<?php echo esc_attr($fecha_nacimiento); ?>">
                        </div>

                        <div class="vds-field vds-field--half">
                            <label for="telefono_personal">Teléfono</label>
                            <input type="text" id="telefono_personal"
                                   placeholder="Ej: +591 70000000"
                                   value="<?php echo esc_attr($telefono_personal); ?>">
                        </div>
                    </div>
                </section>

                <!-- DOCUMENTO -->
                <section class="vds-block">
                    <h3 class="vds-block-title">Documento</h3>

                    <div class="vds-grid">
                        <div class="vds-field">
                            <label for="tipo_documento">Tipo de documento</label>
                            <select id="tipo_documento">
                                <option value="">Selecciona…</option>
                                <option value="ci"        <?php selected($tipo_documento, 'ci'); ?>>CI / Cédula</option>
                                <option value="dni"       <?php selected($tipo_documento, 'dni'); ?>>DNI</option>
                                <option value="pasaporte" <?php selected($tipo_documento, 'pasaporte'); ?>>Pasaporte</option>
                                <option value="otro"      <?php selected($tipo_documento, 'otro'); ?>>Otro</option>
                            </select>
                        </div>

                        <div class="vds-field">
                            <label for="numero_documento">Número de documento</label>
                            <input type="text" id="numero_documento"
                                   value="<?php echo esc_attr($numero_documento); ?>">
                        </div>
                    </div>
                </section>

                <!-- UBICACIÓN -->
                <section class="vds-block">
                    <h3 class="vds-block-title">Ubicación</h3>

                    <div class="vds-grid">
                        <div class="vds-field">
                            <label for="pais">País</label>
                            <select id="pais">
                                <?php foreach ($paises_latam as $p): ?>
                                    <option value="<?php echo esc_attr($p); ?>"
                                        <?php selected($selected_country, $p); ?>>
                                        <?php echo esc_html($p); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="vds-field">
                            <label for="ciudad">Ciudad</label>
                            <input type="text" id="ciudad"
                                   value="<?php echo esc_attr($ciudad); ?>">
                        </div>

                        <div class="vds-field vds-field--full">
                            <label for="direccion">Dirección</label>
                            <textarea id="direccion" rows="3"><?php echo esc_textarea($direccion); ?></textarea>
                        </div>
                    </div>
                </section>

                <!-- REDES SOCIALES -->
                <section class="vds-block">
                    <h3 class="vds-block-title">Redes sociales</h3>

                    <div class="vds-grid">
                        <div class="vds-field vds-field--full">
                            <label for="red_facebook">Facebook</label>
                            <input type="text" id="red_facebook"
                                   placeholder="URL o usuario"
                                   value="<?php echo esc_attr($red_facebook); ?>">
                        </div>

                        <div class="vds-field vds-field--full">
                            <label for="red_instagram">Instagram</label>
                            <input type="text" id="red_instagram"
                                   placeholder="@usuario o URL"
                                   value="<?php echo esc_attr($red_instagram); ?>">
                        </div>

                        <div class="vds-field vds-field--full">
                            <label for="red_tiktok">TikTok</label>
                            <input type="text" id="red_tiktok"
                                   placeholder="@usuario o URL"
                                   value="<?php echo esc_attr($red_tiktok); ?>">
                        </div>

                        <div class="vds-field vds-field--full">
                            <label for="red_x">X (Twitter)</label>
                            <input type="text" id="red_x"
                                   placeholder="@usuario o URL"
                                   value="<?php echo esc_attr($red_x); ?>">
                        </div>
                    </div>
                </section>

                <!-- FOOTER -->
                <div class="vds-perfil-footer">
                    <button type="button" class="d-btn d-btn--primary" id="vds-perfil-save">
                        Guardar cambios
                    </button>
                    <span id="vds-perfil-status" class="vds-status"></span>
                </div>

            </form>

        </section>
    </div>
</div>

<script>
(function() {
    const form   = document.getElementById('vds-perfil-form');
    const btn    = document.getElementById('vds-perfil-save');
    const status = document.getElementById('vds-perfil-status');

    if (!form || !btn) return;

    btn.addEventListener('click', function() {
        const fd = new FormData();
        fd.append('action', 'vds_guardar_perfil');
        fd.append('nonce', form.dataset.nonce || '');

        fd.append('first_name',        document.getElementById('first_name').value);
        fd.append('last_name',         document.getElementById('last_name').value);
        fd.append('telefono_personal', document.getElementById('telefono_personal').value);
        fd.append('tipo_documento',    document.getElementById('tipo_documento').value);
        fd.append('numero_documento',  document.getElementById('numero_documento').value);
        fd.append('pais',              document.getElementById('pais').value);
        fd.append('ciudad',            document.getElementById('ciudad').value);
        fd.append('direccion',         document.getElementById('direccion').value);
        fd.append('red_facebook',      document.getElementById('red_facebook').value);
        fd.append('red_instagram',     document.getElementById('red_instagram').value);
        fd.append('red_tiktok',        document.getElementById('red_tiktok').value);
        fd.append('red_x',             document.getElementById('red_x').value);
        fd.append('fecha_nacimiento',  document.getElementById('fecha_nacimiento').value);

        btn.disabled = true;
        btn.textContent = 'Guardando...';
        status.textContent = '';

        fetch('<?php echo esc_url(admin_url("admin-ajax.php")); ?>', {
            method: 'POST',
            body: fd,
            credentials: 'same-origin'
        })
        .then(r => r.json())
        .then(res => {
            if (res && res.success) {
                status.textContent = 'Guardado ✓';
                status.style.color = 'green';
            } else {
                status.textContent = (res && res.data && res.data.message) ? res.data.message : 'Error al guardar';
                status.style.color = 'red';
            }
        })
        .catch(() => {
            status.textContent = 'Error de comunicación con el servidor.';
            status.style.color = 'red';
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Guardar cambios';
            setTimeout(() => { status.textContent = ''; }, 2500);
        });
    });
})();
</script>

<style>
/* =========================================================
   WRAPPER LOCAL – NO USA .d-page-card
   ========================================================= */
.d-vds-perfil-page {
    background: var(--driftly-bg-elevated, #ffffff);
    border-radius: var(--driftly-radius-lg, 18px);
    box-shadow: var(--driftly-shadow-card, 0 10px 30px rgba(15, 23, 42, 0.08));
    border: 1px solid rgba(148, 163, 184, 0.25);
    padding: 22px 24px;
    max-width: 1180px;
    margin: 0 auto;
}

/* Encabezado local */
.d-vds-perfil-page__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 16px;
}

.d-vds-perfil-page__title {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0 0 4px 0;
}

.d-vds-perfil-page__subtitle {
    margin: 0;
    color: var(--driftly-text-muted, #6b7280);
    font-size: 0.9rem;
}

/* Layout general */
.vds-perfil-layout {
    display: flex;
    gap: 24px;
    align-items: flex-start;
}

/* Columna izquierda */
.vds-perfil-aside {
    width: 220px;
    flex-shrink: 0;
}

.vds-avatar-box {
    margin-bottom: 14px;
}

.vds-avatar-box img,
.vds-avatar-placeholder {
    width: 120px;
    height: 120px;
    border-radius: 999px;
    object-fit: cover;
}

.vds-avatar-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #e5e7eb;
    color: #374151;
    font-size: 2.4rem;
    font-weight: 600;
}

.vds-perfil-name {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 2px;
}

.vds-perfil-email {
    font-size: 0.85rem;
    color: #6b7280;
    word-break: break-all;
}

.vds-muted {
    margin-top: 10px;
    font-size: 0.78rem;
    color: #9ca3af;
}

/* Columna derecha */
.vds-perfil-main {
    flex: 1;
    min-width: 0;
}

/* Bloques */
.vds-block {
    margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 1px solid #e5e7eb;
}

.vds-block:last-of-type {
    border-bottom: none;
}

.vds-block-title {
    font-size: 0.95rem;
    font-weight: 600;
    margin: 0 0 10px 0;
}

/* Grid campos */
.vds-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px 16px;
}

.vds-field {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 0.8rem;
}

.vds-field label {
    color: #6b7280;
    font-size: 0.78rem;
}

.vds-field input,
.vds-field select,
.vds-field textarea {
    width: 100%;
    border-radius: 999px;
    border: 1px solid #e5e7eb;
    padding: 7px 11px;
    font-size: 0.85rem;
    outline: none;
    background: #ffffff;
}

.vds-field textarea {
    border-radius: 12px;
    min-height: 80px;
    resize: vertical;
}

.vds-field--full {
    grid-column: 1 / -1;
}

.vds-field--half {
    grid-column: span 1;
}

/* Footer */
.vds-perfil-footer {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.vds-status {
    font-size: 0.8rem;
    color: #6b7280;
}

/* ==========================
   RESPONSIVE
   ========================== */

@media (max-width: 960px) {
    .d-vds-perfil-page {
        max-width: 100%;
        margin: 0;
    }

    .vds-perfil-layout {
        flex-direction: column;
    }

    .vds-perfil-aside {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .vds-perfil-summary {
        flex: 1;
    }
}

@media (max-width: 640px) {
    /* WRAPPER A ANCHO COMPLETO EN MÓVIL */
    .d-vds-perfil-page {
        width: 100%;
        max-width: 100%;
        margin: 0;
        border-radius: 0;
        box-shadow: none;
        padding: 18px 14px;
        border-left: none;
        border-right: none;
    }

    .vds-grid {
        grid-template-columns: 1fr;
    }

    .vds-field--full,
    .vds-field--half {
        grid-column: 1 / -1;
    }

    .vds-avatar-box img,
    .vds-avatar-placeholder {
        width: 80px;
        height: 80px;
    }
}
</style>
