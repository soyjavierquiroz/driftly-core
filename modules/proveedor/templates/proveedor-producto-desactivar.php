<div class="d-page-card">

    <div class="d-page-card__header">
        <div>
            <h1 class="d-page-card__title">
                <?php echo esc_html( $title ?? 'Solicitar desactivación' ); ?>
            </h1>

            <p class="d-page-card__subtitle">
                Producto: <strong><?php echo esc_html( $product->get_name() ); ?></strong>
            </p>
        </div>
    </div>

    <?php if ( ! empty( $error ) ) : ?>
        <div class="d-alert d-alert--error" style="margin-bottom: 15px;">
            <?php echo esc_html( $error ); ?>
        </div>
    <?php endif; ?>

    <?php if ( ! empty( $success ) ) : ?>
        <div class="d-alert d-alert--success" style="margin-bottom: 20px;">
            <p><strong>Solicitud enviada correctamente.</strong></p>
            <?php if ( $desactivar_en ) : ?>
                <p>Fecha solicitada de desactivación: <?php echo esc_html( $desactivar_en ); ?></p>
            <?php else : ?>
                <p>La fecha exacta de desactivación será revisada por el equipo de Driftly.</p>
            <?php endif; ?>
            <p style="margin-top:10px;">
                <a href="<?php echo esc_url( home_url( '/proveedor/productos' ) ); ?>" class="d-btn d-btn--primary">
                    Volver a mis productos
                </a>
            </p>
        </div>
    <?php endif; ?>

    <div class="d-layout-two-cols" style="display:flex;gap:24px;flex-wrap:wrap;">

        <!-- COLUMNA IZQUIERDA: resumen del producto -->
        <div class="d-col" style="flex:1;min-width:260px;max-width:360px;">

            <div class="d-card" style="background:#fff;border-radius:14px;padding:14px;box-shadow:0 3px 12px rgba(0,0,0,0.06);">
                <?php if ( ! empty( $thumb ) ) : ?>
                    <img src="<?php echo esc_url( $thumb ); ?>"
                         alt="<?php echo esc_attr( $product->get_name() ); ?>"
                         style="width:100%;border-radius:10px;object-fit:cover;margin-bottom:10px;">
                <?php endif; ?>

                <h3 style="margin:0 0 6px;font-size:16px;font-weight:600;">
                    <?php echo esc_html( $product->get_name() ); ?>
                </h3>

                <p style="margin:0 0 10px;color:#666;font-size:13px;">
                    <?php echo esc_html( $categoria ?? '-' ); ?>
                </p>

                <div style="font-size:14px;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span>SKU proveedor:</span>
                        <strong><?php echo $sku_proveedor ? esc_html( $sku_proveedor ) : '-'; ?></strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span>Precio proveedor:</span>
                        <strong>
                            <?php
                            $precio = floatval( $precio_proveedor );
                            echo $precio > 0 ? 'Bs ' . number_format( $precio, 2 ) : 'No definido';
                            ?>
                        </strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span>Stock actual:</span>
                        <strong><?php echo is_numeric( $stock ) ? intval( $stock ) : 0; ?></strong>
                    </div>
                </div>
            </div>

        </div>

        <!-- COLUMNA DERECHA: formulario -->
        <div class="d-col" style="flex:2;min-width:260px;">

            <div class="d-alert d-alert--warning" style="margin-bottom:15px;">
                ⚠ Al solicitar la desactivación de este producto, los vendedores que
                lo estén promocionando deberán ajustar sus campañas.  
                Usa esta opción solo cuando sea realmente necesario
                (sin stock, problemas de calidad, cambios de catálogo, etc.).
            </div>

            <form method="post">

                <?php wp_nonce_field( 'proveedor_desactivar_producto', 'nonce' ); ?>

                <input type="hidden" name="product_id" value="<?php echo intval( $product_id ); ?>">

                <div class="d-field">
                    <label for="motivo">Motivo de la desactivación *</label>
                    <textarea id="motivo" name="motivo" rows="5" required><?php
                        // No repoblo motivo por seguridad / UX simple
                    ?></textarea>
                </div>

                <div class="d-field">
                    <label for="tipo_desactivacion">¿Cuándo deseas que se desactive?</label>

                    <select name="tipo_desactivacion" id="tipo_desactivacion">
                        <option value="inmediato">Inmediatamente</option>
                        <option value="1">En 1 día</option>
                        <option value="3">En 3 días</option>
                        <option value="7">En 7 días</option>
                        <option value="custom">Fecha específica…</option>
                    </select>

                    <input
                        type="date"
                        name="fecha_custom"
                        id="fecha_custom"
                        style="margin-top:8px;display:none;"
                    >
                </div>

                <div style="margin-top:18px;display:flex;gap:10px;">
                    <a href="<?php echo esc_url( home_url( '/proveedor/productos' ) ); ?>"
                       class="d-btn d-btn--ghost">
                        Cancelar
                    </a>

                    <button type="submit" class="d-btn d-btn--primary">
                        Enviar solicitud
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('tipo_desactivacion');
    const inputFecha = document.getElementById('fecha_custom');

    if (!select || !inputFecha) return;

    select.addEventListener('change', function () {
        if (this.value === 'custom') {
            inputFecha.style.display = 'block';
        } else {
            inputFecha.style.display = 'none';
            inputFecha.value = '';
        }
    });
});
</script>
