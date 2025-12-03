<?php
/**
 * Plantilla: Editor individual de producto (VDS)
 *
 * Variables recibidas:
 * $titulo
 * $subtitulo
 * $product_id
 * $nombre
 * $imagen
 * $descripcion_base
 * $proveedor_nombre
 * $precio_vendedor
 * $precio_sugerido
 * $precio_vds
 * $descripcion_vds
 * $orden
 * $urls
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

        <a href="<?php echo esc_url(home_url('/vds/mis-productos')); ?>"
           class="d-btn d-btn--ghost">
            ← Volver
        </a>
    </div>

    <div class="d-product-edit">

        <!-- Imagen -->
        <div class="d-product-edit__image">
            <img src="<?php echo esc_url($imagen); ?>" alt="<?php echo esc_attr($nombre); ?>">
        </div>

        <!-- Panel derecho -->
        <div class="d-product-edit__main">

            <!-- Datos WooCommerce -->
            <section class="d-section">
                <h3>Datos del proveedor</h3>

                <p class="d-text-muted">
                    Proveedor: <strong><?php echo esc_html($proveedor_nombre); ?></strong><br>
                    Precio Vendedor: <strong><?php echo wc_price($precio_vendedor); ?></strong><br>
                    Precio sugerido: <strong><?php echo wc_price($precio_sugerido); ?></strong>
                </p>
            </section>

            <!-- Descripción base -->
            <section class="d-section">
                <h3>Descripción del producto</h3>
                <div class="d-description-box">
                    <?php echo $descripcion_base; ?>
                </div>
            </section>

            <!-- Datos personalizados del VDS -->
            <section class="d-section">
                <h3>Ajustes personalizados (tu tienda)</h3>

                <div class="d-edit-grid">

                    <div class="d-field">
                        <label for="vds-precio-final">Mi precio</label>
                        <input type="number" step="0.01" id="vds-precio-final"
                               value="<?php echo esc_attr($precio_vds); ?>">
                    </div>

                    <div class="d-field">
                        <label for="vds-orden">Orden</label>
                        <input type="number" id="vds-orden"
                               value="<?php echo esc_attr($orden); ?>">
                    </div>

                    <div class="d-field d-field--full">
                        <label for="vds-descripcion">Descripción personalizada</label>
                        <textarea id="vds-descripcion" rows="4"><?php echo esc_textarea($descripcion_vds); ?></textarea>
                    </div>

                </div>
            </section>

            <!-- URLs externas -->
            <section class="d-section">
                <h3>Páginas de venta</h3>

                <?php if (empty($urls)) : ?>

                    <p class="d-text-muted">
                        Aún no existen URLs generadas para este producto.
                    </p>

                <?php else : ?>

                    <ul class="d-url-list">

                        <?php foreach ($urls as $u) : ?>
                            <li class="d-url-list__item">
                                <strong><?php echo esc_html($u['label']); ?></strong>

                                <?php if (!empty($u['es_principal'])) : ?>
                                    <span class="d-badge d-badge--primary" style="margin-left:6px;">Principal</span>
                                <?php endif; ?>

                                <input type="text"
                                       readonly
                                       value="<?php echo esc_attr($u['url']); ?>"
                                       class="d-url-input">

                                <button class="d-btn d-btn--ghost d-btn--small js-copy-url"
                                        data-url="<?php echo esc_attr($u['url']); ?>">
                                    Copiar
                                </button>
                            </li>
                        <?php endforeach; ?>

                    </ul>

                <?php endif; ?>

            </section>

            <!-- Guardar -->
            <div class="d-section">
                <button class="d-btn d-btn--primary"
                        id="vds-save-product"
                        data-id="<?php echo esc_attr($product_id); ?>">
                    Guardar cambios
                </button>

                <div id="vds-save-msg" class="d-text-muted" style="margin-top:10px;"></div>
            </div>

        </div>

    </div>

</div>

<!-- JS: Copiar URL -->
<script>
document.addEventListener("click", e => {
    if (!e.target.classList.contains("js-copy-url")) return;

    const url = e.target.dataset.url;

    navigator.clipboard.writeText(url)
        .then(() => {
            e.target.textContent = "Copiado ✓";
            setTimeout(() => e.target.textContent = "Copiar", 1500);
        });
});
</script>

<!-- JS: Guardar vía AJAX -->
<script>
document.getElementById("vds-save-product").addEventListener("click", async function () {

    const btn = this;
    const msg = document.getElementById("vds-save-msg");

    const productId = btn.dataset.id;
    const precio = document.getElementById("vds-precio-final").value;
    const orden = document.getElementById("vds-orden").value;
    const descripcion = document.getElementById("vds-descripcion").value;

    btn.disabled = true;
    btn.textContent = "Guardando...";
    msg.textContent = "";

    const response = await fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            action: "driftly_update_product_vds",
            product_id: productId,
            precio: precio,
            orden: orden,
            descripcion: descripcion
        })
    });

    const result = await response.json();

    if (result.success) {
        msg.textContent = "Cambios guardados correctamente ✓";
        msg.style.color = "green";
        btn.textContent = "Guardar cambios";
    } else {
        msg.textContent = "Error: " + (result.data?.message ?? "No se pudo guardar");
        msg.style.color = "red";
        btn.textContent = "Guardar cambios";
    }

    btn.disabled = false;
});
</script>

<style>
.d-product-edit {
    display: flex;
    gap: 30px;
}

.d-product-edit__image img {
    width: 280px;
    height: auto;
    border-radius: 12px;
}

.d-product-edit__main {
    flex: 1;
}

.d-section {
    margin-bottom: 35px;
}

.d-description-box {
    background: #fafafa;
    padding: 15px;
    border-radius: 8px;
}

.d-edit-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 18px;
}

.d-field--full {
    grid-column: span 2;
}

.d-url-list {
    list-style: disc;
    padding-left: 22px;
}

.d-url-list__item {
    margin-bottom: 14px;
}

.d-url-input {
    width: 100%;
    margin: 6px 0;
}

@media (max-width: 768px) {
    .d-product-edit {
        flex-direction: column;
    }

    .d-product-edit__image img {
        width: 100%;
    }

    .d-edit-grid {
        grid-template-columns: 1fr;
    }

    .d-field--full {
        grid-column: span 1;
    }
}
</style>
