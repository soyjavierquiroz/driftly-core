<?php
// Variables disponibles:
// $productos, $categorias, $proveedores, $filtros, $paginacion
?>

<div class="d-page-card">

    <div class="d-page-card__header">
        <div>
            <h1 class="d-page-card__title">
                <?php echo esc_html( $titulo ); ?>
            </h1>
            <p class="d-page-card__subtitle">
                <?php echo esc_html( $subtitulo ); ?>
            </p>
        </div>
    </div>

    <!-- Filtros -->
    <form method="get" action="<?php echo esc_url( home_url( '/vds/catalogo' ) ); ?>" class="d-catalog-toolbar">
        <div class="d-catalog-toolbar__left">

            <div class="d-field">
                <input
                    type="text"
                    name="s"
                    placeholder="Buscar producto…"
                    value="<?php echo esc_attr( $filtros['search'] ?? '' ); ?>"
                />
            </div>

            <div class="d-field">
                <select name="cat">
                    <option value="">Todas las categorías</option>
                    <?php foreach ( $categorias as $cat ) : ?>
                        <option
                            value="<?php echo esc_attr( $cat->term_id ); ?>"
                            <?php selected( $filtros['cat'], $cat->term_id ); ?>
                        >
                            <?php echo esc_html( $cat->name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="d-field">
                <select name="proveedor">
                    <option value="">Todos los proveedores</option>
                    <?php foreach ( $proveedores as $prov ) : ?>
                        <option
                            value="<?php echo esc_attr( $prov->ID ); ?>"
                            <?php selected( $filtros['proveedor'], $prov->ID ); ?>
                        >
                            <?php echo esc_html( $prov->display_name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>

        <div class="d-catalog-toolbar__right">
            <button class="d-btn d-btn--ghost" type="submit">Filtrar</button>
        </div>
    </form>

    <!-- Grid de tarjetas -->
    <div class="d-card-grid">

        <?php if ( empty( $productos ) ) : ?>

            <div class="d-placeholder">
                No se encontraron productos con los filtros seleccionados.
            </div>

        <?php else : ?>

            <?php foreach ( $productos as $p ) : ?>
                <div
                    class="d-product-card"
                    data-product-id="<?php echo esc_attr( $p['id'] ); ?>"
                    data-activo="<?php echo $p['activo'] ? '1' : '0'; ?>"
                >
                    <div class="d-product-card__image-wrap">
                        <?php if ( ! empty( $p['imagen'] ) ) : ?>
                            <img
                                src="<?php echo esc_url( $p['imagen'] ); ?>"
                                alt="<?php echo esc_attr( $p['nombre'] ); ?>"
                                class="d-product-card__image"
                            />
                        <?php endif; ?>
                    </div>

                    <div class="d-product-card__body">
                        <div class="d-product-card__title">
                            <?php echo esc_html( $p['nombre'] ); ?>
                        </div>
                        <div class="d-product-card__provider">
                            <?php echo esc_html( $p['proveedor_nombre'] ); ?>
                        </div>

                        <div class="d-product-card__prices">
                            <div class="d-product-card__price-row">
                                <span class="label">Mayorista</span>
                                <span class="value">
                                    <?php echo wc_price( $p['precio_mayorista'] ); ?>
                                </span>
                            </div>
                            <div class="d-product-card__price-row">
                                <span class="label">Sugerido</span>
                                <span class="value">
                                    <?php echo wc_price( $p['precio_sugerido'] ); ?>
                                </span>
                            </div>

                            <?php if ( $p['activo'] ) : ?>
                                <div class="d-product-card__price-row">
                                    <span class="label">Mi precio</span>
                                    <span class="value js-my-price">
                                        <?php echo wc_price( $p['precio_vds'] ); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-product-card__footer">
                            <div class="d-product-card__status">
                                <?php if ( $p['activo'] ) : ?>
                                    <span class="d-badge d-badge--success">En mi tienda</span>
                                <?php else : ?>
                                    <span class="d-badge d-badge--danger">No agregado</span>
                                <?php endif; ?>
                            </div>

                            <div class="d-product-card__actions">
                                <button
                                    type="button"
                                    class="d-btn d-btn--primary js-product-open"
                                    data-id="<?php echo esc_attr( $p['id'] ); ?>"
                                >
                                    <?php echo $p['activo'] ? 'Editar' : 'Agregar'; ?>
                                </button>

                                <?php if ( $p['activo'] ) : ?>
                                    <button
                                        type="button"
                                        class="d-btn d-btn--ghost js-product-toggle"
                                        data-id="<?php echo esc_attr( $p['id'] ); ?>"
                                    >
                                        Desactivar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                </div>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <!-- Paginación simple -->
    <?php if ( $paginacion['total'] > 1 ) : ?>
        <div class="d-pagination">
            <?php
            $base_url   = home_url( '/vds/catalogo' );
            $base_args  = [
                's'         => $filtros['search'] ?? '',
                'cat'       => $filtros['cat'] ?? '',
                'proveedor' => $filtros['proveedor'] ?? '',
            ];
            $current = (int) $paginacion['current'];
            $total   = (int) $paginacion['total'];
            ?>

            <?php if ( $current > 1 ) : ?>
                <a
                    class="d-pagination__link"
                    href="<?php echo esc_url( add_query_arg( array_merge( $base_args, [ 'paged' => $current - 1 ] ), $base_url ) ); ?>"
                >
                    ← Anterior
                </a>
            <?php endif; ?>

            <span class="d-pagination__current">
                Página <?php echo esc_html( $current ); ?> de <?php echo esc_html( $total ); ?>
            </span>

            <?php if ( $current < $total ) : ?>
                <a
                    class="d-pagination__link"
                    href="<?php echo esc_url( add_query_arg( array_merge( $base_args, [ 'paged' => $current + 1 ] ), $base_url ) ); ?>"
                >
                    Siguiente →
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>

<!-- MODAL DETALLE PRODUCTO -->
<div id="d-product-modal" class="d-modal" style="display:none;">
    <div class="d-modal__backdrop js-d-modal-close"></div>
    <div class="d-modal__dialog">
        <button type="button" class="d-modal__close js-d-modal-close">×</button>

        <div class="d-modal__content">
            <div class="d-modal__header">
                <div class="d-modal__image-wrap">
                    <img src="" alt="" id="dpm-image" />
                </div>
                <div class="d-modal__title-block">
                    <h2 id="dpm-title"></h2>
                    <p id="dpm-provider" class="d-text-muted"></p>
                </div>
            </div>

            <div class="d-modal__body">
                <div class="d-modal__section">
                    <h4>Precios base</h4>
                    <p class="d-text-muted">
                        Mayorista: <span id="dpm-mayorista"></span><br>
                        Sugerido: <span id="dpm-sugerido"></span>
                    </p>
                </div>

                <div class="d-modal__section">
                    <h4>Descripción del producto</h4>
                    <div id="dpm-descripcion-base" class="d-modal__description"></div>
                </div>

                <div class="d-modal__section">
                    <h4>Ajustes para tu tienda</h4>

                    <div class="d-modal__form-grid">
                        <div class="d-field">
                            <label for="dpm-precio-final">Mi precio</label>
                            <input type="number" step="0.01" id="dpm-precio-final" />
                        </div>

                        <div class="d-field">
                            <label for="dpm-orden">Orden</label>
                            <input type="number" id="dpm-orden" />
                        </div>

                        <div class="d-field d-field--full">
                            <label for="dpm-descripcion-vds">Descripción personalizada</label>
                            <textarea id="dpm-descripcion-vds" rows="3"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-modal__footer">
                <button
                    type="button"
                    class="d-btn d-btn--ghost js-d-modal-close"
                >
                    Cancelar
                </button>

                <button
                    type="button"
                    class="d-btn d-btn--primary"
                    id="dpm-save"
                >
                    Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>
