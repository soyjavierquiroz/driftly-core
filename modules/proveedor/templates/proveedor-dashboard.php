<div class="d-page-card">

    <div class="d-page-card__header">
        <div>
            <h1 class="d-page-card__title">
                <?php echo esc_html($title ?? 'Dashboard'); ?>
            </h1>

            <?php if (!empty($role)) : ?>
                <p class="d-page-card__subtitle">
                    Rol: <?php echo esc_html($role); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-placeholder">
        Aquí irá el contenido real del dashboard del proveedor.<br>
        KPIs, productos activos, pedidos, etc.
    </div>

</div>
