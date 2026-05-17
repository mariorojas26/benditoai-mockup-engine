<?php
if (!function_exists('benditoai_modelos_ai_get_outfit_catalog')) {
    function benditoai_modelos_ai_get_outfit_catalog() {
       $catalog = array(
    array(
        'id' => 'hip-hop',
        'name' => 'Hip-hop',
        'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/hip-hop.png',
        'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/hip-hop.png',
        'prompt_hint' => 'Aplica un look inspirado en el hip-hop, con prendas urbanas llamativas y una actitud segura.'
    ),
    array(
        'id' => 'streetwear',
        'name' => 'Streetwear',
        'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/streetwear.png',
        'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/streetwear.png',
        'prompt_hint' => 'Prioriza un outfit streetwear contemporaneo, con capas y actitud urbana.'
    ),
    array(
        'id' => 'urbano',
        'name' => 'Urbano',
        'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/urbano.png',
        'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/urbano.png',
        'prompt_hint' => 'Usa un estilo urbano limpio, con siluetas modernas y prendas funcionales para el dia a dia.'
    ),
    array(
        'id' => 'elegante',
        'name' => 'Elegante',
        'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/elegante.png',
        'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/elegante.png',
        'prompt_hint' => 'Manten un estilismo elegante y refinado, con prendas pulidas y acabado visual premium.'
    ),
    array(
        'id' => 'minimalista',
        'name' => 'Minimalista',
        'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/minimalista.png',
        'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/minimalista.png',
        'prompt_hint' => 'Aplica una direccion minimalista, con paleta reducida y composicion limpia.'
    ),
    array(
        'id' => 'deportivo',
        'name' => 'Deportivo',
        'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/deportivo.png',
        'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/deportivo.png',
        'prompt_hint' => 'Usa una direccion deportiva, con influencia activewear y estilismo dinamico.'
    ),
    array(
        'id' => 'casual',
        'name' => 'Casual',
        'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/casual.png',
        'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/casual.png',
        'prompt_hint' => 'Manten un vestuario casual y versatil para contenido social y ecommerce.'
    ),
    array(
        'id' => 'editorial',
        'name' => 'Editorial',
        'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/editorial.png',
        'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/editorial.png',
        'prompt_hint' => 'Usa un tono editorial de moda, con prendas estilizadas y narrativa visual de alto impacto.'
    ),
    array(
        'id' => 'oversize',
        'name' => 'Oversize',
        'thumb_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/oversize.png',
        'reference_url' => BENDIDOAI_PLUGIN_URL . 'assets/images/estilosDeModelo/oversize.png',
        'prompt_hint' => 'Aplica una silueta oversize urbana, con ajuste relajado, proporciones en capas y estilismo contemporaneo.'
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
    $modelo_ids = array();
    foreach ((array) $historial as $historial_item) {
        if (!empty($historial_item->id)) {
            $modelo_ids[] = (int) $historial_item->id;
        }
    }
    $saved_outfits_by_model = function_exists('benditoai_modelos_ai_get_saved_outfits_grouped')
        ? benditoai_modelos_ai_get_saved_outfits_grouped($user_id, $modelo_ids)
        : array();
    $outfit_limit = function_exists('benditoai_modelo_outfit_limit')
        ? benditoai_modelo_outfit_limit($user_id)
        : 1;
    $outfit_limit_warning = function_exists('benditoai_modelo_outfit_warning')
        ? benditoai_modelo_outfit_warning()
        : 'Has alcanzado el lÃ­mite de outfits para este modelo.';

    $plan_data = function_exists('benditoai_get_user_plan_data')
        ? benditoai_get_user_plan_data($user_id)
        : array('max_modelos' => 3);
    $model_limit = max(0, (int) ($plan_data['max_modelos'] ?? 3));
    $model_count = is_array($historial) ? count($historial) : 0;
    $model_limit_warning = 'Maximo de modelos alcanzado. Actualiza tu plan para crear mas.';

    ob_start();
    ?>
    <div class="benditoai-modelos-ai-shell">
        <div class="benditoai-historial-toolbar">
            <a href="<?php echo esc_url(home_url('/crea-modelo/')); ?>" class="benditoai-historial-new-model">+ Crear nuevo modelo</a>
        </div>

        <div
            class="benditoai-wrapper-historia-modelos"
            data-campaign-url="<?php echo esc_url($campaign_url); ?>"
            data-outfit-catalog="<?php echo esc_attr($outfit_catalog_json ? $outfit_catalog_json : '[]'); ?>"
            data-outfit-limit="<?php echo esc_attr($outfit_limit); ?>"
            data-outfit-warning="<?php echo esc_attr($outfit_limit_warning); ?>"
            data-create-model-url="<?php echo esc_url(home_url('/crea-modelo/')); ?>"
            data-model-count="<?php echo esc_attr($model_count); ?>"
            data-model-limit="<?php echo esc_attr($model_limit); ?>"
            data-model-warning="<?php echo esc_attr($model_limit_warning); ?>"
        >
        <h2 class="benditoai-historial-title benditoai-historial-title-1">Mis Modelos</h2>

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
                $saved_outfits = isset($saved_outfits_by_model[(int) $item->id]) && is_array($saved_outfits_by_model[(int) $item->id])
                    ? $saved_outfits_by_model[(int) $item->id]
                    : array();
                $saved_outfits_count = count($saved_outfits);
                $can_save_outfit = $saved_outfits_count < (int) $outfit_limit;
                $principal_outfit = null;
                foreach ($saved_outfits as $candidate_outfit) {
                    if ((string) ($candidate_outfit->outfit_tag ?? '') === 'principal') {
                        $principal_outfit = $candidate_outfit;
                        break;
                    }
                }
                if (!$principal_outfit && !empty($saved_outfits)) {
                    $principal_outfit = $saved_outfits[0];
                }

                $display_image = !empty($principal_outfit->image_url)
                    ? (string) $principal_outfit->image_url
                    : (string) ($item->image_url ?? '');
                $display_name = !empty($principal_outfit->nombre_outfit)
                    ? (string) $principal_outfit->nombre_outfit
                    : (string) $item->nombre_modelo;
                $principal_outfit_id = (int) ($principal_outfit->id ?? 0);
                ?>

                <div
                    class="benditoai-historial-item"
                    data-id="<?php echo esc_attr($item->id); ?>"
                    data-outfit-count="<?php echo esc_attr($saved_outfits_count); ?>"
                    data-outfit-limit="<?php echo esc_attr($outfit_limit); ?>"
                    data-outfit-warning="<?php echo esc_attr($outfit_limit_warning); ?>"
                    data-principal-outfit-id="<?php echo esc_attr($principal_outfit_id); ?>"
                    data-principal-outfit-image="<?php echo esc_url($display_image); ?>"
                >

                    <p class="benditoai-historial-name">
                        <?php echo esc_html($item->nombre_modelo); ?>
                    </p>

                    <?php if (!empty($display_image)): ?>
                        <div class="benditoai-img-wrap">
                            <img
                                src="<?php echo esc_url($display_image); ?>"
                                alt="<?php echo esc_attr($display_name); ?>"
                                class="benditoai-historial-img"
                            />

                            <button
                                type="button"
                                class="benditoai-save-outfit-btn"
                                data-modelo-id="<?php echo esc_attr($item->id); ?>"
                                <?php disabled(!$can_save_outfit); ?>
                                aria-disabled="<?php echo $can_save_outfit ? 'false' : 'true'; ?>"
                            >
                                <i class="far fa-bookmark" aria-hidden="true"></i>
                                <span>Guardar outfit</span>
                            </button>

                            <button
                                type="button"
                                class="benditoai-outfits-toggle"
                                data-outfits-toggle
                                aria-expanded="false"
                            >
                                <i class="fas fa-shirt" aria-hidden="true"></i>
                                <span>Outfits del modelo</span>
                                <strong class="benditoai-outfit-counter" data-outfit-counter>
                                    <?php echo esc_html($saved_outfits_count . ' de ' . (int) $outfit_limit); ?>
                                </strong>
                            </button>

                            <div class="benditoai-saved-outfits benditoai-saved-outfits-panel" data-saved-outfits-rail hidden>
                                <div class="benditoai-saved-outfits-panel-head">
                                    <span>Mis outfits guardados</span>
                                    <strong class="benditoai-outfit-counter" data-outfit-counter>
                                        <?php echo esc_html($saved_outfits_count . ' de ' . (int) $outfit_limit . ' outfits guardados'); ?>
                                    </strong>
                                </div>

                                <p
                                    class="benditoai-outfit-limit-warning"
                                    data-outfit-warning-message
                                    <?php echo $can_save_outfit ? 'hidden' : ''; ?>
                                >
                                    <?php echo esc_html($outfit_limit_warning); ?>
                                </p>

                                <div class="benditoai-saved-outfits-list" data-saved-outfits-list>
                                    <?php if (empty($saved_outfits)): ?>
                                        <p class="benditoai-saved-outfits-empty" data-saved-outfits-empty>
                                            Aun no tienes outfits guardados para este modelo.
                                        </p>
                                    <?php else: ?>
                                        <?php foreach ($saved_outfits as $saved_outfit): ?>
                                            <?php
                                            $saved_outfit_id = (int) ($saved_outfit->id ?? 0);
                                            $saved_outfit_name = (string) ($saved_outfit->nombre_outfit ?? 'Outfit');
                                            $saved_outfit_image = (string) ($saved_outfit->image_url ?? '');
                                            $saved_outfit_tag = (string) ($saved_outfit->outfit_tag ?? 'outfit');
                                            $saved_outfit_label = $saved_outfit_tag === 'principal' ? 'Principal' : 'Outfit';
                                            if ($saved_outfit_id <= 0 || $saved_outfit_image === '') {
                                                continue;
                                            }
                                            ?>
                                            <div
                                                class="benditoai-saved-outfit-card"
                                                data-outfit-id="<?php echo esc_attr($saved_outfit_id); ?>"
                                                data-outfit-tag="<?php echo esc_attr($saved_outfit_tag); ?>"
                                                data-modelo-id="<?php echo esc_attr($item->id); ?>"
                                                data-modelo-nombre="<?php echo esc_attr($item->nombre_modelo); ?>"
                                                data-outfit-name="<?php echo esc_attr($saved_outfit_name); ?>"
                                                data-outfit-image="<?php echo esc_url($saved_outfit_image); ?>"
                                                role="button"
                                                tabindex="0"
                                                aria-pressed="false"
                                                aria-label="<?php echo esc_attr('Usar outfit: ' . $saved_outfit_name); ?>"
                                            >
                                                <div class="benditoai-saved-outfit-thumb">
                                                    <img src="<?php echo esc_url($saved_outfit_image); ?>" alt="<?php echo esc_attr($saved_outfit_name); ?>" loading="lazy" />
                                                    <span class="benditoai-saved-outfit-use">Usar</span>
                                                </div>

                                                <div class="benditoai-saved-outfit-body">
                                                    <span class="benditoai-saved-outfit-tag"><?php echo esc_html($saved_outfit_label); ?></span>
                                                    <span class="benditoai-saved-outfit-name" data-outfit-name-label><?php echo esc_html($saved_outfit_name); ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="benditoai-inline-edit" hidden>
                                <div class="benditoai-inline-edit-surface">
                                    <label class="benditoai-inline-edit-label" for="benditoai-inline-edit-text-<?php echo esc_attr($item->id); ?>">
                                        Que deseas cambiar
                                    </label>
                                    <textarea
                                        id="benditoai-inline-edit-text-<?php echo esc_attr($item->id); ?>"
                                        class="benditoai-inline-edit-text"
                                        placeholder="Ej: cambia los tenis por unas botas de "
                                    ></textarea>

                                    <div class="benditoai-inline-edit-style" hidden>
                                        <span class="benditoai-inline-edit-style-label">Estilo</span>
                                        <span class="benditoai-inline-edit-style-value"></span>
                                        <button type="button" class="benditoai-inline-edit-style-remove" aria-label="Quitar estilo seleccionado" hidden>x</button>
                                    </div>
                                    <input type="hidden" class="benditoai-inline-edit-selected-style" value="">
                                    <input type="hidden" class="benditoai-inline-edit-selected-style-id" value="">

                                    <div class="benditoai-inline-edit-ref-block">
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
                                            <span class="benditoai-inline-edit-ref-trigger-text">Opcional: sube una foto de tu prenda para vestir al modelo con ella.</span>
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
                                    <a href="<?php echo esc_url($display_image); ?>" download class="benditoai-btn benditoai-btn--download benditoai-icon-btn" aria-label="Descargar modelo">
                                        <img
                                            src="<?php echo esc_url($icon_download); ?>"
                                            class="benditoai-btn-icon"
                                            alt=""
                                            aria-hidden="true"
                                        />
                                    </a>
                                </div>

                                <div class="hoverselect">
                                    <button class="benditoai-edit-modelo-btn benditoai-icon-btn" data-id="<?php echo esc_attr($item->id); ?>" data-image="<?php echo esc_url($display_image); ?>" aria-label="Editar modelo">
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
                                data-modelo-nombre="<?php echo esc_attr($display_name); ?>"
                                data-modelo-image="<?php echo esc_url($display_image); ?>"
                                data-outfit-id="<?php echo esc_attr($principal_outfit_id); ?>"
                                data-outfit-tag="principal"
                                data-source="principal_outfit"
                            >
                                Usar para campaÃ±a
                            </button>
                        </div>
                    <?php endif; ?>

                    <div class="benditoai-desktop-model-panel">
                        <button type="button" class="benditoai-desktop-edit-curtain" aria-label="Cerrar editor"></button>
                        <div class="benditoai-desktop-model-head">
                            <div class="benditoai-desktop-model-head-row">
                                <h3><?php echo esc_html($item->nombre_modelo); ?></h3>
                                <button
                                    type="button"
                                    class="benditoai-panel-title-edit benditoai-edit-modelo-btn"
                                    data-id="<?php echo esc_attr($item->id); ?>"
                                    data-image="<?php echo esc_url($display_image); ?>"
                                    aria-label="Editar modelo"
                                >
                                    <i class="fas fa-pen" aria-hidden="true"></i>
                                </button>
                            </div>
                            <div class="benditoai-desktop-model-badges">
                                <span class="benditoai-model-badge benditoai-model-badge--status"><i class="fas fa-lock" aria-hidden="true"></i><?php echo esc_html($estado_label); ?></span>
                                <?php if (!empty($item->estilo)): ?>
                                    <span class="benditoai-model-badge"><i class="fas fa-tag" aria-hidden="true"></i><?php echo esc_html($item->estilo); ?></span>
                                <?php endif; ?>
                                <span class="benditoai-model-badge benditoai-model-badge--outfit-state" data-active-outfit-badge>
                                    <i class="fas fa-shirt" aria-hidden="true"></i>
                                    <span data-active-outfit-label>Principal</span>
                                </span>
                                <span class="benditoai-model-badge"><i class="far fa-check-circle" aria-hidden="true"></i>Listo para campana</span>
                            </div>
                            <div class="benditoai-desktop-model-divider"></div>
                            <div class="benditoai-desktop-model-intro">
                                <h4>Viste este modelo con tu marca</h4>
                                <p>Personalizalo con tu ropa, accesorios y estilo para crear imagenes unicas que promocionen tus productos.</p>
                            </div>
                        </div>

                        <div class="benditoai-desktop-model-actions">
                            <div class="benditoai-desktop-campaign-spotlight">
                                <div class="benditoai-desktop-campaign-spotlight-main">
                                    <span class="benditoai-desktop-campaign-spotlight-icon" aria-hidden="true"><i class="fas fa-rocket"></i></span>
                                    <div class="benditoai-desktop-campaign-spotlight-copy">
                                        <h5>¿Listo para promocionar tus productos?</h5>
                                        <p>Lanza una campana con este modelo</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="benditoai-use-campaign-btn benditoai-use-campaign-btn--panel cards-skills-panel-cta"
                                    data-modelo-id="<?php echo esc_attr($item->id); ?>"
                                    data-modelo-nombre="<?php echo esc_attr($display_name); ?>"
                                    data-modelo-image="<?php echo esc_url($display_image); ?>"
                                    data-outfit-id="<?php echo esc_attr($principal_outfit_id); ?>"
                                    data-outfit-tag="principal"
                                    data-source="principal_outfit"
                                >
                                    Lanzar campana <span aria-hidden="true">&rarr;</span>
                                </button>
                            </div>

                            <h4 class="benditoai-desktop-manage-title">Gestiona tu modelo</h4>
                            <div class="benditoai-desktop-model-secondary">
                                <button class="benditoai-edit-modelo-btn benditoai-desktop-tool-card" data-id="<?php echo esc_attr($item->id); ?>" data-image="<?php echo esc_url($display_image); ?>">
                                    <span class="benditoai-desktop-tool-title"><i class="fas fa-pen" aria-hidden="true"></i><span>Editar modelo</span></span>
                                    <span class="benditoai-desktop-tool-desc">Cambia la apariencia, ropa, accesorios o detalles del modelo.</span>
                                    <span class="benditoai-desktop-tool-arrow" aria-hidden="true">&rarr;</span>
                                </button>
                                <a href="<?php echo esc_url($display_image); ?>" download class="benditoai-desktop-tool-card">
                                    <span class="benditoai-desktop-tool-title"><i class="fas fa-download" aria-hidden="true"></i><span>Descargar modelo</span></span>
                                    <span class="benditoai-desktop-tool-desc">Descarga las imagenes del modelo para usar en tus proyectos.</span>
                                    <span class="benditoai-desktop-tool-arrow" aria-hidden="true">&rarr;</span>
                                </a>
                            </div>

                            <?php if (!empty($outfit_catalog) && is_array($outfit_catalog)): ?>
                                <div class="benditoai-desktop-style-pills" data-style-catalog-rail>
                                    <div class="benditoai-desktop-style-pills-head">
                                        <span>Estilos para editar tu modelo</span>
                                        <div class="benditoai-style-rail-nav" aria-label="Navegar estilos">
                                            <button type="button" class="benditoai-style-rail-btn is-prev" data-style-nav="prev" aria-label="Ver estilos anteriores">
                                                <span aria-hidden="true">&lsaquo;</span>
                                            </button>
                                            <button type="button" class="benditoai-style-rail-btn is-next" data-style-nav="next" aria-label="Ver mas estilos">
                                                <span aria-hidden="true">&rsaquo;</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="benditoai-desktop-style-pills-list">
                                        <?php foreach ($outfit_catalog as $outfit): ?>
                                            <?php
                                            $outfit_id = isset($outfit['id']) ? (string) $outfit['id'] : '';
                                            $outfit_name = isset($outfit['name']) ? (string) $outfit['name'] : 'Outfit';
                                            $outfit_hint = isset($outfit['prompt_hint']) ? (string) $outfit['prompt_hint'] : '';
                                            $outfit_thumb = isset($outfit['thumb_url']) ? (string) $outfit['thumb_url'] : '';
                                            $outfit_ref = isset($outfit['reference_url']) ? (string) $outfit['reference_url'] : $outfit_thumb;
                                            if ($outfit_id === '') {
                                                continue;
                                            }
                                            ?>
                                            <button
                                                type="button"
                                                class="benditoai-style-option"
                                                data-style-id="<?php echo esc_attr($outfit_id); ?>"
                                                data-style-label="<?php echo esc_attr($outfit_name); ?>"
                                                data-style-prompt="<?php echo esc_attr($outfit_hint); ?>"
                                                data-style-reference="<?php echo esc_url($outfit_ref); ?>"
                                                aria-label="<?php echo esc_attr('Usar estilo: ' . $outfit_name); ?>"
                                                aria-pressed="false"
                                            >
                                                <img src="<?php echo esc_url($outfit_thumb); ?>" alt="<?php echo esc_attr($outfit_name); ?>" loading="lazy" />
                                                <span><?php echo esc_html($outfit_name); ?></span>
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
                                <p><span>Cejas</span><strong><?php echo esc_html(!empty($item->color_cejas) ? $item->color_cejas : 'Variable'); ?></strong></p>
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
                        <button type="button" class="benditoai-edit-add-btn">Agregar</button>
                        <button type="button" class="benditoai-edit-replace-btn">Reemplazar</button>
                    </div>

                    <button class="benditoai-toggle-info">Ver detalles</button>

                    <div class="benditoai-historial-info" style="display:none;">
                        <p><strong>Flujo:</strong> <?php echo esc_html($modo_label); ?></p>
                        <p><strong>Genero:</strong> <?php echo esc_html($item->genero); ?></p>
                        <p><strong>Edad:</strong> <?php echo esc_html($item->edad); ?></p>
                        <p><strong>Estilo:</strong> <?php echo esc_html($item->estilo); ?></p>
                        <p><strong>Visibilidad:</strong> <?php echo esc_html($visibilidad_label); ?></p>
                        <p><strong>Ojos:</strong> <?php echo esc_html(!empty($item->color_ojos) ? $item->color_ojos : '-'); ?></p>
                        <p><strong>Pelo:</strong> <?php echo esc_html(!empty($item->color_pelo) ? $item->color_pelo : '-'); ?></p>
                        <p><strong>Cejas:</strong> <?php echo esc_html(!empty($item->color_cejas) ? $item->color_cejas : '-'); ?></p>

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
        <section class="benditoai-prompt-debugger is-collapsed" data-prompt-debugger aria-label="Depurador de prompts">
            <button type="button" class="benditoai-prompt-debugger-toggle" data-prompt-debugger-toggle aria-expanded="false">
                <i class="fas fa-terminal" aria-hidden="true"></i>
                <span>Prompts</span>
            </button>
            <div class="benditoai-prompt-debugger-panel" data-prompt-debugger-panel>
                <div class="benditoai-prompt-debugger-head">
                    <div>
                        <span class="benditoai-prompt-debugger-kicker">Debug en vivo</span>
                        <strong>Prompt armado</strong>
                    </div>
                    <button type="button" class="benditoai-prompt-debugger-minimize" data-prompt-debugger-minimize aria-label="Minimizar depurador">
                        <i class="fas fa-minus" aria-hidden="true"></i>
                    </button>
                </div>
                <div class="benditoai-prompt-debugger-meta">
                    <span data-prompt-debug-model>Modelo: sin editor</span>
                    <span data-prompt-debug-outfit>Outfit: principal</span>
                    <span data-prompt-debug-mode>Modo: esperando</span>
                </div>
                <div class="benditoai-prompt-debugger-output-rich" data-prompt-debug-output>Abre el editor, escribe, selecciona un estilo o sube una prenda para ver aqui el prompt en tiempo real.</div>
            </div>
        </section>
    </div>

    <?php

    return ob_get_clean();
}
add_shortcode('benditoai_modelos_ai_historial', 'benditoai_modelos_ai_historial_shortcode');
