<div class="d-page-card">

    <div class="d-page-card__header">
        <div>
            <h1 class="d-page-card__title">
                <?php echo esc_html($title ?? 'Editar Producto'); ?>
            </h1>

            <p class="d-page-card__subtitle">
                Editando el producto con ID: <?php echo intval($product_id ?? 0); ?>
            </p>
        </div>
    </div>

    <div class="d-placeholder">
        Aquí irá el editor detallado del producto del proveedor.
    </div>

</div>
