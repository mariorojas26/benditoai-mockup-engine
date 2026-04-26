<?php
function benditoai_cards_skills_shortcode($atts) {

    $atts = shortcode_atts([
        'ver_mas_url' => '/herramientas',
    ], $atts);

    $url = esc_url($atts['ver_mas_url']);

   $herramientas = [
    [
        'icono'       => '<i class="fas fa-tshirt"></i>',
        'titulo'      => 'Generador de Mockups',
        'descripcion' => 'Genera mockups de moda con IA usando prompts inteligentes. Resultado en segundos.',
        'tokens'      => '10 tokens',
        'gratis'      => false,
    ],
    [
        'icono'       => '<i class="fas fa-robot"></i>',
        'titulo'      => 'Modelos AI',
        'descripcion' => 'Crea y gestiona modelos personalizados. Edítalos conversacionalmente con IA.',
        'tokens'      => '10 tokens',
        'gratis'      => false,
    ],
    [
        'icono'       => '<i class="fas fa-cut"></i>',
        'titulo'      => 'Eliminar Fondo',
        'descripcion' => 'Elimina el fondo de cualquier imagen automáticamente con inteligencia artificial.',
        'tokens'      => '5 tokens',
        'gratis'      => false,
    ],
    [
        'icono'       => '<i class="fas fa-magic"></i>',
        'titulo'      => 'Mejorar Imagen',
        'descripcion' => 'Aumenta la calidad y resolución de tus imágenes generadas con IA.',
        'tokens'      => '6 tokens',
        'gratis'      => false,
    ],
    [
        'icono'       => '<i class="fas fa-fire"></i>',
        'titulo'      => 'Tendencias',
        'descripcion' => 'Descubre los estilos y prompts más populares del momento en moda AI.',
        'tokens'      => 'Gratis',
        'gratis'      => true,
    ],
    [
        'icono'       => '<i class="fas fa-history"></i>',
        'titulo'      => 'Historial',
        'descripcion' => 'Accede a todos tus mockups y modelos generados anteriormente.',
        'tokens'      => 'Gratis',
        'gratis'      => true,
    ],
];
    ob_start();
    ?>

    <div class="cards-skills-wrapper">

        <div class="cards-skills-grid" id="cards-skills-grid">

            <?php foreach ($herramientas as $index => $h): ?>

                <div class="cards-skills-item <?php echo $index >= 4 ? 'cards-skills-item--oculta' : ''; ?>">

                    <div class="cards-skills-icono">
                        <?php echo $h['icono']; ?>
                    </div>

                    <div class="cards-skills-header">
                        <h3 class="cards-skills-titulo"><?php echo esc_html($h['titulo']); ?></h3>
                        <span class="cards-skills-badge <?php echo $h['gratis'] ? 'cards-skills-badge--gratis' : 'cards-skills-badge--tokens'; ?>">
                            <?php echo esc_html($h['tokens']); ?>
                        </span>
                    </div>

                    <p class="cards-skills-descripcion"><?php echo esc_html($h['descripcion']); ?></p>

                </div>

            <?php endforeach; ?>

        </div>

        <div class="cards-skills-footer">
            <a href="<?php echo $url; ?>" class="cards-skills-cta">
                Ver todas las herramientas
               <i class="fas fa-long-arrow-alt-right"></i>
            </a>
        </div>

    </div>

    <script>
    document.addEventListener("DOMContentLoaded", function () {

        const btn = document.getElementById("cards-skills-ver-mas");

        if (!btn) return;

        btn.addEventListener("click", function () {
            const ocultas = document.querySelectorAll(".cards-skills-item--oculta");
            ocultas.forEach(function (card) {
                card.classList.remove("cards-skills-item--oculta");
                card.classList.add("cards-skills-item--visible");
            });
            btn.style.display = "none";
        });

    });
    </script>

    <?php
    return ob_get_clean();
}
add_shortcode('benditoai_cards_skills', 'benditoai_cards_skills_shortcode');