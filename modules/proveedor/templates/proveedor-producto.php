<?php
/**
 * Plantilla: Crear / Editar producto de proveedor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Variables esperadas:
// $title, $is_new (bool), $product_id, $product (WC_Product o null), $acf_fields (array)
$is_new      = ! empty( $is_new );
$product_id  = $product_id ?? null;
$product     = $product ?? null;
$acf_fields  = is_array( $acf_fields ?? null ) ? $acf_fields : [];

// Valores por defecto
$titulo_value           = $product ? $product->get_name() : '';
$desc_corta_value       = $product ? $product->get_short_description() : '';
$desc_larga_value       = $product ? $product->get_description() : '';
$precio_proveedor_value = isset( $acf_fields['precio_proveedor'] ) ? $acf_fields['precio_proveedor'] : '';
$notas_value            = isset( $acf_fields['notas_logisticas'] ) ? $acf_fields['notas_logisticas'] : '';
$stock_value            = $product ? $product->get_stock_quantity() : '';

// SKU oficial WooCommerce
$sku_value = $product ? $product->get_sku() : '';

$categorias_ids = [];
if ( $product ) {
    $categorias_ids = $product->get_category_ids();
}
$cat_selected = ! empty( $categorias_ids ) ? (int) $categorias_ids[0] : 0;
?>

<div class="d-page-card">

    <div class="d-page-card__header">
        <div>
            <h1 class="d-page-card__title">
                <?php echo esc_html( $title ?? ( $is_new ? 'Crear producto' : 'Editar producto' ) ); ?>
            </h1>

            <?php if ( $product_id ) : ?>
                <p class="d-page-card__subtitle">
                    Editando el producto con ID: <?php echo intval( $product_id ); ?>
                </p>
            <?php else : ?>
                <p class="d-page-card__subtitle">
                    Creando un nuevo producto como proveedor
                </p>
            <?php endif; ?>
        </div>
    </div>

    <form id="proveedor-producto-form" method="post" enctype="multipart/form-data">

        <?php wp_nonce_field( 'guardar_producto_proveedor_nonce', 'nonce' ); ?>

        <?php if ( $product_id ) : ?>
            <input type="hidden" name="product_id" value="<?php echo intval( $product_id ); ?>">
        <?php endif; ?>

        <!-- TÍTULO -->
        <div class="d-field">
            <label>Título del producto *</label>
            <input type="text"
                   name="titulo"
                   required
                   value="<?php echo esc_attr( $titulo_value ); ?>">
        </div>

        <!-- SKU (solo lectura / automático) -->
        <div class="d-field">
            <label>SKU del producto (automático)</label>

            <?php if ( $sku_value ) : ?>
                <input type="text"
                       value="<?php echo esc_attr( $sku_value ); ?>"
                       readonly
                       style="background:#f5f5f5; cursor:not-allowed;">
                <p class="d-field__hint">
                    Este SKU se generó automáticamente en base a tu ID de proveedor y el ID del producto.
                </p>
            <?php else : ?>
                <input type="text"
                       value="Se generará automáticamente al guardar"
                       readonly
                       style="background:#f5f5f5; cursor:not-allowed;">
                <p class="d-field__hint">
                    Al guardar, crearemos un SKU con el formato <code>P{proveedor}-{producto}</code>, por ejemplo: <code>P012-000345</code>.
                </p>
            <?php endif; ?>
        </div>

        <!-- DESCRIPCIÓN CORTA -->
        <div class="d-field">
            <label>Descripción corta *</label>
            <textarea name="descripcion_corta" rows="3" required><?php
                echo esc_textarea( $desc_corta_value );
            ?></textarea>
        </div>

        <!-- DESCRIPCIÓN LARGA -->
        <div class="d-field">
            <label>Descripción larga *</label>
            <textarea name="descripcion_larga" rows="6" required><?php
                echo esc_textarea( $desc_larga_value );
            ?></textarea>
        </div>

        <!-- IMAGEN DESTACADA -->
        <div class="d-field">
            <label>Imagen destacada <?php echo $is_new ? '*' : ''; ?></label>
            <input type="file" name="imagen_destacada" accept="image/*" <?php echo $is_new ? 'required' : ''; ?>>

            <?php if ( $product && $product->get_image_id() ) : ?>
                <div style="margin-top:8px;">
                    <strong>Imagen actual:</strong><br>
                    <?php echo wp_get_attachment_image( $product->get_image_id(), 'thumbnail', false, [ 'style' => 'border-radius:6px;' ] ); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- GALERÍA -->
        <div class="d-field">
            <label>Galería de imágenes</label>
            <input type="file" name="galeria[]" accept="image/*" multiple>
        </div>

        <!-- PRECIO DEL PROVEEDOR -->
        <div class="d-field">
            <label>Precio del proveedor (Bs) *</label>
            <input type="number"
                   step="0.01"
                   name="precio_proveedor"
                   required
                   value="<?php echo esc_attr( $precio_proveedor_value ); ?>">
        </div>

        <!-- NOTAS LOGÍSTICAS -->
        <div class="d-field">
            <label>Notas logísticas</label>
            <textarea name="notas_logisticas" rows="3"><?php
                echo esc_textarea( $notas_value );
            ?></textarea>
        </div>

        <!-- STOCK -->
        <div class="d-field">
            <label>Stock *</label>
            <input type="number"
                   name="stock"
                   min="0"
                   required
                   value="<?php echo esc_attr( $stock_value ); ?>">
        </div>

        <!-- CATEGORÍA (WooCommerce) -->
        <div class="d-field">
            <label>Categoría *</label>
            <select name="categoria" required>
                <option value="">Seleccione una categoría</option>
                <?php
                $categorias = get_terms( [
                    'taxonomy'   => 'product_cat',
                    'hide_empty' => false,
                ] );

                if ( ! empty( $categorias ) && ! is_wp_error( $categorias ) ) {
                    foreach ( $categorias as $cat ) {
                        printf(
                            '<option value="%d" %s>%s</option>',
                            $cat->term_id,
                            selected( $cat_selected, $cat->term_id, false ),
                            esc_html( $cat->name )
                        );
                    }
                }
                ?>
            </select>
        </div>

        <!-- BOTONES -->
        <div style="margin-top: 16px;">
            <button class="d-btn d-btn--primary" type="submit">
                <?php echo $product_id ? 'Actualizar producto' : 'Crear producto'; ?>
            </button>

            <a href="<?php echo esc_url( home_url( '/proveedor/productos' ) ); ?>"
               class="d-btn d-btn--ghost"
               style="margin-left:8px;">
                Volver al listado
            </a>
        </div>

    </form>

</div>
