<div class="d-page-card">

    <div class="d-page-card__header">
        <div>
            <h1 class="d-page-card__title">
                <?php echo esc_html($title ?? 'Mis Productos'); ?>
            </h1>

            <?php if (!empty($role)) : ?>
                <p class="d-page-card__subtitle">
                    <?php echo esc_html($role); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <div class="d-placeholder">
        Aquí aparecerá el listado de productos del proveedor.
    </div>

</div>
