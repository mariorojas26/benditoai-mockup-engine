# Guia Visual Oficial BenditoAI

Esta guia es la fuente unica de verdad para construir UI en este plugin.
Si una propuesta visual no cumple esta guia, se considera fuera de look & feel.

## Regla Operativa (Obligatoria)

Antes de crear o refactorizar cualquier componente:

1. Leer esta guia.
2. Reusar tokens/criterios aqui definidos.
3. Validar el checklist final.
4. Si se necesita romper una regla, documentar por que.

## Direccion Visual

- Estilo oscuro, premium, tecnologico y enfocado en IA.
- Base casi negra, superficies morado oscuro.
- Acento principal violeta brillante.
- Contraste alto para titulos y acciones.
- Bordes finos violeta translucido.
- Componentes compactos, escaneables, sin espacios muertos.
- No definir `font-family` en modulos: heredar tipografia global.

## Paleta Base (Design Tokens)

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

## Fondos y Superficies

```css
.bai-page-section {
    background: var(--bai-bg);
    color: var(--bai-text);
}

.bai-surface {
    background: var(--bai-surface);
    border: 1px solid rgba(124, 58, 255, 0.24);
}

.bai-panel {
    background: linear-gradient(180deg, var(--bai-surface-strong), var(--bai-surface-bottom));
    border: 1px solid rgba(124, 58, 255, 0.3);
}
```

Reglas:

- No usar fondos blancos en herramientas principales del plugin.
- Evitar orbes, bokeh o decoracion grande salvo solicitud explicita.
- Usar `#0a0518` para areas de media/preview.

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

Uso:

- Botones: `12px`.
- Cards: `14px`.
- Paneles: `16px`.
- Badges/chips: `999px`.
- Sombras solo para separacion de capas.

## Tipografia

```css
.bai-title {
    color: var(--bai-text);
    line-height: 1.2;
    font-weight: 800;
}

.bai-description {
    color: var(--bai-text-soft);
    line-height: 1.32;
    font-weight: 400;
}

.bai-muted {
    color: var(--bai-text-muted);
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

- Evitar pesos excesivos en bloques largos.
- Titulos fuertes, descripcion suave.
- No abusar de uppercase sostenido.

## Botones

```css
.bai-btn-primary {
    min-height: 42px;
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid rgba(124, 58, 255, 0.68);
    background: #5e1df7;
    color: #fff;
    font-weight: 600;
}

.bai-btn-primary:hover {
    background: #7c3aff;
    border-color: rgba(167, 139, 250, 0.9);
    transform: translateY(-1px);
}

.bai-btn-ghost {
    min-height: 42px;
    padding: 10px 18px;
    border-radius: 12px;
    border: 1px solid rgba(124, 58, 255, 0.35);
    background: transparent;
    color: #c4b5fd;
    font-weight: 600;
}
```

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

- Evitar cards dentro de cards sin necesidad funcional.
- Padding recomendado: `14px-18px`.
- Gap recomendado: `8px-14px`.

## Estados, Inputs y Media

- Inputs: oscuros con focus violeta suave.
- Error/success: contraste claro, borde definido.
- Media: siempre con `overflow: hidden` y `object-fit: cover`.

## Espaciado

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

- Paneles: `16px`.
- Separacion entre controles: `8px-12px`.
- Separacion entre bloques: `14px-18px`.

## Movimiento

- Transiciones: `0.16s-0.26s`.
- Hover sin saltos bruscos ni cambios de layout.
- Respetar `prefers-reduced-motion`.

## Checklist QA UI (Obligatorio)

Antes de entregar un componente, validar:

- Hereda tipografia global.
- Usa paleta y tokens de esta guia.
- Fondo/superficie oscuros coherentes.
- Jerarquia de texto clara: titulo > descripcion > metadata.
- Radios entre `10px` y `16px`.
- Botones consistentes con estilos primario/ghost.
- Responsive probado en `320`, `768`, `1024`.
- Sin scroll horizontal.
- Hover/focus visibles y estables.
- Si hay animacion, incluye fallback de `prefers-reduced-motion`.

## Politica de Uso en el Proyecto

- Esta guia aplica a cualquier nuevo shortcode, bloque o dashboard del plugin.
- Si un modulo requiere excepcion visual, documentar en el PR/comentario tecnico: motivo + alcance.
- Si hay conflicto entre decisiones ad hoc y esta guia, prevalece esta guia.
