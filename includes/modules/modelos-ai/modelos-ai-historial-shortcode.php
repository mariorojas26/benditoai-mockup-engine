<?php
if (!function_exists('benditoai_modelos_ai_get_outfit_catalog')) {
    function benditoai_modelos_ai_get_outfit_catalog() {
        $catalog = array(
            array(
                'id' => 'outfit_casual_olive',
                'name' => 'Casual Olive',
                'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/vistemodelo.png',
                'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/vistemodelo.png',
                'prompt_hint' => 'Use this outfit reference as primary wardrobe.'
            ),
            array(
                'id' => 'outfit_street_dark',
                'name' => 'Street Dark',
                'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/vendemodelo.png',
                'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/vendemodelo.png',
                'prompt_hint' => 'Replicate this streetwear outfit faithfully.'
            ),
            array(
                'id' => 'outfit_editorial_clean',
                'name' => 'Editorial Clean',
                'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/creamodelo.png',
                'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/creamodelo.png',
                'prompt_hint' => 'Use this outfit for a clean editorial wardrobe.'
            ),
            array(
                'id' => 'outfit_urban_core',
                'name' => 'Urban Core',
                'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/crea3.png',
                'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/crea3.png',
                'prompt_hint' => 'Match this urban outfit composition and style.'
            ),
        );

        return apply_filters('benditoai_modelos_ai_outfit_catalog', $catalog);
    }
}

function benditoai_modelos_ai_historial_shortcode() {

    if (!is_user_logged_in()) {
        return '<p class="benditoai-message">Debes iniciar sesion para ver tus modelos AI.</p>';
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'benditoai_modelos_ai';

    $historial = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name
             WHERE user_id = %d
             ORDER BY created_at DESC, id DESC",
            $user_id
        )
    );

    $sin_modelos = (!is_array($historial) || count($historial) === 0);
    $campaign_url = apply_filters('benditoai_modelos_ai_campaign_url', home_url('/crea-campana/'));
    $icon_download = BENDIDOAI_PLUGIN_URL . 'assets/images/icon-download.png';
    $icon_edit = BENDIDOAI_PLUGIN_URL . 'assets/images/icon-edit.png';
    $icon_delete = BENDIDOAI_PLUGIN_URL . 'assets/images/icon-delete.png';
    $outfit_catalog = benditoai_modelos_ai_get_outfit_catalog();
    $outfit_catalog_json = wp_json_encode($outfit_catalog);

    ob_start();
    ?>
    <div
        class="benditoai-wrapper-historia-modelos"
        data-campaign-url="<?php echo esc_url($campaign_url); ?>"
        data-outfit-catalog="<?php echo esc_attr($outfit_catalog_json ? $outfit_catalog_json : '[]'); ?>"
    >
        <div class="benditoai-historial-toolbar" aria-hidden="true">
            <a href="<?php echo esc_url(home_url('/crea-modelo/')); ?>" class="benditoai-historial-new-model">+ Crear nuevo modelo</a>
            <button type="button" class="benditoai-historial-toolbar-icon" tabindex="-1">
                <i class="fas fa-bell" aria-hidden="true"></i>
            </button>
        </div>

        <h2 class="benditoai-historial-title">Mis Modelos AI</h2>

        <?php if ($sin_modelos): ?>
            <p class="benditoai-message" id="benditoai-empty-message">
                No has generado ningun modelo todavia.
            </p>
        <?php endif; ?>

        <div class="benditoai-historial-grid" id="benditoai-historial-mockups">

            <?php if (!$sin_modelos): ?>
                <?php foreach ($historial as $item): ?>

                <?php
                $modo_label = (isset($item->modo_creacion) && $item->modo_creacion === 'referencia')
                    ? 'Imagen de referencia'
                    : 'Rasgos';
                $visibilidad_label = (isset($item->perfil_publico) && (int) $item->perfil_publico === 1)
                    ? 'Publico'
                    : 'Privado';
                $estado_label = ((int) ($item->perfil_publico ?? 0) === 1) ? 'Activo' : 'Privado';
                $descripcion_panel = trim((string) ($item->descripcion_modelo ?? ''));
                if ($descripcion_panel === '') {
                    $descripcion_panel = 'Modelo AI listo para campanas de moda, redes y lookbooks.';
                }
                ?>

                <div class="benditoai-historial-item" data-id="<?php echo esc_attr($item->id); ?>">

                    <p class="benditoai-historial-name">
                        <?php echo esc_html($item->nombre_modelo); ?>
                    </p>

                    <?php if (!empty($item->image_url)): ?>
                        <div class="benditoai-img-wrap">
                            <img
                                src="<?php echo esc_url($item->image_url); ?>"
                                alt="<?php echo esc_attr($item->nombre_modelo); ?>"
                                class="benditoai-historial-img"
                            />

                            <div class="benditoai-inline-edit" hidden>
                                <div class="benditoai-inline-edit-surface">
                                    <label class="benditoai-inline-edit-label" for="benditoai-inline-edit-text-<?php echo esc_attr($item->id); ?>">
                                        Que deseas cambiar
                                    </label>
                                    <textarea
                                        id="benditoai-inline-edit-text-<?php echo esc_attr($item->id); ?>"
                                        class="benditoai-inline-edit-text"
                                        placeholder="Ej: cambia solo la chaqueta por una bomber negra."
                                    ></textarea>

                                    <div class="benditoai-inline-edit-ref-block">
                                        <p class="benditoai-inline-edit-ref-help">
                                            Opcional: sube una imagen de prenda o elige un outfit sugerido para usarlo como referencia de vestuario.
                                        </p>
                                        <input
                                            type="file"
                                            class="benditoai-inline-edit-ref-file"
                                            accept="image/png,image/jpeg,image/webp"
                                            hidden
                                        >
                                        <button type="button" class="benditoai-inline-edit-ref-trigger">
                                            <i class="fas fa-plus" aria-hidden="true"></i>
                                            <span class="benditoai-inline-edit-ref-trigger-preview" hidden>
                                                <img src="" alt="" class="benditoai-inline-edit-ref-trigger-preview-img" />
                                            </span>
                                            <span class="benditoai-inline-edit-ref-trigger-text">Una prenda de vestir (opcional)</span>
                                        </button>
                                        <p class="benditoai-inline-edit-ref-name"></p>
                                    </div>

                                    <div class="benditoai-inline-edit-submit-block">
                                        <div class="benditoai-inline-edit-actions">
                                            <button type="button" class="benditoai-inline-edit-submit">Enviar</button>
                                            <button type="button" class="benditoai-inline-edit-cancel">Volver</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="benditoai-action-buttons">
                                <div class="hoverselect">
                                    <a href="<?php echo esc_url($item->image_url); ?>" download class="benditoai-btn benditoai-btn--download benditoai-icon-btn" aria-label="Descargar modelo">
                                        <img
                                            src="<?php echo esc_url($icon_download); ?>"
                                            class="benditoai-btn-icon"
                                            alt=""
                                            aria-hidden="true"
                                        />
                                    </a>
                                </div>

                                <div class="hoverselect">
                                    <button class="benditoai-edit-modelo-btn benditoai-icon-btn" data-id="<?php echo esc_attr($item->id); ?>" data-image="<?php echo esc_url($item->image_url); ?>" aria-label="Editar modelo">
                                        <img
                                            src="<?php echo esc_url($icon_edit); ?>"
                                            class="benditoai-btn-icon"
                                            alt=""
                                            aria-hidden="true"
                                        />
                                    </button>
                                </div>

                                <div class="hoverselect">
                                    <button class="benditoai-delete-modelo-btn benditoai-action-btn benditoai-icon-btn" data-id="<?php echo esc_attr($item->id); ?>" aria-label="Eliminar modelo">
                                        <img
                                            src="<?php echo esc_url($icon_delete); ?>"
                                            class="benditoai-btn-icon"
                                            alt=""
                                            aria-hidden="true"
                                        />
                                    </button>
                                </div>
                            </div>

                            <button
                                type="button"
                                class="benditoai-use-campaign-btn"
                                data-modelo-id="<?php echo esc_attr($item->id); ?>"
                                data-modelo-nombre="<?php echo esc_attr($item->nombre_modelo); ?>"
                                data-modelo-image="<?php echo esc_url($item->image_url); ?>"
                            >
                                Usar para campaña
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="benditoai-desktop-model-panel">
                        <div class="benditoai-desktop-model-head">
                            <h3><?php echo esc_html($item->nombre_modelo); ?></h3>
                            <div class="benditoai-desktop-model-badges">
                                <span class="benditoai-model-badge benditoai-model-badge--status"><?php echo esc_html($estado_label); ?></span>
                                <?php if (!empty($item->estilo)): ?>
                                    <span class="benditoai-model-badge"><?php echo esc_html($item->estilo); ?></span>
                                <?php endif; ?>
                                <span class="benditoai-model-badge">Listo para campana</span>
                            </div>
                            <p><?php echo esc_html($descripcion_panel); ?></p>
                        </div>

                        <div class="benditoai-desktop-model-actions">
                            <p class="benditoai-desktop-model-section-label">Accion principal</p>
                            <button
                                type="button"
                                class="benditoai-use-campaign-btn benditoai-use-campaign-btn--panel"
                                data-modelo-id="<?php echo esc_attr($item->id); ?>"
                                data-modelo-nombre="<?php echo esc_attr($item->nombre_modelo); ?>"
                                data-modelo-image="<?php echo esc_url($item->image_url); ?>"
                            >
                                Usar para campana <span aria-hidden="true">&rarr;</span>
                            </button>

                            <p class="benditoai-desktop-model-section-label">Herramientas</p>
                            <div class="benditoai-desktop-model-secondary">
                                <button class="benditoai-edit-modelo-btn" data-id="<?php echo esc_attr($item->id); ?>" data-image="<?php echo esc_url($item->image_url); ?>">
                                    <i class="fas fa-pen" aria-hidden="true"></i>
                                    <span>Editar modelo</span>
                                </button>
                                <a href="<?php echo esc_url($item->image_url); ?>" download>
                                    <i class="fas fa-download" aria-hidden="true"></i>
                                    <span>Descargar</span>
                                </a>
                            </div>

                            <?php if (!empty($outfit_catalog) && is_array($outfit_catalog)): ?>
                                <div class="benditoai-desktop-outfit-thumbs" data-outfit-catalog-rail>
                                    <div class="benditoai-desktop-outfit-thumbs-head">
                                        <span>Outfits sugeridos</span>
                                        <small>Global</small>
                                    </div>
                                    <div class="benditoai-desktop-outfit-thumbs-list">
                                        <?php foreach ($outfit_catalog as $outfit): ?>
                                            <?php
                                            $outfit_id = isset($outfit['id']) ? (string) $outfit['id'] : '';
                                            $outfit_name = isset($outfit['name']) ? (string) $outfit['name'] : 'Outfit';
                                            $outfit_thumb = isset($outfit['thumb_url']) ? (string) $outfit['thumb_url'] : '';
                                            $outfit_ref = isset($outfit['reference_url']) ? (string) $outfit['reference_url'] : $outfit_thumb;
                                            $outfit_hint = isset($outfit['prompt_hint']) ? (string) $outfit['prompt_hint'] : '';
                                            if ($outfit_id === '' || $outfit_thumb === '') {
                                                continue;
                                            }
                                            ?>
                                            <button
                                                type="button"
                                                class="benditoai-outfit-thumb"
                                                data-outfit-id="<?php echo esc_attr($outfit_id); ?>"
                                                data-outfit-label="<?php echo esc_attr($outfit_name); ?>"
                                                data-outfit-reference="<?php echo esc_url($outfit_ref); ?>"
                                                data-outfit-prompt="<?php echo esc_attr($outfit_hint); ?>"
                                                aria-label="<?php echo esc_attr('Usar outfit: ' . $outfit_name); ?>"
                                                aria-pressed="false"
                                            >
                                                <img src="<?php echo esc_url($outfit_thumb); ?>" alt="<?php echo esc_attr($outfit_name); ?>" loading="lazy" />
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="benditoai-desktop-model-meta">
                            <div class="benditoai-desktop-model-box">
                                <h4>Atributos</h4>
                                <p><span>Complexion</span><strong><?php echo esc_html(!empty($item->cuerpo) ? $item->cuerpo : 'Personalizable'); ?></strong></p>
                                <p><span>Edad aparente</span><strong><?php echo esc_html(!empty($item->edad) ? $item->edad : 'Variable'); ?></strong></p>
                                <p><span>Origen</span><strong><?php echo esc_html(!empty($item->nacionalidad) ? $item->nacionalidad : (!empty($item->etnia) ? $item->etnia : 'Global')); ?></strong></p>
                            </div>

                            <div class="benditoai-desktop-model-box">
                                <h4>Campanas</h4>
                                <p><span>Estado</span><strong>Disponible</strong></p>
                                <p><span>Uso sugerido</span><strong>Moda y redes</strong></p>
                                <p><span>Flujo</span><strong><?php echo esc_html($modo_label); ?></strong></p>
                            </div>
                        </div>
                    </div>

                    <div class="benditoai-edit-decision" hidden>
                        <button type="button" class="benditoai-edit-apply-btn">Conservar</button>
                        <button type="button" class="benditoai-edit-discard-btn">Deshacer</button>
                    </div>

                    <button class="benditoai-toggle-info">Ver detalles</button>

                    <div class="benditoai-historial-info" style="display:none;">
                        <p><strong>Flujo:</strong> <?php echo esc_html($modo_label); ?></p>
                        <p><strong>Genero:</strong> <?php echo esc_html($item->genero); ?></p>
                        <p><strong>Edad:</strong> <?php echo esc_html($item->edad); ?></p>
                        <p><strong>Estilo:</strong> <?php echo esc_html($item->estilo); ?></p>
                        <p><strong>Visibilidad:</strong> <?php echo esc_html($visibilidad_label); ?></p>

                        <p>
                            <strong>Creado:</strong>
                            <?php
                            echo (!empty($item->created_at) && $item->created_at !== '0000-00-00 00:00:00')
                                ? esc_html(date('d/m/Y H:i', strtotime($item->created_at)))
                                : 'Fecha no disponible';
                            ?>
                        </p>
                    </div>

                </div>

                <?php endforeach; ?>
            <?php endif; ?>

        </div>
        <p class="benditoai-historial-scroll-hint" id="benditoai-historial-scroll-hint" hidden>
            <i class="fas fa-arrows-alt-h" aria-hidden="true"></i>
            Desliza para ver mas modelos
        </p>

        <div class="benditoai-historial-pagination" id="benditoai-historial-pagination" hidden>
            <button type="button" class="benditoai-historial-page-btn" data-history-page="prev" aria-label="Ver modelos anteriores">
                <span aria-hidden="true">&lsaquo;</span>
            </button>
            <p class="benditoai-historial-page-status" data-history-page="status">1 / 1</p>
            <button type="button" class="benditoai-historial-page-btn" data-history-page="next" aria-label="Ver mas modelos">
                <span aria-hidden="true">&rsaquo;</span>
            </button>
        </div>
    </div>

    <?php

    return ob_get_clean();
}
add_shortcode('benditoai_modelos_ai_historial', 'benditoai_modelos_ai_historial_shortcode');
