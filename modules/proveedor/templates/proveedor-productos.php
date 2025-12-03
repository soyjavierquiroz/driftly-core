<div class="d-page-card">

    <div class="d-page-card__header">
        <div>
            <h1 class="d-page-card__title">
                <?php echo esc_html( $title ?? 'Mis Productos' ); ?>
            </h1>

            <?php if (!empty($role)) : ?>
                <p class="d-page-card__subtitle"><?php echo esc_html($role); ?></p>
            <?php endif; ?>
        </div>

        <div>
            <a href="/proveedor/producto/nuevo" class="d-btn d-btn--primary">
                + Nuevo Producto
            </a>
        </div>
    </div>

    <?php if (empty($productos)) : ?>

        <div class="d-placeholder">
            Aún no has creado productos como proveedor.
            <br><br>
            <a href="/proveedor/producto/nuevo" class="d-btn d-btn--primary">
                Crear mi primer producto
            </a>
        </div>

    <?php else : ?>

        <div class="d-proveedor-grid">

            <?php foreach ($productos as $p): ?>
                <div class="d-prov-card">

                    <!-- Imagen -->
                    <div class="d-prov-card__img">
                        <img src="<?php echo esc_url($p['imagen']); ?>"
                             alt="<?php echo esc_attr($p['nombre']); ?>">
                    </div>

                    <!-- Info -->
                    <div class="d-prov-card__body">

                        <h3 class="d-prov-card__title">
                            <?php echo esc_html($p['nombre']); ?>
                        </h3>

                        <p class="d-prov-card__cat">
                            <?php echo esc_html($p['categoria']); ?>
                        </p>

                        <div class="d-prov-card__row">
                            <span>SKU:</span>
                            <strong><?php echo $p['sku'] ?: '-'; ?></strong>
                        </div>

                        <div class="d-prov-card__row">
                            <span>Precio proveedor:</span>
                            <strong>
                                <?php
                                    $precio = floatval($p['precio_proveedor']);
                                    echo $precio > 0
                                        ? 'Bs ' . number_format($precio, 2)
                                        : 'No definido';
                                ?>
                            </strong>
                        </div>

                        <div class="d-prov-card__row">
                            <span>Stock:</span>
                            <strong><?php echo intval($p['stock']); ?></strong>
                        </div>

                        <!-- ESTADO -->
                        <div class="d-prov-card__row">
                            <span>Estado:</span>

                            <?php if ($p['es_publicado']): ?>
                                <strong style="color:green;">Publicado</strong>

                            <?php elseif ($p['es_pendiente']): ?>
                                <strong style="color:#F5A623;">Pendiente de aprobación</strong>

                            <?php elseif ($p['es_borrador']): ?>
                                <strong style="color:#999;">Borrador</strong>

                            <?php else: ?>
                                <strong><?php echo esc_html($p['estado_label']); ?></strong>
                            <?php endif; ?>
                        </div>

                        <!-- ALERTA: pendiente -->
                        <?php if ($p['es_pendiente']): ?>
                            <div class="d-prov-card__alert" style="background:#fff4d3;">
                                <strong>En revisión por el equipo Driftly</strong><br>
                                Este producto estará oculto para vendedores hasta su aprobación.
                            </div>
                        <?php endif; ?>

                        <!-- ALERTA: solicitud de desactivación -->
                        <?php if (!empty($p['solicitud_pendiente'])): ?>
                            <div class="d-prov-card__alert">
                                <strong>Solicitud de desactivación pendiente</strong><br>
                                <?php echo $p['solicitud_fecha'] ?: 'Fecha en revisión'; ?>
                            </div>
                        <?php endif; ?>

                    </div>

                    <!-- Acciones -->
                    <div class="d-prov-card__footer">

                        <!-- Editar -->
                        <a href="/proveedor/producto/<?php echo intval($p['id']); ?>"
                           class="d-btn d-btn--primary d-btn--small">
                            Editar
                        </a>

                        <!-- Solicitar Desactivación -->
                        <button
                            class="d-btn d-btn--ghost d-btn--small"
                            onclick="window.location.href='/proveedor/producto/<?php echo $p['id']; ?>/desactivar';"
                            <?php
                                echo $p['es_pendiente'] ? 'disabled' : '';
                                echo !empty($p['solicitud_pendiente']) ? ' disabled' : '';
                            ?>
                        >
                            <?php
                                if ($p['es_pendiente']) {
                                    echo 'En revisión';
                                } elseif (!empty($p['solicitud_pendiente'])) {
                                    echo 'Solicitud enviada';
                                } else {
                                    echo 'Solicitar desactivación';
                                }
                            ?>
                        </button>

                    </div>

                </div>
            <?php endforeach; ?>

        </div>

    <?php endif; ?>

</div>

<style>
.d-proveedor-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 24px;
    margin-top: 20px;
}
.d-prov-card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    padding: 12px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 3px 12px rgba(0,0,0,0.06);
}
.d-prov-card__img {
    width: 100%;
    height: 180px;
    border-radius: 10px;
    overflow: hidden;
    background: #fafafa;
}
.d-prov-card__img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.d-prov-card__body { margin-top: 12px; }
.d-prov-card__title { font-size: 16px; font-weight: 600; margin-bottom: 6px; }
.d-prov-card__cat { color: #666; margin-bottom: 12px; font-size: 13px; }
.d-prov-card__row { display: flex; justify-content: space-between; margin-bottom: 6px; font-size: 14px; }
.d-prov-card__alert {
    margin-top: 10px;
    padding: 8px 10px;
    background: #fff7e5;
    border-radius: 8px;
    font-size: 13px;
}
.d-prov-card__footer {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    gap: 8px;
    padding-top: 12px;
}
</style>
