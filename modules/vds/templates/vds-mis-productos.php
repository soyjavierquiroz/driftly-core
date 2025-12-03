<?php
/**
 * Plantilla: Mis Productos (VDS)
 */

if (!defined('ABSPATH')) exit;
?>

<div class="d-page-card">

    <div class="d-page-card__header">
        <div>
            <h1 class="d-page-card__title"><?php echo esc_html($titulo); ?></h1>
            <p class="d-page-card__subtitle"><?php echo esc_html($subtitulo); ?></p>
        </div>
    </div>

    <?php if (empty($productos)) : ?>

        <div class="d-placeholder">
            Aún no tienes productos activos en tu tienda.
        </div>

    <?php else : ?>

        <div class="d-card-list">

            <?php foreach ($productos as $p) : ?>
                <div class="d-product-card d-product-card--wide d-vds-product">

                    <!-- Imagen -->
                    <div class="d-vds-product__image-wrap">
                        <img src="<?php echo esc_url($p['imagen']); ?>"
                             alt="<?php echo esc_attr($p['nombre']); ?>">
                    </div>

                    <!-- COLUMNA DERECHA -->
                    <div class="d-vds-product__info">

                        <!-- Título -->
                        <h3 class="d-vds-product__title"><?php echo esc_html($p['nombre']); ?></h3>
                        <p class="d-vds-product__provider"><?php echo esc_html($p['proveedor_nombre']); ?></p>

                        <!-- Precios -->
                        <div class="d-vds-product__prices">

                            <div class="row">
                                <span>Vendedor</span>
                                <strong><?php echo wc_price($p['precio_vendedor']); ?></strong>
                            </div>

                            <div class="row">
                                <span>Sugerido</span>
                                <strong><?php echo wc_price($p['precio_sugerido']); ?></strong>
                            </div>

                            <div class="row">
                                <span>Mi precio</span>
                                <strong><?php echo wc_price($p['precio_vds']); ?></strong>
                            </div>

                        </div>

                        <!-- URLs -->
                        <div class="d-vds-product__urls">
                            <h4>Páginas de venta</h4>

                            <?php if (empty($p['urls'])) : ?>

                                <p class="d-text-muted">No hay URLs generadas todavía.</p>

                            <?php else : ?>

                                <ul class="d-url-list">
                                    <?php foreach ($p['urls'] as $url) : ?>
                                        <li class="d-url-list__item">

                                            <strong><?php echo esc_html($url['label']); ?></strong>

                                            <input type="text"
                                                   readonly
                                                   value="<?php echo esc_attr($url['url']); ?>"
                                                   class="d-url-input">

                                            <button class="d-btn d-btn--ghost d-btn--small js-copy-url"
                                                    data-url="<?php echo esc_attr($url['url']); ?>">
                                                Copiar
                                            </button>

                                        </li>
                                    <?php endforeach; ?>
                                </ul>

                            <?php endif; ?>
                        </div>

                        <!-- Footer -->
                        <div class="d-vds-product__footer">
                            <a href="<?php echo esc_url(home_url('/vds/producto/'.$p['id'])); ?>"
                               class="d-btn d-btn--primary">
                                Editar
                            </a>
                        </div>

                    </div>

                </div>
            <?php endforeach; ?>

        </div>
    <?php endif; ?>

</div>




<!-- JS: Copiar URL -->
<script>
document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('js-copy-url')) return;
    const url = e.target.dataset.url;
    navigator.clipboard.writeText(url)
        .then(() => {
            e.target.textContent = "Copiado ✓";
            setTimeout(() => { e.target.textContent = "Copiar"; }, 1500);
        });
});
</script>



<!-- CSS -->
<style>
/* LISTA GENERAL */
.d-card-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-top: 15px;
}

/* CARD RESPONSIVE */
.d-vds-product {
    display: flex;
    flex-direction: row;
    gap: 25px;
    width: 100%;
    padding: 20px;
}

/* IMAGEN */
.d-vds-product__image-wrap {
    width: 40%;
    max-width: 380px;
    border-radius: 12px;
    overflow: hidden;
}

.d-vds-product__image-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* INFO */
.d-vds-product__info {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* TITULO */
.d-vds-product__title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 4px;
}

.d-vds-product__provider {
    color: #666;
    margin-bottom: 14px;
}

/* PRECIOS */
.d-vds-product__prices .row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 6px;
}

/* URLs */
.d-url-list {
    list-style: disc;
    padding-left: 20px;
    margin-top: 10px;
}

.d-url-list__item {
    margin-bottom: 15px;
}

.d-url-input {
    width: 100%;
    margin: 6px 0 8px;
}

/* FOOTER */
.d-vds-product__footer {
    margin-top: 20px;
}

/* MOBILE */
@media (max-width: 768px) {

    .d-vds-product {
        flex-direction: column;
        padding: 15px;
    }

    .d-vds-product__image-wrap {
        width: 100%;
        max-width: none;
    }

    .d-vds-product__info {
        width: 100%;
    }
}
</style>
