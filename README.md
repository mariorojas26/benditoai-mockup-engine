# BenditoAI Mockup Engine

![BendidoAI](https://bendidoai.com/wp-content/uploads/2024/01/logo.png)

**BenditoAI Mockup Engine** es un plugin de WordPress diseñado para crear experiencias de generación visual con IA en tu sitio. Está pensado para tiendas y portales que quieren ofrecer mockups inteligentes, generar campañas, editar modelos y mejorar imágenes usando servicios de IA como Gemini.

---

## 🌟 Características principales

- Generación de mockups con IA
- Administración de modelos AI y outfits
- Creación de campañas AI
- Historial de mockups generados
- Editor de imágenes: eliminar fondo y mejorar imagen
- Integración con Gemini para texto e imagen
- Sistema de tokens y planes para gestionar acceso
- Shortcodes para incluir funciones en páginas y secciones
- Carga de estilos y scripts optimizados

---

## 🚀 Módulos incluidos

- `includes/modules/auth/` - autenticación y redirección de usuarios
- `includes/modules/plans/` - configuración y actualización de planes
- `includes/modules/tokens/` - gestión de tokens y uso
- `includes/modules/mockup/` - generación de mockups y shortcodes
- `includes/modules/historial/` - historial de mockups
- `includes/modules/scroll-video/` - shortcode para videos scroll
- `includes/modules/campanas-ai/` - campañas AI
- `includes/modules/remove-bg/` - eliminar fondo de imágenes
- `includes/modules/enhance-image/` - mejorar imágenes
- `includes/modules/tendencias/` - tendencias AI
- `includes/modules/modelos-ai/` - modelos AI y outfits
- `includes/modules/Home/` - funcionalidades de Home y WhatsApp

---

## 📦 Instalación

1. Copia la carpeta `bendidoai-mockup-engine` en `wp-content/plugins/`.
2. Activa el plugin desde el panel de administración de WordPress.
3. Si es la primera vez, el plugin crea las tablas necesarias en la base de datos.
4. Configura los planes, tokens y cualquier integración en el panel de administración.

---

## 🧩 Shortcodes disponibles

Este plugin ofrece varios shortcodes listos para usar en páginas, entradas o bloques:

- `[benditoai_mockup]` - generador de mockups
- `[benditoai_historial]` - historial de mockups
- `[benditoai_remove_bg]` - eliminar fondo de imagen
- `[benditoai_enhance_image]` - mejorar imagen con IA
- `[benditoai_scroll_video]` - scroll de video
- `[benditoai_campanas_ai]` - campaña AI
- `[benditoai_trending]` - tendencias AI
- `[benditoai_modelos_ai]` - modelos AI y outfits
- `[benditoai_before_after]` - comparador antes y después
- `[benditoai_whatsapp_chat]` - asistente de WhatsApp

> Nota: el nombre exacto del shortcode puede variar según la implementación final del módulo. Revisa los archivos PHP en `includes/modules/` para confirmar los nombres precisos.

---

## ⚙️ Requisitos

- WordPress 5.0+
- PHP 7.4+ (o versión compatible según el hosting)
- Acceso a la API de Gemini para uso de IA
- Permisos para crear y actualizar tablas en la base de datos

---

## 🧠 Integración con Gemini

El plugin incluye soporte para servicios de IA en:

- `includes/services/gemini/gemini-api.php`
- `includes/services/gemini/gemini-api-text.php`
- `includes/services/gemini/gemini-api-multi-image.php`

Esto habilita generación de texto, generación de imágenes y solicitudes avanzadas para el motor de mockups.

---

## 🎨 Activos y scripts

- CSS principal: `assets/css/styles.css`
- JS principal: `assets/js/benditoai-main.js`
- Scripts adicionales: `assets/js/scroll-video.js`, `includes/modules/modelos-ai/modelos-ai-script.js`
- Librería externa: `assets/vendor/choices/choices.min.js`

---

## 🛠️ Estructura del plugin

```
/bendidoai-mockup-engine
├── assets/
│   ├── css/
│   ├── js/
│   └── vendor/
├── includes/
│   ├── admin/
│   ├── core/
│   ├── modules/
│   └── services/
└── bendidoai-mockup-engine.php
```

---

## 👨‍💻 Desarrollo

Si quieres ampliar o modificar el plugin, estos son los puntos clave:

- El archivo principal `bendidoai-mockup-engine.php` carga todos los módulos y activa los scripts.
- La función `benditoai_enqueue_assets()` inyecta estilos y scripts en el frontend.
- Las acciones de activación y desactivación se manejan en el mismo archivo principal.
- Para añadir un nuevo shortcode o módulo, crea el archivo correspondiente dentro de `includes/modules/` y agrégalo al arreglo de `benditoai_require_files()`.

---

## 📬 Contacto

- Sitio web: https://bendidoai.com
- Autor: BendidoTrazo
- Licencia: GPL2

---

## 💡 Sugerencia

Para que GitHub muestre correctamente el README, añade una imagen destacada del plugin en la parte superior y actualiza los enlaces a la documentación interna si agregas más contenido.
