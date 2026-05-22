<?php
if (!defined('ABSPATH')) exit;

function benditoai_campanas_ai_shortcode() {

    if (!is_user_logged_in()) {
        return '<p>Debes iniciar sesion</p>';
    }

    global $wpdb;

    $user_id = get_current_user_id();
    $modelos_table = $wpdb->prefix . 'benditoai_modelos_ai';
    $create_model_url = apply_filters('benditoai_campanas_create_model_url', home_url('/crea-modelo/'));
    $edit_model_url = apply_filters('benditoai_campanas_edit_model_url', home_url('/mis-modelos/'));

    if (function_exists('benditoai_modelo_outfit_ensure_schema')) {
        benditoai_modelo_outfit_ensure_schema();
    }

    $modelos = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
             FROM $modelos_table
             WHERE user_id = %d
             ORDER BY created_at DESC, id DESC",
            $user_id
        )
    );

    $modelo_ids = array();
    foreach ((array) $modelos as $modelo_item) {
        if (!empty($modelo_item->id)) {
            $modelo_ids[] = (int) $modelo_item->id;
        }
    }

    $outfits_by_model = array();

    if (!empty($modelo_ids) && function_exists('benditoai_modelos_ai_get_saved_outfits_grouped')) {
        $outfits_by_model = benditoai_modelos_ai_get_saved_outfits_grouped($user_id, $modelo_ids);
    }

    $category_options = array(
        'Moda',
        'Belleza',
        'Deportes',
        'Hogar',
        'Tecnologia',
        'Accesorios',
        'Alimentos',
        'Lifestyle',
    );

    $tone_options = array(
        'Elegante',
        'Juvenil',
        'Agresivo',
        'Inspirador',
        'Sofisticado',
        'Casual',
        'Profesional',
        'Divertido',
        'Urbano',
        'Emocional',
    );

    $style_options = array(
        'Minimalista' => 'Limpio, directo y con foco total en producto.',
        'Urbano' => 'Energia de calle, texturas reales y actitud moderna.',
        'Lujo' => 'Materiales premium, luz controlada y composicion aspiracional.',
        'Editorial' => 'Composicion de revista, poses y encuadres con caracter.',
        'Comercial' => 'Resultado claro para venta, catalogo y pauta.',
        'Futurista' => 'Tecnologia, reflejos y atmosfera de innovacion.',
        'Vintage' => 'Color nostalgico, textura calida y estetica retro.',
        'High Fashion' => 'Drama visual, styling marcado y presencia de marca.',
        'Lifestyle' => 'Natural, cotidiano y listo para redes.',
    );

    $palette_options = array(
        array('id' => 'aurora', 'name' => 'Aurora Neon', 'colors' => array('#7c3aff', '#22d3ee', '#f472b6'), 'hint' => 'Energia digital para lanzamientos y redes.'),
        array('id' => 'carbon', 'name' => 'Carbon Premium', 'colors' => array('#111827', '#c4b5fd', '#ffffff'), 'hint' => 'Sobrio, elegante y muy enfocado en producto.'),
        array('id' => 'fresh', 'name' => 'Fresh Pop', 'colors' => array('#10b981', '#facc15', '#ffffff'), 'hint' => 'Vibrante, juvenil y facil de recordar.'),
        array('id' => 'rose', 'name' => 'Rose Studio', 'colors' => array('#f43f5e', '#f9a8d4', '#fff7ed'), 'hint' => 'Suave, emocional y cercano.'),
        array('id' => 'mono', 'name' => 'Mono Editorial', 'colors' => array('#f8fafc', '#64748b', '#020617'), 'hint' => 'Minimalista, editorial y de alto contraste.'),
    );

    $background_options = array(
        'Estudio premium con luz suave',
        'Calle urbana con arquitectura moderna',
        'Interior minimalista de lujo',
        'Escenario deportivo dinamico',
        'Set editorial con sombras dramaticas',
        'Tienda pop-up comercial',
    );

    $format_options = array(
        array('id' => 'instagram', 'label' => 'Instagram', 'ratio' => '1:1', 'size' => '1080x1080', 'image_size' => '1K', 'icon' => 'fab fa-instagram'),
        array('id' => 'story', 'label' => 'Story', 'ratio' => '9:16', 'size' => '1080x1920', 'image_size' => '1K', 'icon' => 'fas fa-mobile-screen-button'),
        array('id' => 'tiktok', 'label' => 'TikTok', 'ratio' => '9:16', 'size' => '1080x1920', 'image_size' => '1K', 'icon' => 'fab fa-tiktok'),
        array('id' => 'banner', 'label' => 'Banner web', 'ratio' => '16:9', 'size' => '1920x1080', 'image_size' => '1K', 'icon' => 'fas fa-rectangle-ad'),
        array('id' => 'pinterest', 'label' => 'Pinterest', 'ratio' => '2:3', 'size' => '1000x1500', 'image_size' => '1K', 'icon' => 'fab fa-pinterest'),
    );

    ob_start();
?>

<div class="benditoai-campaign-wizard" data-create-model-url="<?php echo esc_url($create_model_url); ?>" data-edit-model-url="<?php echo esc_url($edit_model_url); ?>">
    <div class="baiw-shell">
        <header class="baiw-header">
            <div>
                <h2 class="baiw-title-main">Crea tu campana</h2>
                <p>Construye una pieza profesional desde producto, modelo opcional, direccion visual, copy y formatos finales.</p>
            </div>

        </header>

        <ol class="baiw-stepper bai-campaign-stepper" aria-label="Progreso del wizard">
            <li class="is-active" data-step-indicator="0"><span class="baiw-step-badge">1</span><span class="baiw-step-copy"><strong>Enfoque</strong><small>Modelo</small></span></li>
            <li data-step-indicator="1"><span class="baiw-step-badge">2</span><span class="baiw-step-copy"><strong>Producto</strong><small>Base</small></span></li>
            <li data-step-indicator="2"><span class="baiw-step-badge">3</span><span class="baiw-step-copy"><strong>Modelo</strong><small>Confirmar</small></span></li>
            <li data-step-indicator="3"><span class="baiw-step-badge">4</span><span class="baiw-step-copy"><strong>Visual</strong><small>Look</small></span></li>
            <li data-step-indicator="4"><span class="baiw-step-badge">5</span><span class="baiw-step-copy"><strong>Copy</strong><small>Marketing</small></span></li>
            <li data-step-indicator="5"><span class="baiw-step-badge">6</span><span class="baiw-step-copy"><strong>Formatos</strong><small>Exportar</small></span></li>
            <li data-step-indicator="6"><span class="baiw-step-badge">7</span><span class="baiw-step-copy"><strong>Resultado</strong><small>Final</small></span></li>
        </ol>

        <div class="baiw-progress-track" aria-hidden="true">
            <div class="baiw-progress-fill" id="benditoai-campaign-progress"></div>
        </div>

        <form id="benditoai-form-campana-ai" class="baiw-form" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="campaign_flow" id="benditoai-campaign-flow" value="">
            <input type="hidden" name="model_id" id="benditoai-campaign-model-id" value="">
            <input type="hidden" name="model_url" id="benditoai-campaign-model-url" value="">
            <input type="hidden" name="outfit_id" id="benditoai-campaign-outfit-id" value="">
            <input type="hidden" name="outfit_tag" id="benditoai-campaign-outfit-tag" value="">

            <section class="baiw-step is-active" data-step="0" aria-hidden="false">
                <div class="baiw-card">
                    <div class="baiw-card-heading">

                        <div>
                            <h3>Elige el enfoque de la campana</h3>
                            <p class="baiw-hint">Define si el producto se vendera con un modelo, con un modelo nuevo o sin persona.</p>
                        </div>
                    </div>

                    <div class="bai-campaign-focus-grid">
                        <button type="button" class="bai-campaign-focus-card" data-campaign-flow="create_model">
                            <span class="bai-campaign-focus-icon" aria-hidden="true"><i class="fas fa-user-plus"></i></span>
                            <strong>Crear modelo nuevo</strong>
                            <small>Ideal si aun no tienes un personaje listo para vender.</small>
                            <em>Ir a crea-modelo</em>
                            <span class="bai-campaign-focus-reveal">Te llevaremos a crea-modelo para preparar primero tu modelo.</span>
                        </button>

                        <button type="button" class="bai-campaign-focus-card" data-campaign-flow="use_model" data-has-models="<?php echo empty($modelos) ? '0' : '1'; ?>">
                            <span class="bai-campaign-focus-icon" aria-hidden="true"><i class="fas fa-user-check"></i></span>
                            <strong>Usar modelo ya creado</strong>
                            <small>Activa tus modelos y outfits existentes.</small>
                            <em><?php echo empty($modelos) ? 'Abrir selector' : count($modelos) . ' modelos disponibles'; ?></em>
                            <span class="bai-campaign-focus-reveal">
                                <?php echo esc_html(empty($modelos) ? 'Abriremos el selector. Si no aparece ningun modelo, puedes ir a Mis modelos o crear uno nuevo.' : 'Selecciona un modelo y uno de sus outfits guardados.'); ?>
                            </span>
                        </button>

                        <button type="button" class="bai-campaign-focus-card" data-campaign-flow="no_model">
                            <span class="bai-campaign-focus-icon" aria-hidden="true"><i class="fas fa-box-open"></i></span>
                            <strong>No usar modelo</strong>
                            <small>Campana centrada 100% en el producto.</small>
                            <em>Producto protagonista</em>
                            <span class="bai-campaign-focus-reveal">La IA enfocara el resultado en producto, set, composicion y marca, sin personas.</span>
                        </button>
                    </div>

                    <div class="bai-campaign-model-picker" id="benditoai-campaign-model-picker" hidden>
                        <div class="bai-campaign-subhead">
                            <h4>Selecciona modelo y outfit</h4>
                            <p>El outfit elegido sera la referencia visual que viajara al prompt de campana.</p>
                        </div>

                        <?php if (!empty($modelos)): ?>
                            <div class="bai-campaign-model-select-wrap">
                                <div class="baiw-field">
                                    <label for="benditoai-campaign-model-select">Modelo</label>
                                    <div class="baiw-input-shell bai-campaign-model-shell">
                                        <i class="fas fa-user" aria-hidden="true"></i>
                                        <select id="benditoai-campaign-model-select" aria-label="Selecciona un modelo">
                                            <option value="">Selecciona un modelo</option>
                                            <?php foreach ($modelos as $m): ?>
                                                <option
                                                    value="<?php echo esc_attr((int) $m->id); ?>"
                                                    data-model-name="<?php echo esc_attr($m->nombre_modelo); ?>"
                                                    data-model-url="<?php echo esc_url($m->image_url); ?>"
                                                >
                                                    <?php echo esc_html($m->nombre_modelo); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="bai-campaign-outfit-stage" id="benditoai-campaign-outfit-stage" hidden>
                                <?php foreach ($modelos as $m): ?>
                                    <?php
                                    $mid = (int) $m->id;
                                    $model_outfits = isset($outfits_by_model[$mid]) ? $outfits_by_model[$mid] : array();
                                    if (empty($model_outfits)) {
                                        $fallback = new stdClass();
                                        $fallback->id = 0;
                                        $fallback->modelo_id = $mid;
                                        $fallback->nombre_outfit = 'Principal';
                                        $fallback->outfit_tag = 'principal';
                                        $fallback->image_url = $m->image_url;
                                        $model_outfits = array($fallback);
                                    }
                                    ?>
                                    <div class="bai-campaign-outfit-set" data-model-id="<?php echo esc_attr($mid); ?>" hidden>
                                        <div class="bai-campaign-outfit-group">
                                            <span>Outfits guardados</span>
                                            <em>Selecciona uno para continuar</em>
                                        </div>
                                        <div class="bai-campaign-outfit-rail" aria-label="Outfits guardados">
                                            <?php foreach ($model_outfits as $outfit): ?>
                                                <?php
                                                $outfit_label = (string) ($outfit->outfit_tag ?? '') === 'principal' ? 'Principal' : (string) $outfit->nombre_outfit;
                                                ?>
                                                <button
                                                    type="button"
                                                    class="bai-campaign-outfit-chip bai-campaign-outfit-card"
                                                    data-model-id="<?php echo esc_attr($mid); ?>"
                                                    data-model-name="<?php echo esc_attr($m->nombre_modelo); ?>"
                                                    data-model-url="<?php echo esc_url($m->image_url); ?>"
                                                    data-outfit-id="<?php echo esc_attr((int) $outfit->id); ?>"
                                                    data-outfit-tag="<?php echo esc_attr($outfit->outfit_tag); ?>"
                                                    data-outfit-name="<?php echo esc_attr($outfit_label); ?>"
                                                    data-outfit-url="<?php echo esc_url($outfit->image_url); ?>"
                                                    aria-label="<?php echo esc_attr('Usar outfit ' . $outfit_label); ?>"
                                                >
                                                    <img src="<?php echo esc_url($outfit->image_url); ?>" alt="">
                                                    <span><?php echo esc_html($outfit_label); ?></span>
                                                </button>
                                            <?php endforeach; ?>

                                            <?php if (count($model_outfits) <= 1): ?>
                                                <div class="bai-campaign-outfit-placeholder">
                                        <i class="fas fa-tshirt" aria-hidden="true"></i>
                                                    <strong>Aqui iran tus demas outfits guardados</strong>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="bai-campaign-empty-models">
                                <span aria-hidden="true"><i class="fas fa-user-slash"></i></span>
                                <div>
                                    <h4>No encontramos modelos para esta cuenta</h4>
                                    <p>Si ya venias desde un modelo guardado intentaremos tomarlo del enlace o del navegador. Tambien puedes revisar tu historial o crear uno nuevo.</p>
                                </div>
                                <div class="bai-campaign-empty-actions">
                                    <a class="baiw-btn" href="<?php echo esc_url($edit_model_url); ?>">Ver mis modelos</a>
                                    <a class="baiw-btn baiw-btn--primary" href="<?php echo esc_url($create_model_url); ?>">Crear modelo</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="baiw-nav bai-campaign-start-nav" hidden>
                        <button type="button" class="baiw-btn baiw-btn--primary benditoai-next">Siguiente</button>
                    </div>
                </div>
            </section>

            <section class="baiw-step" data-step="1" aria-hidden="true" hidden>
                <div class="baiw-card bai-campaign-product-card">
                    <div class="bai-campaign-product-copy">
                        <div class="baiw-card-heading">

                            <div class="bai-campaign-product-heading">
                                <h3>Producto a vender</h3>
                                <p class="baiw-hint">Elige si quieres que el modelo muestre un producto en la campaña, o si la campaña sera enfocada en el modelo.</p>
                            </div>
                        </div>

                        <input type="hidden" name="product_mode" id="benditoai-product-mode" value="">

                        <div class="bai-campaign-product-mode" aria-label="Tipo de producto de campana">
                            <button type="button" class="bai-campaign-product-mode-card" data-product-mode="upload_product">
                                <span aria-hidden="true"><i class="fas fa-upload"></i></span>
                                <strong>Cargar producto al modelo</strong>
                                <small>Sube referencias del producto para que el modelo lo exhiba en la campana.</small>
                            </button>
                            <button type="button" class="bai-campaign-product-mode-card" data-product-mode="model_product">
                                <span aria-hidden="true"><i class="fas fa-tshirt"></i></span>
                                <strong>Mi modelo ya es el producto</strong>
                                <small>Usa el outfit seleccionado como pieza principal de la campana.</small>
                            </button>
                        </div>

                        <div class="bai-campaign-model-product-note" id="benditoai-model-product-note" hidden>
                            <i class="fas fa-check-circle" aria-hidden="true"></i>
                            <p>Usaremos el modelo y outfit seleccionados como referencia principal. No necesitas subir fotos adicionales del producto.</p>
                        </div>

                        <div class="bai-campaign-product-upload-fields" id="benditoai-product-upload-fields" hidden>
                            <div class="baiw-field">
                                <label for="benditoai-campaign-product">Nombre del producto</label>
                                <div class="baiw-input-shell">
                                    <i class="fas fa-tag" aria-hidden="true"></i>
                                    <input id="benditoai-campaign-product" name="producto" type="text" maxlength="70" placeholder="Ej: chaqueta denim oversized">
                                </div>
                            </div>

                            <div class="baiw-field">
                                <label for="benditoai-campaign-category">Categoria</label>
                                <select id="benditoai-campaign-category" name="categoria">
                                    <option value="">Selecciona categoria</option>
                                    <?php foreach ($category_options as $category): ?>
                                        <option value="<?php echo esc_attr($category); ?>"><?php echo esc_html($category); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <label class="bai-campaign-dropzone" id="benditoai-product-dropzone" for="benditoai-product-images">
                                <input type="file" id="benditoai-product-images" accept="image/*" multiple hidden>
                                <span aria-hidden="true"><i class="fas fa-images"></i></span>
                                <strong>Arrastra tus imagenes aqui</strong>
                                <small>o haz clic para seleccionar maximo 3 archivos</small>
                            </label>

                            <div class="bai-campaign-thumbs" id="benditoai-product-thumbs" aria-live="polite"></div>
                        </div>
                    </div>
                </div>

                <div class="baiw-nav">
                    <button type="button" class="baiw-btn benditoai-prev">Atras</button>
                    <button type="button" class="baiw-btn baiw-btn--primary benditoai-next">Siguiente</button>
                </div>
            </section>

            <section class="baiw-step" data-step="2" aria-hidden="true" hidden>
                <div class="baiw-card bai-campaign-confirm-model">
                    <div class="bai-campaign-confirm-heading">
                        <h3>Confirmacion de modelo</h3>
                        <p>Revisa la informacion del modelo y el producto para tu campana.</p>
                    </div>

                    <div class="bai-campaign-confirm-layout">
                        <div class="bai-campaign-confirm-hero">
                            <div class="bai-campaign-confirm-media" id="benditoai-selected-model-preview">
                                <i class="fas fa-user" aria-hidden="true"></i>
                            </div>
                        </div>

                        <aside class="bai-campaign-confirm-side">
                            <div class="bai-campaign-confirm-details">
                                <h4>Detalles seleccionados</h4>
                                <dl>
                                    <div>
                                        <dt><i class="fas fa-user" aria-hidden="true"></i><span>Modelo</span></dt>
                                        <dd id="benditoai-selected-model-name">Sin seleccionar</dd>
                                    </div>
                                    <div>
                                        <dt><i class="fas fa-tshirt" aria-hidden="true"></i><span>Outfit</span></dt>
                                        <dd id="benditoai-selected-outfit-name">Selecciona modelo y outfit.</dd>
                                    </div>
                                    <div>
                                        <dt><i class="fas fa-shopping-bag" aria-hidden="true"></i><span>Producto</span></dt>
                                        <dd id="benditoai-confirm-product-name">Sin producto</dd>
                                    </div>
                                    <div>
                                        <dt><i class="fas fa-tag" aria-hidden="true"></i><span>Categoria</span></dt>
                                        <dd id="benditoai-confirm-category-name">Sin categoria</dd>
                                    </div>
                                </dl>
                                <div class="bai-campaign-confirm-footer">
                                    <p><i class="fas fa-check-circle" aria-hidden="true"></i> Esta informacion se usara como base en tu campana.</p>
                                    <a class="bai-campaign-edit-model-link" id="benditoai-edit-model-link" href="<?php echo esc_url($edit_model_url); ?>" data-exit-warning="1">
                                        <i class="fas fa-pen" aria-hidden="true"></i>
                                        Editar modelo
                                    </a>
                                </div>
                            </div>
                        </aside>
                    </div>
                </div>

                <div class="baiw-nav">
                    <button type="button" class="baiw-btn benditoai-prev">Atras</button>
                    <button type="button" class="baiw-btn baiw-btn--primary benditoai-next">Siguiente</button>
                </div>
            </section>

            <section class="baiw-step" data-step="3" aria-hidden="true" hidden>
                <div class="bai-campaign-visual-layout">
                    <div class="baiw-card bai-campaign-visual-controls">
                        <div class="baiw-card-heading">
                            <span class="baiw-card-heading__icon" aria-hidden="true"><i class="fas fa-sliders"></i></span>
                            <div>
                                <h3>Configuracion visual</h3>
                                <p class="baiw-hint">Ajusta paleta, tono, estilo y ambientacion antes de generar.</p>
                            </div>
                        </div>

                        <div class="baiw-field">
                            <label>Paleta de colores</label>
                            <div class="bai-campaign-palette-grid">
                                <?php foreach ($palette_options as $palette): ?>
                                    <button type="button" class="bai-campaign-palette" data-palette-id="<?php echo esc_attr($palette['id']); ?>" data-palette-name="<?php echo esc_attr($palette['name']); ?>" data-palette-colors="<?php echo esc_attr(implode(',', $palette['colors'])); ?>" data-palette-hint="<?php echo esc_attr($palette['hint']); ?>">
                                        <span>
                                            <?php foreach ($palette['colors'] as $color): ?>
                                                <i style="background: <?php echo esc_attr($color); ?>"></i>
                                            <?php endforeach; ?>
                                        </span>
                                        <strong><?php echo esc_html($palette['name']); ?></strong>
                                        <small><?php echo esc_html($palette['hint']); ?></small>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="paleta" id="benditoai-campaign-palette" value="">
                            <input type="hidden" name="paleta_colores" id="benditoai-campaign-palette-colors" value="">
                        </div>

                        <div class="baiw-field">
                            <label for="benditoai-custom-colors">Colores personalizados</label>
                            <input id="benditoai-custom-colors" name="colores_custom" type="text" placeholder="Ej: negro, lavanda, plata">
                        </div>

                        <div class="bai-campaign-control-grid">
                            <div class="baiw-field">
                                <label for="benditoai-campaign-tone">Tono de campana</label>
                                <select id="benditoai-campaign-tone" name="tono" required>
                                    <?php foreach ($tone_options as $tone): ?>
                                        <option value="<?php echo esc_attr($tone); ?>"><?php echo esc_html($tone); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="bai-campaign-style-hint">Define la energia emocional y comercial de la pieza.</small>
                            </div>

                            <div class="baiw-field">
                                <label for="benditoai-campaign-style">Estilo de imagen</label>
                                <select id="benditoai-campaign-style" name="estilo" required>
                                    <?php foreach ($style_options as $style => $hint): ?>
                                        <option value="<?php echo esc_attr($style); ?>" data-hint="<?php echo esc_attr($hint); ?>"><?php echo esc_html($style); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="bai-campaign-style-hint" id="benditoai-style-hint"><?php echo esc_html(reset($style_options)); ?></small>
                            </div>
                        </div>

                        <div class="baiw-field">
                            <label for="benditoai-campaign-background">Fondo / ambientacion</label>
                            <select id="benditoai-campaign-background" name="fondo_preset">
                                <?php foreach ($background_options as $background): ?>
                                    <option value="<?php echo esc_attr($background); ?>"><?php echo esc_html($background); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="baiw-field">
                            <label for="benditoai-custom-background">Fondo personalizado</label>
                            <textarea id="benditoai-custom-background" name="fondo_custom" rows="2" placeholder="Describe el lugar, materiales, iluminacion o atmosfera deseada."></textarea>
                        </div>

                        <label class="bai-campaign-toggle">
                            <input type="checkbox" id="benditoai-vary-background" name="generar_angulos_fondo" value="1">
                            <span><i class="fas fa-camera-rotate" aria-hidden="true"></i> Generar diferentes angulos del fondo</span>
                        </label>

                        <div class="baiw-nav baiw-nav--inside">
                            <button type="button" class="baiw-btn" id="benditoai-reset-visual">Reset visual</button>
                        </div>
                    </div>
                </div>

                <div class="baiw-nav">
                    <button type="button" class="baiw-btn benditoai-prev">Atras</button>
                    <button type="button" class="baiw-btn baiw-btn--primary benditoai-next">Siguiente</button>
                </div>
            </section>

            <section class="baiw-step" data-step="4" aria-hidden="true" hidden>
                <div class="baiw-card bai-campaign-copy-step">
                    <div class="bai-campaign-copy-fields">
                        <div class="baiw-card-heading">
                            <span class="baiw-card-heading__icon" aria-hidden="true"><i class="fas fa-bullhorn"></i></span>
                            <div>
                                <h3>Texto de marketing</h3>
                                <p class="baiw-hint">Define el mensaje que acompana la pieza final.</p>
                            </div>
                        </div>

                        <div class="baiw-field">
                            <label for="benditoai-campaign-slogan">Frase principal / eslogan</label>
                            <input id="benditoai-campaign-slogan" name="slogan" type="text" maxlength="90" placeholder="Ej: Tu estilo empieza antes de salir">
                            <small>Tip: 5 a 9 palabras suele funcionar mejor en redes.</small>
                        </div>

                        <div class="baiw-field">
                            <label for="benditoai-campaign-cta">CTA</label>
                            <input id="benditoai-campaign-cta" name="cta" type="text" maxlength="40" placeholder="Compra ahora">
                            <small>Usa verbos claros: compra, descubre, reserva, empieza.</small>
                        </div>

                        <div class="baiw-field">
                            <label for="benditoai-copy-direction">Direccion creativa del texto</label>
                            <textarea id="benditoai-copy-direction" name="copy_direction" rows="3" placeholder="Ej: ubicar el eslogan arriba a la izquierda, CTA pequeno abajo, tipografia limpia y mucho espacio negativo."></textarea>
                            <small>Esto va directo al prompt para decidir ubicacion, jerarquia y estilo del texto.</small>
                        </div>
                    </div>

                    <aside class="bai-campaign-copy-intent">
                        <span>Como lo usara la IA</span>
                        <p>El prompt integrara estas palabras dentro de la composicion final, ajustando posicion, contraste, espacio negativo y jerarquia segun formato.</p>
                        <ul>
                            <li>Frase principal como mensaje visual dominante.</li>
                            <li>CTA claro, pequeno y con buen contraste.</li>
                            <li>Sin texto aleatorio ni letras deformes.</li>
                        </ul>
                    </aside>
                </div>

                <div class="baiw-nav">
                    <button type="button" class="baiw-btn benditoai-prev">Atras</button>
                    <button type="button" class="baiw-btn baiw-btn--primary benditoai-next">Siguiente</button>
                </div>
            </section>

            <section class="baiw-step" data-step="5" aria-hidden="true" hidden>
                <div class="baiw-card bai-campaign-format-step">
                    <div class="baiw-card-heading">
                        <span class="baiw-card-heading__icon" aria-hidden="true"><i class="fas fa-crop-simple"></i></span>
                        <div>
                            <h3>Formatos y exportacion</h3>
                            <p class="baiw-hint">Selecciona una o varias piezas finales segun plataforma.</p>
                        </div>
                    </div>

                    <div class="bai-campaign-format-layout">
                        <div class="bai-campaign-format-list">
                            <?php foreach ($format_options as $format): ?>
                                <label class="bai-campaign-format-option" data-format-card="<?php echo esc_attr($format['id']); ?>" data-ratio="<?php echo esc_attr($format['ratio']); ?>" data-size="<?php echo esc_attr($format['size']); ?>" data-image-size="<?php echo esc_attr($format['image_size']); ?>">
                                    <input type="checkbox" name="formatos[]" value="<?php echo esc_attr($format['id']); ?>" <?php checked($format['id'], 'instagram'); ?>>
                                    <span class="bai-campaign-format-icon" aria-hidden="true"><i class="<?php echo esc_attr($format['icon']); ?>"></i></span>
                                    <span>
                                        <strong><?php echo esc_html($format['label']); ?></strong>
                                        <small><?php echo esc_html($format['ratio'] . ' - ' . $format['size']); ?></small>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <aside class="bai-campaign-format-preview">
                            <span id="benditoai-format-preview-name">Instagram</span>
                            <div class="bai-campaign-ratio-box" id="benditoai-format-ratio-box" style="--campaign-ratio: 1 / 1;">
                                <div>
                                    <strong id="benditoai-format-preview-title">Tu campana</strong>
                                    <small id="benditoai-format-preview-size">1080x1080</small>
                                </div>
                            </div>
                            <label class="bai-campaign-toggle bai-campaign-export-toggle">
                                <input type="checkbox" name="export_png" value="1" checked>
                                <span><i class="fas fa-file-image" aria-hidden="true"></i> Exportar en PNG/JPG</span>
                            </label>
                        </aside>
                    </div>

                    <div class="bai-campaign-progress-box" id="benditoai-generation-status" hidden>
                        <span class="bai-campaign-spinner" aria-hidden="true"></span>
                        <div>
                            <strong>Generando campana...</strong>
                            <small id="benditoai-generation-detail">Preparando prompt visual.</small>
                        </div>
                    </div>
                </div>

                <div class="baiw-nav">
                    <button type="button" class="baiw-btn benditoai-prev">Volver</button>
                    <button type="submit" class="baiw-btn baiw-btn--primary" id="benditoai-generate-campaign">Generar campana</button>
                </div>
            </section>

            <section class="baiw-step" data-step="6" aria-hidden="true" hidden>
                <div class="baiw-card bai-campaign-results">
                    <div class="baiw-card-heading">
                        <span class="baiw-card-heading__icon" aria-hidden="true"><i class="fas fa-sparkles"></i></span>
                        <div>
                            <h3>Resultado final</h3>
                            <p class="baiw-hint">Revisa, descarga o regenera piezas si quieres otro resultado.</p>
                        </div>
                    </div>

                    <div class="bai-campaign-result-main" id="benditoai-result-main">
                        <div class="bai-campaign-result-empty">
                            <i class="fas fa-image" aria-hidden="true"></i>
                            <p>Tus imagenes generadas apareceran aqui.</p>
                        </div>
                    </div>

                    <div class="bai-campaign-result-grid" id="benditoai-result-grid"></div>

                    <p class="baiw-error-inline" id="benditoai-error" hidden>No se pudo generar la campana.</p>

                    <div class="bai-campaign-result-actions">
                        <button type="button" class="baiw-btn baiw-btn--primary" id="benditoai-download-all" disabled>
                            <i class="fas fa-download" aria-hidden="true"></i>
                            Descargar todas
                        </button>
                        <button type="button" class="baiw-btn" id="benditoai-share-campaign" disabled>
                            <i class="fas fa-share-nodes" aria-hidden="true"></i>
                            Compartir
                        </button>
                        <button type="button" class="baiw-btn" id="benditoai-recrear">
                            <i class="fas fa-rotate" aria-hidden="true"></i>
                            Re-generar
                        </button>
                        <button type="button" class="baiw-btn" id="benditoai-reset">
                            <i class="fas fa-sliders" aria-hidden="true"></i>
                            Configurar otra
                        </button>
                    </div>
                </div>
            </section>

            <div class="baiw-error-inline" id="benditoai-campaign-inline-error" role="alert" aria-live="polite" hidden></div>
        </form>
    </div>
</div>

<style>
.benditoai-campaign-wizard {
    --baiw-bg: #05020d;
    --baiw-bg-deep: #020006;
    --baiw-surface: rgba(18, 8, 38, 0.78);
    --baiw-surface-soft: rgba(255, 255, 255, 0.055);
    --baiw-surface-strong: rgba(22, 11, 49, 0.95);
    --baiw-surface-bottom: rgba(10, 5, 23, 0.98);
    --baiw-primary: #7c3aff;
    --baiw-primary-strong: #5e1df7;
    --baiw-accent: #c4b5fd;
    --baiw-ink: #ffffff;
    --baiw-muted: rgba(236, 232, 255, 0.72);
    --baiw-border: rgba(124, 58, 255, 0.24);
    --baiw-border-active: rgba(124, 58, 255, 0.65);
    --baiw-success: #86efac;
    --baiw-danger: #ffd2dc;
    max-width: 1120px;
    margin: 42px auto;
    color: var(--baiw-ink);
}

.benditoai-campaign-wizard * {
    box-sizing: border-box;
}

.benditoai-campaign-wizard button {
    margin: 0;
    font: inherit;
}

.benditoai-campaign-wizard .baiw-shell,
.benditoai-campaign-wizard .baiw-card {
    border: 1px solid rgba(124, 58, 255, 0.3);
    background: linear-gradient(180deg, var(--baiw-surface-strong), var(--baiw-surface-bottom));
    box-shadow: 0 14px 34px rgba(8, 2, 22, 0.28);
}

.benditoai-campaign-wizard .baiw-shell {
    border-radius: 16px;
    padding: clamp(16px, 2vw, 22px);
}

.benditoai-campaign-wizard .baiw-card {
    border-radius: 16px;
    padding: clamp(14px, 2vw, 18px);
}

.benditoai-campaign-wizard .baiw-header,
.benditoai-campaign-wizard .baiw-card-heading,
.benditoai-campaign-wizard .baiw-nav,
.benditoai-campaign-wizard .baiw-ai-helper,
.benditoai-campaign-wizard .baiw-ai-helper__copy {
    display: flex;
    align-items: center;
    justify-content: center;
}

.benditoai-campaign-wizard .baiw-header {
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 16px;
}

.benditoai-campaign-wizard .baiw-header h2,
.benditoai-campaign-wizard .baiw-card h3 {
    margin: 0;
    color: #ffffff;
    font-weight: 800;
    line-height: 1.18;
    letter-spacing: 0;
}

.benditoai-campaign-wizard .baiw-header h2 {
    font-size: clamp(1.55rem, 2.2vw, 2.3rem);
}

.benditoai-campaign-wizard .baiw-header p,
.benditoai-campaign-wizard .baiw-hint,
.benditoai-campaign-wizard small,
.benditoai-campaign-wizard .bai-campaign-preview-caption {
    color: var(--baiw-muted);
    line-height: 1.32;
}

.benditoai-campaign-wizard .baiw-ai-helper {
    min-width: 230px;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 14px;
    border: 1px solid var(--baiw-border);
    background: rgba(18, 8, 38, 0.78);
}

.benditoai-campaign-wizard .baiw-ai-helper__spark,
.benditoai-campaign-wizard .baiw-card-heading__icon,
.benditoai-campaign-wizard .bai-campaign-focus-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: rgba(124, 58, 255, 0.14);
    color: #c4b5fd;
}

.benditoai-campaign-wizard .baiw-ai-helper__spark,
.benditoai-campaign-wizard .baiw-card-heading__icon {
    width: 40px;
    height: 40px;
    flex: 0 0 40px;
}

.benditoai-campaign-wizard .baiw-ai-helper__copy {
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
}

.benditoai-campaign-wizard .baiw-ai-helper__badge {
    margin-left: auto;
    border-radius: 999px;
    padding: 4px 8px;
    background: #5e1df7;
    color: #ffffff;
    font-size: 0.74rem;
    font-weight: 800;
}

.benditoai-campaign-wizard .baiw-stepper {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    align-items: center;
    gap: clamp(16px, 3vw, 44px);
    max-width: 100%;
    padding: 0;
    margin: 0 0 18px;
    list-style: none;
    overflow: visible;
    transition: max-width 0.24s ease;
}

.benditoai-campaign-wizard .baiw-stepper.is-single-window {
    grid-template-columns: minmax(260px, 1fr);
    max-width: 420px;
    margin-left: auto;
    margin-right: auto;
}

.benditoai-campaign-wizard .baiw-stepper li {
    position: relative;
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) minmax(34px, 0.72fr);
    align-items: center;
    gap: 12px;
    min-height: 58px;
    padding: 0;
    border: 1px solid transparent;
    border-radius: 16px;
    background: transparent;
    color: rgba(236, 232, 255, 0.78);
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.22s ease, transform 0.22s ease, border-color 0.22s ease, background 0.22s ease, box-shadow 0.22s ease, color 0.22s ease;
}

.benditoai-campaign-wizard .baiw-stepper li::after {
    content: "";
    display: block;
    grid-column: 3;
    width: 100%;
    height: 2px;
    border-radius: 999px;
    background: rgba(124, 58, 255, 0.28);
}

.benditoai-campaign-wizard .baiw-stepper li.is-window-visible {
    opacity: 1;
    transform: translateY(0);
}

.benditoai-campaign-wizard .baiw-stepper li.is-window-last::after {
    display: none;
}

.benditoai-campaign-wizard .baiw-stepper li.is-active {
    grid-template-columns: auto minmax(0, 1fr) minmax(34px, 0.65fr);
    padding: 13px 18px;
    border-color: rgba(149, 104, 255, 0.74);
    background:
        linear-gradient(135deg, rgba(124, 58, 255, 0.78), rgba(40, 17, 92, 0.92));
    box-shadow:
        0 0 0 1px rgba(149, 104, 255, 0.2),
        0 16px 34px rgba(14, 2, 40, 0.34);
    color: #ffffff;
}

.benditoai-campaign-wizard .baiw-stepper li.is-complete {
    color: rgba(246, 242, 255, 0.9);
}

.benditoai-campaign-wizard .baiw-step-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.12);
    color: rgba(236, 232, 255, 0.86);
    font-size: 0.88rem;
    font-weight: 800;
}

.benditoai-campaign-wizard .is-complete .baiw-step-badge {
    background: rgba(124, 58, 255, 0.28);
    color: #ffffff;
}

.benditoai-campaign-wizard .is-active .baiw-step-badge {
    background: #ffffff;
    color: #5e1df7;
}

.benditoai-campaign-wizard .baiw-step-copy {
    display: grid;
    gap: 1px;
    min-width: 0;
}

.benditoai-campaign-wizard .baiw-step-copy strong,
.benditoai-campaign-wizard .baiw-step-copy small {
    line-height: 1.1;
}

.benditoai-campaign-wizard .baiw-step-copy strong {
    color: #ffffff;
    font-size: 0.88rem;
}

.benditoai-campaign-wizard .baiw-step-copy small {
    color: rgba(236, 232, 255, 0.78);
    font-size: 0.76rem;
}

.benditoai-campaign-wizard .baiw-progress-track {
    display: none;
}

.benditoai-campaign-wizard .baiw-progress-fill {
    width: 0%;
    height: 100%;
    border-radius: inherit;
    background: #5e1df7;
    transition: width 0.22s ease;
}

.benditoai-campaign-wizard .baiw-step {
    display: none;
}

.benditoai-campaign-wizard .baiw-step.is-active {
    display: block;
}

.benditoai-campaign-wizard .baiw-card-heading {
    gap: 12px;
    margin: 37px 0px;
    text-align: center;
}

.benditoai-campaign-wizard .baiw-field {
    display: grid;
    gap: 7px;
    margin-bottom: 12px;
}

.benditoai-campaign-wizard label,
.benditoai-campaign-wizard .baiw-field > label {
    color: rgba(236, 232, 255, 0.9);
    font-size: 0.84rem;
    font-weight: 600;
    letter-spacing: 0;
}

.benditoai-campaign-wizard input,
.benditoai-campaign-wizard select,
.benditoai-campaign-wizard textarea {
    width: 100%;
    border-radius: 12px;
    border: 1px solid rgba(124, 58, 255, 0.28);
    background: rgba(10, 5, 23, 0.78);
    color: #ffffff;
    padding: 12px 13px;
    outline: none;
    box-shadow: none;
}

.benditoai-campaign-wizard textarea {
    resize: vertical;
    min-height: 76px;
}

.benditoai-campaign-wizard input::placeholder,
.benditoai-campaign-wizard textarea::placeholder {
    color: rgba(220, 209, 250, 0.38);
}

.benditoai-campaign-wizard input:focus,
.benditoai-campaign-wizard select:focus,
.benditoai-campaign-wizard textarea:focus {
    border-color: rgba(124, 58, 255, 0.75);
    background: rgba(18, 8, 38, 0.92);
    box-shadow: 0 0 0 3px rgba(124, 58, 255, 0.14);
}

.benditoai-campaign-wizard .baiw-input-shell {
    position: relative;
}

.benditoai-campaign-wizard .baiw-input-shell i {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #c4b5fd;
}

.benditoai-campaign-wizard .baiw-input-shell input {
    padding-left: 40px;
}

.benditoai-campaign-wizard .baiw-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 42px;
    margin: 0;
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid rgba(124, 58, 255, 0.35);
    background: transparent;
    color: #c4b5fd;
    font-size: 0.9rem;
    font-weight: 600;
    line-height: 1;
    cursor: pointer;
    text-decoration: none;
    transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.benditoai-campaign-wizard .baiw-btn:hover {
    border-color: rgba(124, 58, 255, 0.65);
    background: rgba(124, 58, 255, 0.12);
    color: #ffffff;
}

.benditoai-campaign-wizard .baiw-btn--primary {
    border-color: rgba(124, 58, 255, 0.68);
    background: #5e1df7;
    color: #ffffff;
}

.benditoai-campaign-wizard .baiw-btn--primary:hover {
    transform: translateY(-1px);
    border-color: rgba(167, 139, 250, 0.9);
    background: #7c3aff;
}

.benditoai-campaign-wizard .baiw-btn:disabled {
    opacity: 0.58;
    cursor: not-allowed;
}

.benditoai-campaign-wizard .baiw-nav {
    justify-content: flex-end;
    gap: 10px;
    margin-top: 14px;
}

.bai-campaign-focus-grid,
.bai-campaign-model-grid,
.bai-campaign-palette-grid,
.bai-campaign-control-grid {
    display: grid;
    gap: 12px;
}

.bai-campaign-focus-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.bai-campaign-focus-card,
.bai-campaign-model-main,
.bai-campaign-palette,
.bai-campaign-format-option {
    position: relative;
    border-radius: 14px;
    border: 1px solid rgba(124, 58, 255, 0.24);
    background: rgba(18, 8, 38, 0.78);
    color: #ffffff;
    cursor: pointer;
    transition: transform 0.18s ease, border-color 0.18s ease, background 0.18s ease;
}

.bai-campaign-focus-card {
    display: grid;
    gap: 11px;
    min-height: 196px;
    padding: 20px;
    text-align: left;
}

.bai-campaign-focus-card:hover,
.bai-campaign-focus-card.is-active,
.bai-campaign-model-card.is-active .bai-campaign-model-main,
.bai-campaign-palette:hover,
.bai-campaign-palette.is-active,
.bai-campaign-format-option:hover,
.bai-campaign-format-option.is-active {
    border-color: rgba(124, 58, 255, 0.65);
    background: rgba(21, 9, 46, 0.9);
}

.bai-campaign-focus-card.is-active,
.bai-campaign-model-card.is-active .bai-campaign-model-main,
.bai-campaign-palette.is-active,
.bai-campaign-format-option.is-active {
    box-shadow: 0 0 0 1px rgba(182, 148, 255, 0.65), 0 0 22px rgba(124, 58, 255, 0.32);
}

.bai-campaign-focus-card.is-disabled {
    opacity: 0.54;
    cursor: not-allowed;
}

.bai-campaign-focus-reveal {
    display: block;
    overflow: hidden;
    max-height: 0;
    margin-top: 0;
    padding: 0;
    color: rgba(236, 232, 255, 0.74);
    font-size: 0.78rem;
    line-height: 1.35;
    font-weight: 500;
    opacity: 0;
    pointer-events: none;
    transform: translateY(-4px);
    transition: max-height 0.24s ease, margin-top 0.24s ease, opacity 0.22s ease, transform 0.22s ease;
}

.bai-campaign-focus-card:hover .bai-campaign-focus-reveal,
.bai-campaign-focus-card:focus-visible .bai-campaign-focus-reveal {
    max-height: 62px;
    margin-top: 2px;
    opacity: 1;
    transform: translateY(0);
}

.bai-campaign-focus-icon {
    width: 46px;
    height: 46px;
    font-size: 1.15rem;
}

.bai-campaign-focus-card strong,
.bai-campaign-model-copy strong,
.bai-campaign-palette strong,
.bai-campaign-format-option strong {
    font-size: 1rem;
    line-height: 1.15;
}

.bai-campaign-focus-card em {
    color: #c4b5fd;
    font-style: normal;
    font-size: 0.82rem;
    font-weight: 700;
}

.bai-campaign-model-picker {
    max-height: 0;
    margin-top: 0;
    padding: 0 20px;
    border-radius: 18px;
    border: 1px solid rgba(124, 58, 255, 0);
    background: rgba(10, 5, 23, 0);
    opacity: 0;
    overflow: hidden;
    transform: translateY(-8px) scale(0.992);
    transition: max-height 0.28s ease, margin-top 0.24s ease, padding 0.24s ease, opacity 0.22s ease, transform 0.24s ease, border-color 0.24s ease, background 0.24s ease;
}

.bai-campaign-model-picker.is-visible {
    max-height: 980px;
    margin-top: 18px;
    padding: 20px;
    border-color: rgba(124, 58, 255, 0.24);
    background: rgba(10, 5, 23, 0.48);
    opacity: 1;
    transform: translateY(0) scale(1);
}

.bai-campaign-subhead {
    display: grid;
    gap: 6px;

    justify-content: center;
    text-align: center;
    margin: 40px 0px;
}

.bai-campaign-subhead h4,
.bai-campaign-confirm-copy h4,
.bai-campaign-copy-intent h4 {
    margin: 0;
    color: #ffffff;
}

.bai-campaign-subhead p {
    max-width: 760px;
    margin: 0;
    color: var(--baiw-muted);
}

.bai-campaign-model-select-wrap {
    margin-bottom: 14px;
}

.bai-campaign-model-shell {
    min-height: 54px;
}

.bai-campaign-model-shell select {
    padding-left: 45px;
}

.bai-campaign-outfit-stage {
    display: grid;
    gap: 10px;
}

.bai-campaign-outfit-set {
    display: grid;
    gap: 10px;
}

.bai-campaign-outfit-stage[hidden],
.bai-campaign-outfit-set[hidden] {
    display: none !important;
}

.bai-campaign-empty-models {
    display: grid;
    grid-template-columns: 46px minmax(0, 1fr) auto;
    gap: 12px;
    align-items: center;
    padding: 14px;
    border-radius: 14px;
    border: 1px solid rgba(124, 58, 255, 0.24);
    background: rgba(18, 8, 38, 0.78);
}

.bai-campaign-empty-models > span {
    display: grid;
    place-items: center;
    width: 46px;
    height: 46px;
    border-radius: 12px;
    background: rgba(124, 58, 255, 0.14);
    color: #c4b5fd;
}

.bai-campaign-empty-models h4,
.bai-campaign-empty-models p {
    margin: 0;
}

.bai-campaign-empty-models p {
    margin-top: 4px;
    color: var(--baiw-muted);
}

.bai-campaign-empty-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    justify-content: flex-end;
}

.bai-campaign-model-grid {
    grid-template-columns: repeat(auto-fit, minmax(310px, 1fr));
    align-items: stretch;
}

.bai-campaign-model-card {
    display: grid;
    gap: 12px;
    min-width: 0;
    padding: 14px;
    border-radius: 18px;
    border: 1px solid rgba(124, 58, 255, 0.24);
    background: rgba(18, 8, 38, 0.72);
    transition: border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
}

.bai-campaign-model-card:hover {
    border-color: rgba(124, 58, 255, 0.5);
    background: rgba(21, 9, 46, 0.84);
}

.bai-campaign-model-card.is-active {
    border-color: rgba(124, 58, 255, 0.85);
    background: rgba(37, 16, 83, 0.82);
    box-shadow: 0 0 0 1px rgba(182, 148, 255, 0.55), 0 18px 34px rgba(8, 2, 22, 0.34);
}

.bai-campaign-model-main {
    display: grid;
    grid-template-columns: minmax(0, 1fr);
    align-items: start;
    gap: 14px;
    width: 100%;
    min-height: 0;
    padding: 0;
    border: none;
    background: transparent;
    text-align: left;
}

.bai-campaign-model-image,
.bai-campaign-confirm-media,
.bai-campaign-preview-media,
.bai-campaign-composite-stage,
.bai-campaign-result-main {
    overflow: hidden;
    border-radius: 14px;
    border: 1px solid rgba(124, 58, 255, 0.3);
    background: #0a0518;
}

.bai-campaign-model-image {
    position: relative;
    aspect-ratio: 3 / 4;
    min-height: 260px;
    border-radius: 14px;
    background: #0a0518;
}

.bai-campaign-model-image img,
.bai-campaign-outfit-chip img,
.bai-campaign-confirm-media img,
.bai-campaign-preview-media img,
.bai-campaign-result-main img,
.bai-campaign-result-card img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: cover;
}

.bai-campaign-model-copy {
    display: grid;
    align-content: start;
    gap: 8px;
    min-width: 0;
    padding: 0 2px;
}

.bai-campaign-model-copy strong {
    color: #ffffff;
    font-size: 1.05rem;
    line-height: 1.16;
    white-space: normal;
}

.bai-campaign-model-copy small {
    color: var(--baiw-muted);
    font-size: 0.84rem;
}

.bai-campaign-model-meta {
    display: inline-flex;
    width: fit-content;
    max-width: 100%;
    padding: 5px 9px;
    border-radius: 999px;
    border: 1px solid rgba(124, 58, 255, 0.24);
    background: rgba(124, 58, 255, 0.12);
    color: #c4b5fd;
    font-size: 0.74rem;
    font-weight: 800;
    line-height: 1;
    text-transform: uppercase;
}

.bai-campaign-outfit-chip span {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.bai-campaign-model-selected {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 9px;
    right: 9px;
    width: 30px;
    height: 30px;
    border-radius: 999px;
    background: #5e1df7;
    color: #ffffff;
    opacity: 0;
    transform: scale(0.88);
    transition: opacity 0.18s ease, transform 0.18s ease;
}

.bai-campaign-model-card.is-active .bai-campaign-model-selected {
    opacity: 1;
    transform: scale(1);
}

.bai-campaign-outfit-rail {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    overflow-y: hidden;
    padding: 2px 2px 6px;
    scroll-snap-type: x proximity;
    scrollbar-width: thin;
    scrollbar-color: rgba(124, 58, 255, 0.55) rgba(10, 5, 23, 0.45);
}

.bai-campaign-outfit-rail::-webkit-scrollbar {
    height: 8px;
}

.bai-campaign-outfit-rail::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: rgba(124, 58, 255, 0.55);
}

.bai-campaign-outfit-rail::-webkit-scrollbar-track {
    border-radius: 999px;
    background: rgba(10, 5, 23, 0.45);
}

.bai-campaign-outfit-group {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding-top: 10px;
    border-top: 1px solid rgba(124, 58, 255, 0.16);
}

.bai-campaign-outfit-group span {
    color: #ffffff;
    font-size: 0.82rem;
    font-weight: 800;
}

.bai-campaign-outfit-group em {
    color: var(--baiw-muted);
    font-size: 0.74rem;
    font-style: normal;
}

.bai-campaign-outfit-chip {
    display: grid;
    gap: 6px;
    flex: 0 0 122px;
    min-width: 122px;
    margin: 0;
    padding: 8px;
    border-radius: 12px;
    border: 1px solid rgba(124, 58, 255, 0.24);
    background: rgba(10, 5, 23, 0.72);
    color: rgba(236, 232, 255, 0.9);
    cursor: pointer;
    text-align: center;
    scroll-snap-align: start;
    transition: border-color 0.18s ease, background 0.18s ease, transform 0.18s ease;
}

.bai-campaign-outfit-card {
    flex-basis: 200px;
    min-width: 200px;
    align-content: start;
    padding: 9px;
    border-radius: 14px;
    text-align: left;
}

.bai-campaign-outfit-card img {
    aspect-ratio: 3 / 4;
    border-radius: 10px;
}

.bai-campaign-outfit-card span {
    margin-top: 2px;
    font-size: 0.9rem;
    font-weight: 700;
    line-height: 1.15;
    white-space: nowrap;
}

.bai-campaign-outfit-placeholder {
    display: grid;
    place-items: center;
    gap: 10px;
    flex: 0 0 200px;
    min-width: 200px;
    padding: 14px;
    border-radius: 14px;
    border: 1px dashed rgba(124, 58, 255, 0.38);
    background: rgba(10, 5, 23, 0.45);
    color: var(--baiw-muted);
    text-align: center;
}

.bai-campaign-outfit-placeholder i {
    font-size: 1.15rem;
    color: #a78bfa;
}

.bai-campaign-outfit-placeholder strong {
    font-size: 0.86rem;
    line-height: 1.25;
    color: rgba(236, 232, 255, 0.86);
}

.bai-campaign-outfit-chip img {
    width: 100%;
    aspect-ratio: 1 / 1.9;
    height: auto;
    border-radius: 10px;
    object-fit: cover;
    background: #0a0518;
}

.bai-campaign-outfit-chip span {
    color: rgba(236, 232, 255, 0.86);
    font-size: 0.72rem;
    line-height: 1.1;
}

.bai-campaign-outfit-chip:hover,
.bai-campaign-outfit-chip.is-active {
    border-color: rgba(124, 58, 255, 0.75);
    background: rgba(124, 58, 255, 0.18);
}

.bai-campaign-outfit-chip.is-active {
    box-shadow: 0 0 0 1px rgba(182, 148, 255, 0.5);
}

.bai-campaign-product-card,
.bai-campaign-copy-step,
.bai-campaign-format-layout,
.bai-campaign-confirm-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.1fr) minmax(280px, 0.9fr);
    gap: 16px;
    align-items: stretch;
}

.bai-campaign-product-card {
    grid-template-columns: minmax(0, 1fr);
}

.bai-campaign-product-copy {
    max-width: 100%;
}

.bai-campaign-product-mode {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
    margin-bottom: 14px;
}

.bai-campaign-product-mode-card {
    display: grid;
    grid-template-columns: 42px minmax(0, 1fr);
    gap: 10px;
    align-items: start;
    min-height: 108px;
    padding: 14px;
    border-radius: 14px;
    border: 1px solid rgba(124, 58, 255, 0.24);
    background: rgba(18, 8, 38, 0.7);
    color: #ffffff;
    cursor: pointer;
    text-align: left;
    transition: border-color 0.18s ease, background 0.18s ease, box-shadow 0.18s ease;
}

.bai-campaign-product-mode-card > span {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: rgba(124, 58, 255, 0.14);
    color: #c4b5fd;
}

.bai-campaign-product-mode-card strong,
.bai-campaign-product-mode-card small {
    grid-column: 2;
}

.bai-campaign-product-mode-card strong {
    font-size: 0.98rem;
    line-height: 1.2;
}

.bai-campaign-product-mode-card small {
    color: var(--baiw-muted);
    line-height: 1.35;
}

.bai-campaign-product-mode-card:hover,
.bai-campaign-product-mode-card.is-active {
    border-color: rgba(124, 58, 255, 0.68);
    background: rgba(21, 9, 46, 0.9);
}

.bai-campaign-product-mode-card.is-active {
    box-shadow: 0 0 0 1px rgba(182, 148, 255, 0.45);
}

.bai-campaign-product-mode-card[hidden],
.bai-campaign-product-upload-fields[hidden],
.bai-campaign-model-product-note[hidden] {
    display: none !important;
}

.bai-campaign-model-product-note {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    margin: 4px 0 14px;
    padding: 12px 14px;
    border-radius: 12px;
    border: 1px solid rgba(34, 197, 94, 0.28);
    background: rgba(34, 197, 94, 0.1);
    color: rgba(236, 232, 255, 0.88);
}

.bai-campaign-model-product-note i {
    color: #86efac;
    margin-top: 2px;
}

.bai-campaign-model-product-note p {
    margin: 0;
    line-height: 1.35;
}

.bai-campaign-live-preview,
.bai-campaign-copy-intent,
.bai-campaign-format-preview,
.bai-campaign-composite-preview {
    display: grid;
    gap: 10px;
}

.bai-campaign-preview-label,
.bai-campaign-copy-intent > span,
.bai-campaign-format-preview > span,
.bai-campaign-confirm-copy > span {
    color: #c4b5fd;
    font-size: 0.78rem;
    font-weight: 800;
}

.bai-campaign-preview-media {
    display: grid;
    place-items: center;
    min-height: 260px;
    padding: 16px;
    color: var(--baiw-muted);
    text-align: center;
}

.bai-campaign-preview-media i,
.bai-campaign-result-empty i {
    color: #c4b5fd;
    font-size: 2rem;
}

.bai-campaign-dropzone {
    display: grid;
    place-items: center;
    min-height: 190px;
    padding: 20px;
    border-radius: 16px;
    border: 1px dashed rgba(124, 58, 255, 0.48);
    background: rgba(10, 5, 23, 0.6);
    text-align: center;
    cursor: pointer;
}

.bai-campaign-dropzone.is-dragging,
.bai-campaign-dropzone:hover {
    border-color: rgba(124, 58, 255, 0.85);
    background: rgba(124, 58, 255, 0.12);
}

.bai-campaign-dropzone > span {
    color: #c4b5fd;
    font-size: 2rem;
}

.bai-campaign-thumbs {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin-top: 12px;
}

.bai-campaign-thumb {
    position: relative;
    min-height: 170px;
    border-radius: 14px;
    border: 1px solid rgba(124, 58, 255, 0.24);
    background: rgba(18, 8, 38, 0.78);
    overflow: hidden;
}

.bai-campaign-thumb img {
    width: 100%;
    height: 132px;
    display: block;
    object-fit: cover;
}

.bai-campaign-thumb figcaption {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 8px;
    color: rgba(236, 232, 255, 0.9);
    font-size: 0.78rem;
}

.bai-campaign-thumb-actions {
    display: flex;
    gap: 4px;
}

.bai-campaign-mini-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    margin: 0;
    border-radius: 9px;
    border: 1px solid rgba(124, 58, 255, 0.28);
    background: rgba(10, 5, 23, 0.78);
    color: #c4b5fd;
    cursor: pointer;
}

.bai-campaign-confirm-layout {
    display: grid;
    grid-template-columns: minmax(0, 0.95fr) minmax(320px, 1.05fr);
    gap: 24px;
    align-items: stretch;
    margin-top: 18px;
}

.bai-campaign-confirm-hero,
.bai-campaign-confirm-side {
    display: grid;
    gap: 16px;
}

.bai-campaign-confirm-heading h3 {
    margin: 0;
    color: #ffffff;
    font-size: clamp(1.55rem, 2vw, 2.2rem);
    line-height: 1.1;
}
.bai-campaign-confirm-heading {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    margin: 40px 0px;
    gap:8px;
}

.bai-campaign-confirm-heading p {
    margin: 6px 0 0;
    color: rgba(236, 232, 255, 0.82);
    font-size: 1rem;
    line-height: 1.35;
}

.bai-campaign-edit-model-link {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    width: fit-content;
    margin-top: 12px;
    color: #c4b5fd;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    opacity: 0.78;
    transition: opacity 0.18s ease, color 0.18s ease;
}

.bai-campaign-edit-model-link:hover {
    color: #ffffff;
    opacity: 1;
}

.bai-campaign-confirm-media {
    display: grid;
    place-items: center;
    width: 50%;
    min-width: 260px;
    min-height: 420px;
    margin: 0 auto;
    color: #c4b5fd;
    font-size: 2rem;
}

.bai-campaign-confirm-media img {
    object-fit: cover;
}

.bai-campaign-confirm-tip,
.bai-campaign-confirm-details {
    border-radius: 16px;
    border: 1px solid rgba(124, 58, 255, 0.24);
    background: rgba(10, 5, 23, 0.46);
}

.bai-campaign-confirm-tip {
    display: grid;
    grid-template-columns: 30px minmax(0, 1fr);
    gap: 9px;
    align-items: start;
    justify-self: end;
    align-self: start;
    width: fit-content;
    max-width: 380px;
    padding: 10px 12px;
    margin-left: auto;
    border-radius: 12px;
}

.bai-campaign-confirm-tip > span {
    display: grid;
    place-items: center;
    width: 28px;
    height: 28px;
    border-radius: 9px;
    background: rgba(124, 58, 255, 0.14);
    color: #d8b4fe;
    font-size: 0.82rem;
}

.bai-campaign-confirm-tip strong,
.bai-campaign-confirm-details h4 {
    color: #ffffff;
    font-weight: 800;
}

.bai-campaign-confirm-tip p {
    margin: 3px 0 0;
    color: rgba(236, 232, 255, 0.82);
    font-size: 0.78rem;
    line-height: 1.32;
}

.bai-campaign-confirm-details {
    display: grid;
    align-content: start;
    min-height: 420px;
    padding: 24px;
}

.bai-campaign-confirm-details h4 {
    margin: 0 0 22px;
    color: #d8b4fe;
    font-size: 1rem;
}

.bai-campaign-confirm-details dl {
    display: grid;
    gap: 0;
    margin: 0;
}

.bai-campaign-confirm-details dl > div {
    display: grid;
    grid-template-columns: minmax(150px, 0.75fr) minmax(0, 1fr);
    gap: 20px;
    align-items: center;
    padding: 18px 0;
    border-bottom: 1px solid rgba(124, 58, 255, 0.18);
}

.bai-campaign-confirm-details dt {
    display: inline-grid;
    grid-template-columns: 34px minmax(0, 1fr);
    gap: 12px;
    align-items: center;
    color: rgba(236, 232, 255, 0.9);
    font-weight: 700;
}

.bai-campaign-confirm-details dt i {
    color: #d8b4fe;
    font-size: 1.25rem;
}

.bai-campaign-confirm-details dd {
    min-width: 0;
    margin: 0;
    color: #ffffff;
    line-height: 1.35;
}

.bai-campaign-confirm-footer {
    display: grid;
    gap: 8px;
    margin-top: 22px;
}

.bai-campaign-confirm-footer p {
    display: inline-flex;
    gap: 8px;
    align-items: center;
    margin: 0;
    color: var(--baiw-muted);
    font-size: 0.86rem;
}

.bai-campaign-confirm-footer p i {
    color: #a78bfa;
}

.bai-campaign-visual-layout {
    display: block;
}

.bai-campaign-visual-controls {
    max-width: 920px;
    margin: 0 auto;
}

.bai-campaign-composite-stage {
    position: relative;
    min-height: 440px;
    padding: 18px;
}

.bai-campaign-composite-stage::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(124, 58, 255, 0.2), transparent 42%), radial-gradient(circle at 80% 20%, rgba(34, 211, 238, 0.16), transparent 30%);
    pointer-events: none;
}

.bai-campaign-composite-model,
.bai-campaign-composite-product {
    position: absolute;
    border-radius: 14px;
    border: 1px solid rgba(124, 58, 255, 0.35);
    background: rgba(18, 8, 38, 0.72);
    overflow: hidden;
}

.bai-campaign-composite-model {
    left: 28px;
    bottom: 28px;
    width: 47%;
    height: 78%;
}

.bai-campaign-composite-product {
    right: 28px;
    bottom: 42px;
    width: 42%;
    height: 42%;
    display: grid;
    place-items: center;
    color: #c4b5fd;
}

.bai-campaign-composite-model img,
.bai-campaign-composite-product img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.bai-campaign-palette-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.bai-campaign-palette {
    display: grid;
    gap: 7px;
    padding: 11px;
    text-align: left;
}

.bai-campaign-palette > span {
    display: flex;
    gap: 5px;
}

.bai-campaign-palette i {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.22);
}

.bai-campaign-control-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    align-items: stretch;
}

.bai-campaign-control-grid .baiw-field {
    grid-template-rows: auto minmax(46px, auto) minmax(40px, auto);
    align-content: start;
}

.bai-campaign-style-hint {
    display: block;
    min-height: 40px;
}

.bai-campaign-toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    width: auto;
    padding: 10px 12px;
    border-radius: 12px;
    border: 1px solid rgba(124, 58, 255, 0.24);
    background: rgba(255, 255, 255, 0.055);
    cursor: pointer;
}

.bai-campaign-toggle input {
    width: auto;
}

.bai-campaign-toggle span {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.bai-campaign-copy-intent {
    min-height: 260px;
    padding: 16px;
    border-radius: 14px;
    border: 1px solid rgba(124, 58, 255, 0.3);
    background: linear-gradient(160deg, rgba(94, 29, 247, 0.22), rgba(10, 5, 23, 0.95));
    align-content: center;
}

.bai-campaign-copy-intent p {
    margin: 0;
    color: rgba(236, 232, 255, 0.9);
    line-height: 1.38;
}

.bai-campaign-copy-intent ul {
    display: grid;
    gap: 8px;
    margin: 4px 0 0;
    padding-left: 18px;
    color: var(--baiw-muted);
}

.bai-campaign-format-list {
    display: grid;
    gap: 8px;
}

.bai-campaign-format-option {
    display: grid;
    grid-template-columns: 22px 38px minmax(0, 1fr);
    align-items: center;
    gap: 10px;
    padding: 10px;
}

.bai-campaign-format-option input {
    width: auto;
}

.bai-campaign-format-icon {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: rgba(124, 58, 255, 0.14);
    color: #c4b5fd;
}

.bai-campaign-ratio-box {
    display: grid;
    place-items: center;
    width: min(100%, 340px);
    aspect-ratio: var(--campaign-ratio, 1 / 1);
    max-height: 420px;
    margin: 0 auto;
    border-radius: 14px;
    border: 1px solid rgba(124, 58, 255, 0.3);
    background: linear-gradient(145deg, rgba(124, 58, 255, 0.22), rgba(10, 5, 23, 0.96));
    text-align: center;
}

.bai-campaign-ratio-box > div {
    display: grid;
    gap: 6px;
}

.bai-campaign-export-toggle {
    justify-self: center;
}

.bai-campaign-progress-box {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-top: 14px;
    padding: 12px;
    border-radius: 14px;
    border: 1px solid rgba(124, 58, 255, 0.3);
    background: rgba(124, 58, 255, 0.12);
}

.bai-campaign-spinner {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    border: 3px solid rgba(196, 181, 253, 0.25);
    border-top-color: #c4b5fd;
    animation: baiCampaignSpin 0.8s linear infinite;
}

.bai-campaign-result-main {
    display: grid;
    place-items: center;
    min-height: 480px;
    margin-bottom: 12px;
}

.bai-campaign-result-empty {
    display: grid;
    place-items: center;
    gap: 8px;
    color: var(--baiw-muted);
}

.bai-campaign-result-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
}

.bai-campaign-result-card {
    border-radius: 14px;
    border: 1px solid rgba(124, 58, 255, 0.24);
    background: rgba(18, 8, 38, 0.78);
    overflow: hidden;
    cursor: pointer;
}

.bai-campaign-result-card.is-active {
    border-color: rgba(124, 58, 255, 0.75);
}

.bai-campaign-result-card img {
    aspect-ratio: 1 / 1;
}

.bai-campaign-result-card span {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 8px;
    color: rgba(236, 232, 255, 0.9);
    font-size: 0.78rem;
}

.bai-campaign-result-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: center;
    margin-top: 14px;
}

.benditoai-campaign-wizard .baiw-error-inline {
    display: block;
    margin: 12px 0 0;
    padding: 10px 12px;
    border-radius: 10px;
    border: 1px solid rgba(251, 113, 133, 0.5);
    background: rgba(95, 24, 43, 0.58);
    color: #ffd2dc;
    font-size: 0.84rem;
}

.benditoai-campaign-wizard .baiw-error-inline[hidden] {
    display: none !important;
}

.benditoai-campaign-wizard .baiw-error-inline.is-success {
    border-color: rgba(34, 197, 94, 0.34);
    background: rgba(34, 197, 94, 0.12);
    color: #86efac;
}

@keyframes baiCampaignSpin {
    to { transform: rotate(360deg); }
}

@media (prefers-reduced-motion: reduce) {
    .benditoai-campaign-wizard * {
        transition: none !important;
        animation: none !important;
    }
}

@media (max-width: 1024px) {
    .benditoai-campaign-wizard .baiw-stepper { max-width: 100%; }

    .bai-campaign-focus-grid,
    .bai-campaign-model-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .bai-campaign-visual-layout,
    .bai-campaign-product-card,
    .bai-campaign-copy-step,
    .bai-campaign-format-layout,
    .bai-campaign-confirm-grid,
    .bai-campaign-confirm-layout {
        grid-template-columns: 1fr;
    }

    .bai-campaign-confirm-media {
        min-height: 420px;
    }
}

@media (max-width: 768px) {
    .benditoai-campaign-wizard {
        margin: 24px auto;
    }

    .benditoai-campaign-wizard .baiw-header,
    .benditoai-campaign-wizard .baiw-nav {
        align-items: stretch;
        flex-direction: column;
    }

    .benditoai-campaign-wizard .baiw-ai-helper {
        min-width: 0;
        width: 100%;
    }

    .benditoai-campaign-wizard .baiw-stepper,
    .benditoai-campaign-wizard .baiw-stepper.is-single-window {
        grid-template-columns: 1fr;
        max-width: 100%;
        gap: 10px;
    }

    .benditoai-campaign-wizard .baiw-stepper li,
    .benditoai-campaign-wizard .baiw-stepper li.is-active {
        grid-template-columns: auto minmax(0, 1fr);
        padding: 12px 14px;
        background: rgba(18, 8, 38, 0.68);
    }

    .benditoai-campaign-wizard .baiw-stepper li.is-active {
        background: linear-gradient(135deg, rgba(124, 58, 255, 0.78), rgba(40, 17, 92, 0.92));
    }

    .benditoai-campaign-wizard .baiw-stepper li::after {
        display: none;
    }

    .bai-campaign-focus-grid,
    .bai-campaign-product-mode,
    .bai-campaign-model-grid,
    .bai-campaign-empty-models,
    .bai-campaign-thumbs,
    .bai-campaign-palette-grid,
    .bai-campaign-control-grid,
    .bai-campaign-result-grid {
        grid-template-columns: 1fr;
    }

    .bai-campaign-focus-card {
        min-height: 162px;
    }

    .bai-campaign-confirm-details {
        min-height: 0;
        padding: 18px;
    }

    .bai-campaign-confirm-details dl > div {
        grid-template-columns: 1fr;
        gap: 8px;
        padding: 14px 0;
    }

    .bai-campaign-confirm-media {
        width: 100%;
        min-width: 0;
        min-height: 340px;
    }

    .bai-campaign-confirm-tip {
        grid-template-columns: 30px minmax(0, 1fr);
        max-width: none;
        width: auto;
        justify-self: stretch;
        margin-left: 0;
        padding: 10px 12px;
    }

    .bai-campaign-confirm-tip > span {
        width: 28px;
        height: 28px;
    }

    .bai-campaign-model-main {
        min-height: 0;
    }

    .bai-campaign-model-image {
        min-height: 220px;
    }

    .bai-campaign-outfit-rail {
        gap: 8px;
    }

    .bai-campaign-outfit-chip {
        flex-basis: 108px;
        min-width: 108px;
    }

    .bai-campaign-outfit-card,
    .bai-campaign-outfit-placeholder {
        flex-basis: 168px;
        min-width: 168px;
    }

    .bai-campaign-composite-stage,
    .bai-campaign-result-main {
        min-height: 360px;
    }

    .bai-campaign-result-actions .baiw-btn {
        width: 100%;
    }
}

@media (max-width: 420px) {
    .bai-campaign-model-main {
        min-height: 0;
    }

    .bai-campaign-model-image {
        min-height: 180px;
    }

    .bai-campaign-outfit-rail {
        gap: 8px;
    }

    .bai-campaign-outfit-chip {
        flex-basis: 96px;
        min-width: 96px;
    }

    .bai-campaign-outfit-card,
    .bai-campaign-outfit-placeholder {
        flex-basis: 144px;
        min-width: 144px;
    }
}
</style>

<?php
    return ob_get_clean();
}

add_shortcode('benditoai_campanas_ai', 'benditoai_campanas_ai_shortcode');
