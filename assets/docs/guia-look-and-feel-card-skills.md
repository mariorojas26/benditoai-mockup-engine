# Guia Visual General BenditoAI

Esta guia define el look & feel general para modulos BenditoAI. No esta pensada solo para tabs; sirve para formularios, cards, paneles, dashboards, wizards, resultados, historiales y herramientas IA.

## Direccion Visual

- Estilo oscuro, premium, tecnologico y enfocado en IA.
- Base casi negra, con superficies morado oscuro.
- Acento principal violeta brillante.
- Contraste alto para titulos y acciones.
- Bordes finos con violeta translucido.
- Componentes compactos, faciles de escanear y sin espacios vacios innecesarios.
- No usar `font-family` en modulos: todos deben heredar la tipografia global de la web.

## Paleta Base

```css
:root {
    --bai-bg: #05020d;
    --bai-bg-deep: #020006;
    --bai-surface: rgba(18, 8, 38, 0.78);
    --bai-surface-hover: rgba(21, 9, 46, 0.9);
    --bai-surface-strong: rgba(22, 11, 49, 0.95);
    --bai-surface-bottom: rgba(10, 5, 23, 0.98);
    --bai-media-bg: #0a0518;

    --bai-accent: #7c3aff;
    --bai-accent-solid: #5e1df7;
    --bai-accent-hover: #7c3aff;
    --bai-accent-soft: rgba(124, 58, 255, 0.14);
    --bai-accent-muted: rgba(124, 58, 255, 0.24);

    --bai-text: #ffffff;
    --bai-text-soft: rgba(236, 232, 255, 0.9);
    --bai-text-muted: rgba(236, 232, 255, 0.72);
    --bai-text-chip: rgba(236, 232, 255, 0.82);
    --bai-text-accent: #c4b5fd;

    --bai-success: #86efac;
    --bai-success-bg: rgba(34, 197, 94, 0.12);
    --bai-success-border: rgba(34, 197, 94, 0.34);
    --bai-error: #ffd2dc;
    --bai-error-bg: rgba(95, 24, 43, 0.58);
    --bai-error-border: rgba(251, 113, 133, 0.5);
}
```

## Fondos

Usar fondos oscuros y sobrios. Evitar gradientes grandes decorativos, orbes o bokeh.

```css
.bai-page-section {
    background: #05020d;
    color: #ffffff;
}

.bai-surface {
    background: rgba(18, 8, 38, 0.78);
    border: 1px solid rgba(124, 58, 255, 0.24);
}

.bai-panel {
    background: linear-gradient(180deg, rgba(22, 11, 49, 0.95), rgba(10, 5, 23, 0.98));
    border: 1px solid rgba(124, 58, 255, 0.3);
}
```

Reglas:

- Usa superficies oscuras para agrupar contenido.
- Usa el panel con gradiente solo en contenedores importantes.
- Usa `#0a0518` como fondo para areas de imagen, preview o media.

## Bordes, Radios y Sombras

```css
:root {
    --bai-border-soft: rgba(124, 58, 255, 0.24);
    --bai-border-medium: rgba(124, 58, 255, 0.3);
    --bai-border-active: rgba(124, 58, 255, 0.65);

    --bai-radius-sm: 10px;
    --bai-radius-md: 12px;
    --bai-radius-lg: 14px;
    --bai-radius-xl: 16px;
    --bai-radius-pill: 999px;

    --bai-shadow-soft: 0 14px 34px rgba(8, 2, 22, 0.28);
}
```

Uso recomendado:

- Botones: `12px`.
- Cards compactas: `14px`.
- Paneles principales: `16px`.
- Badges/chips: `999px`.
- Sombras solo si ayudan a separar capas, no como decoracion pesada.

## Tipografia

No definir familia tipografica en modulos.

```css
.bai-title {
    color: #ffffff;
    line-height: 1.2;
    font-weight: 800;
}

.bai-subtitle,
.bai-description {
    color: rgba(236, 232, 255, 0.9);
    line-height: 1.32;
    font-weight: 400;
}

.bai-muted {
    color: rgba(236, 232, 255, 0.72);
}
```

Escala sugerida:

```css
.bai-title-xl { font-size: clamp(2rem, 3vw, 3rem); }
.bai-title-lg { font-size: clamp(1.5rem, 2vw, 2.25rem); }
.bai-title-md { font-size: clamp(1.15rem, 1.2vw, 1.5rem); }
.bai-body { font-size: 1rem; }
.bai-small { font-size: 0.84rem; }
.bai-micro { font-size: 0.75rem; }
```

Reglas:

- No usar uppercase sostenido en labels comunes.
- Titulos fuertes, descripciones suaves.
- En mobile, reducir antes de apilar demasiado.

## Botones

### Boton primario

```css
.bai-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 42px;
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid rgba(124, 58, 255, 0.68);
    background: #5e1df7;
    color: #ffffff;
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    cursor: pointer;
    transition: transform 0.2s ease, background 0.2s ease, border-color 0.2s ease;
}

.bai-btn-primary:hover {
    background: #7c3aff;
    border-color: rgba(167, 139, 250, 0.9);
    transform: translateY(-1px);
}
```

### Boton secundario / ghost

```css
.bai-btn-ghost {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 42px;
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid rgba(124, 58, 255, 0.35);
    background: transparent;
    color: #c4b5fd;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
}

.bai-btn-ghost:hover {
    background: rgba(124, 58, 255, 0.12);
    border-color: rgba(124, 58, 255, 0.65);
    color: #ffffff;
}
```

Reglas:

- Usar icono de flecha solo cuando la accion navega.
- En mobile, botones importantes a ancho completo.
- No usar sombras fuertes en botones.

## Cards y Paneles

```css
.bai-card {
    border-radius: 14px;
    border: 1px solid rgba(124, 58, 255, 0.24);
    background: rgba(18, 8, 38, 0.78);
    padding: 16px;
}

.bai-card:hover,
.bai-card.is-active {
    border-color: rgba(124, 58, 255, 0.65);
    background: rgba(21, 9, 46, 0.9);
}

.bai-panel {
    border-radius: 16px;
    border: 1px solid rgba(124, 58, 255, 0.3);
    background: linear-gradient(180deg, rgba(22, 11, 49, 0.95), rgba(10, 5, 23, 0.98));
    padding: 16px;
}
```

Reglas:

- No meter cards dentro de cards salvo items repetidos o controles funcionales.
- Mantener padding compacto: `14px-18px`.
- Usar gap de `8px-14px`.

## Badges y Chips

### Badge de estado o tokens

```css
.bai-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 28px;
    padding: 5px 10px;
    border-radius: 999px;
    border: 1px solid rgba(124, 58, 255, 0.42);
    background: rgba(124, 58, 255, 0.14);
    color: #c4b5fd;
    font-size: 12px;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
}

.bai-badge-success {
    border-color: rgba(34, 197, 94, 0.34);
    background: rgba(34, 197, 94, 0.12);
    color: #86efac;
}
```

### Microbeneficios

```css
.bai-chip-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.bai-chip {
    display: inline-flex;
    align-items: center;
    min-height: 26px;
    padding: 5px 9px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.055);
    color: rgba(236, 232, 255, 0.82);
    font-size: 12px;
    line-height: 1.2;
}
```

## Inputs y Formularios

```css
.bai-field label {
    display: block;
    margin-bottom: 6px;
    color: rgba(236, 232, 255, 0.9);
    font-weight: 600;
}

.bai-input,
.bai-select,
.bai-textarea {
    width: 100%;
    border-radius: 12px;
    border: 1px solid rgba(124, 58, 255, 0.28);
    background: rgba(10, 5, 23, 0.78);
    color: #ffffff;
    padding: 12px 14px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
}

.bai-input:focus,
.bai-select:focus,
.bai-textarea:focus {
    outline: none;
    border-color: rgba(124, 58, 255, 0.75);
    background: rgba(18, 8, 38, 0.92);
    box-shadow: 0 0 0 3px rgba(124, 58, 255, 0.14);
}

.bai-input::placeholder,
.bai-textarea::placeholder {
    color: rgba(220, 209, 250, 0.38);
}
```

Reglas:

- Inputs oscuros.
- Focus violeta suave.
- Placeholder sutil, nunca blanco fuerte.

## Imagenes y Media

```css
.bai-media {
    width: 100%;
    border-radius: 13px;
    overflow: hidden;
    background: #0a0518;
}

.bai-media img,
.bai-media video {
    display: block;
    width: 100%;
    height: auto;
    object-fit: cover;
    object-position: center center;
}
```

Reglas:

- Media siempre con `overflow: hidden`.
- Usar `object-fit: cover` para previews.
- No usar imagenes oscuras/borrosas cuando el usuario debe inspeccionar detalles.

## Estados

```css
.bai-error {
    border-radius: 10px;
    border: 1px solid rgba(251, 113, 133, 0.5);
    background: rgba(95, 24, 43, 0.58);
    color: #ffd2dc;
    padding: 10px 12px;
    font-size: 0.84rem;
}

.bai-success {
    border-radius: 10px;
    border: 1px solid rgba(34, 197, 94, 0.34);
    background: rgba(34, 197, 94, 0.12);
    color: #86efac;
    padding: 10px 12px;
    font-size: 0.84rem;
}
```

## Espaciado

Escala recomendada:

```css
:root {
    --bai-space-1: 4px;
    --bai-space-2: 8px;
    --bai-space-3: 10px;
    --bai-space-4: 12px;
    --bai-space-5: 14px;
    --bai-space-6: 16px;
    --bai-space-7: 18px;
    --bai-space-8: 24px;
}
```

Reglas:

- Paneles compactos: `16px`.
- Separacion entre controles: `8px-12px`.
- Separacion entre bloques: `14px-18px`.
- Evitar padding grande en componentes de trabajo.

## Responsive

```css
@media (max-width: 768px) {
    .bai-panel {
        padding: 18px;
        border-radius: 14px;
    }

    .bai-btn-primary,
    .bai-btn-ghost {
        width: 100%;
        min-height: 40px;
        padding: 10px 14px;
        font-size: 13px;
    }

    .bai-chip {
        min-height: 23px;
        padding: 4px 8px;
        font-size: 11px;
    }
}

@media (max-width: 390px) {
    .bai-panel {
        padding: 16px;
    }
}
```

## Movimiento

```css
@keyframes bai-panel-open {
    from {
        opacity: 0;
        transform: translateY(5px) scale(0.998);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}
```

Reglas:

- Transiciones cortas: `0.16s-0.26s`.
- Evitar blur animado en mobile.
- Hover no debe mover layout ni cambiar dimensiones.

## Checklist de Consistencia

- Hereda `font-family`.
- Fondo oscuro, superficies morado oscuro.
- Acento violeta `#7c3aff` / `#5e1df7`.
- Texto principal blanco, texto secundario lavanda suave.
- Bordes violetas translucidos.
- Radios entre `10px` y `16px`.
- Badges/chips tipo pill.
- Botones primarios violetas solidos.
- Inputs oscuros con focus violeta.
- Imagenes dentro de contenedores con radio y `object-fit: cover`.
- Mobile compacto, botones full width cuando sean acciones principales.
- No usar uppercase sostenido.
- No usar orbes, bokeh o decoraciones que no aporten.
