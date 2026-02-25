# Rollpix_SkeletonPreloader

Módulo para **Magento 2** que inyecta skeletons animados (shimmer / pulse / wave / static) sobre elementos HTML configurables, reservando espacio visual y previniendo **Content Layout Shift (CLS)** mientras cargan imágenes, sliders y componentes pesados.

![Magento 2](https://img.shields.io/badge/Magento-2.4.7--2.4.8-orange)
![PHP](https://img.shields.io/badge/PHP-8.1--8.3-blue)
![License](https://img.shields.io/badge/license-proprietary-lightgrey)
![Version](https://img.shields.io/badge/version-1.0.0-green)

---

## Tabla de contenidos

- [Problema que resuelve](#problema-que-resuelve)
- [Cómo funciona](#cómo-funciona)
- [Requisitos](#requisitos)
- [Instalación](#instalación)
  - [Vía Composer (recomendado)](#vía-composer-recomendado)
  - [Manual](#manual)
- [Configuración](#configuración)
  - [General](#general)
  - [Presets](#presets)
  - [Custom Selectors](#custom-selectors)
- [Efectos disponibles](#efectos-disponibles)
- [Estructura del módulo](#estructura-del-módulo)
- [Cómo funciona internamente](#cómo-funciona-internamente)
- [FAQ / Troubleshooting](#faq--troubleshooting)
- [Compatibilidad](#compatibilidad)
- [Desinstalación](#desinstalación)
- [Changelog](#changelog)

---

## Problema que resuelve

Cuando una página de Magento carga, los elementos con imágenes pesadas (banner sliders, galerías de producto, imágenes de categoría, bloques CMS) generan un **CLS severo**: los contenedores aparecen vacíos, luego las imágenes cargan y empujan todo el contenido hacia abajo.

Esto provoca:
- Experiencia visual pobre (saltos de contenido)
- Penalización en **Core Web Vitals** (CLS > 0.1)
- Peor ranking en Google

## Cómo funciona

1. **CSS crítico inline** se inyecta en `<head>` antes que cualquier JS → reserva espacio inmediatamente
2. Un **pseudo-elemento `::before`** con animación cubre cada target como overlay
3. **JavaScript liviano** detecta cuándo el contenido real está listo (img loaded, slider inicializado)
4. El skeleton desaparece con un **fade-out** suave
5. Si pasa el timeout configurado sin detección, se remueve automáticamente (fallback de seguridad)

**Zero coupling**: no modifica archivos de terceros ni depende de módulos externos.

---

## Requisitos

| Requisito | Versión |
|-----------|---------|
| Magento   | 2.4.7 ~ 2.4.8 |
| PHP       | 8.1 ~ 8.3 |
| Theme     | Luma-based (RequireJS) |

---

## Instalación

### Vía Composer (recomendado)

**1. Agregar el repositorio** (ejecutar desde la raíz del proyecto Magento):

```bash
composer config repositories.rollpix-skeleton vcs https://github.com/ROLLPIX/M2-images-skeleton.git
```

**2. Requerir el paquete:**

```bash
composer require rollpix/module-skeleton-preloader:^1.0
```

**3. Habilitar y configurar:**

```bash
bin/magento module:enable Rollpix_SkeletonPreloader
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Manual

1. Descargar o clonar este repositorio
2. Copiar la carpeta `app/code/Rollpix/SkeletonPreloader/` dentro de la raíz de tu proyecto Magento en la misma ruta
3. Ejecutar:

```bash
bin/magento module:enable Rollpix_SkeletonPreloader
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

### Verificar instalación

```bash
bin/magento module:status Rollpix_SkeletonPreloader
```

Debe mostrar: `Module is enabled`

---

## Configuración

Ir a **Stores → Configuration → Rollpix → Skeleton Preloader**

### General

| Campo | Tipo | Default | Descripción |
|-------|------|---------|-------------|
| Enable Module | Yes/No | Yes | Habilita o deshabilita el módulo globalmente |
| Pages | Multiselect | Home, Category, Product, CMS | Tipos de página donde los skeletons estarán activos |
| Fade Out Duration | Input (ms) | 300 | Duración de la animación de fade-out al revelar contenido |
| Skeleton Timeout | Input (ms) | 8000 | Tiempo máximo que se muestra el skeleton antes de removerse como fallback de seguridad |

### Presets

El módulo incluye **6 presets predefinidos** con selectores CSS conocidos. Cada uno se activa individualmente y tiene sus propias opciones:

| Preset | Selector CSS | Height default | Uso típico |
|--------|-------------|----------------|------------|
| Mageplaza Banner Slider | `.mageplaza-bannerslider` | 450px | Banner slider de la home |
| Product Images (Listing) | `.product-image-container` | auto | Imágenes en listado de categoría |
| Product Image (PDP) | `.gallery-placeholder` | auto | Galería de producto en PDP |
| Category Image | `.category-image` | auto | Imagen de cabecera de categoría |
| CMS Block Images | `.widget img, .block-static-block img` | auto | Imágenes en bloques CMS |
| Page Builder Images | `.pagebuilder-banner-wrapper, .pagebuilder-slide-wrapper` | 400px | Elementos de Page Builder |

**Opciones por preset:**

| Campo | Descripción |
|-------|-------------|
| Enable | Activa/desactiva este preset |
| CSS Selector | Selector CSS del elemento (editable si tu theme usa otro) |
| Effect | Shimmer / Pulse / Wave / Static |
| Reserved Height | Altura en px (ej: `450px`) o `auto` para no reservar |
| Aspect Ratio | Ratio (ej: `16:9`, `3:1`). Si se define, sobreescribe height |
| Border Radius | Redondeo de esquinas en px |
| Background Color | Color base hexadecimal (ej: `#e0e0e0`) |
| Highlight Color | Color highlight hexadecimal (ej: `#f5f5f5`) |
| Animation Speed | Velocidad de la animación en ms |

### Custom Selectors

Para elementos no cubiertos por los presets, usa el campo **Custom Selectors (JSON)** con un array JSON.

**Formato:**

```json
[
  {
    "label": "Mi Slider Custom",
    "selector": ".my-slider",
    "effect": "shimmer",
    "height": "300px",
    "aspect_ratio": "",
    "border_radius": "0",
    "bg_color": "#e0e0e0",
    "hl_color": "#f5f5f5",
    "animation_speed": "1500",
    "pages": "home,category"
  },
  {
    "label": "Hero Banner",
    "selector": ".hero-banner-wrapper",
    "effect": "wave",
    "height": "500px",
    "aspect_ratio": "16:9",
    "border_radius": "8",
    "bg_color": "#eeeeee",
    "hl_color": "#fafafa",
    "animation_speed": "1200",
    "pages": "home"
  }
]
```

**Campos disponibles:**

| Campo | Requerido | Default | Descripción |
|-------|-----------|---------|-------------|
| `label` | No | — | Nombre descriptivo |
| `selector` | **Sí** | — | Selector CSS del elemento |
| `effect` | No | shimmer | shimmer / pulse / wave / static |
| `height` | No | auto | Altura en px o "auto" |
| `aspect_ratio` | No | — | Ej: "16:9". Sobreescribe height |
| `border_radius` | No | 0 | En px |
| `bg_color` | No | #e0e0e0 | Color base hex |
| `hl_color` | No | #f5f5f5 | Color highlight hex |
| `animation_speed` | No | 1500 | En ms |
| `pages` | No | — | Tipos de página separados por coma: `home,category,product,cms,all` |

---

## Efectos disponibles

### Shimmer
Gradiente lineal animado que se desplaza horizontalmente simulando un reflejo de luz. El efecto más común y reconocible.

### Pulse
Efecto de opacidad que alterna suavemente entre visible y semi-transparente. Sutil y elegante.

### Wave
Similar a shimmer pero con un gradiente más angosto, simula una onda que atraviesa el elemento. Más dinámico.

### Static
Color sólido sin animación. Solo reserva espacio y muestra un placeholder plano. Mínimo impacto en performance.

---

## Estructura del módulo

```
app/code/Rollpix/SkeletonPreloader/
├── registration.php                          # Registro del módulo
├── composer.json                             # Metadatos Composer (version: 1.0.0)
├── etc/
│   ├── module.xml                            # Declaración del módulo
│   ├── config.xml                            # Valores por defecto
│   ├── adminhtml/
│   │   └── system.xml                        # Configuración del admin
│   └── frontend/
│       └── di.xml                            # ViewModel DI
├── Model/
│   ├── Config.php                            # Lectura centralizada de configuración
│   └── Config/Source/
│       ├── Pages.php                         # Source model: Home/Category/Product/CMS/All
│       └── Effects.php                       # Source model: Shimmer/Pulse/Wave/Static
├── ViewModel/
│   └── SkeletonConfig.php                    # Genera CSS crítico + JSON config
└── view/frontend/
    ├── layout/default.xml                    # Inyecta bloques en head y body
    ├── templates/
    │   ├── skeleton-critical-css.phtml       # <style> inline en <head>
    │   └── skeleton-init.phtml               # x-magento-init con config JSON
    └── web/
        ├── css/skeleton-preloader.css        # Estilos base (no-críticos)
        └── js/skeleton-preloader.js          # Detección de carga y remoción
```

---

## Cómo funciona internamente

### CSS Crítico (inline en `<head>`)

El template `skeleton-critical-css.phtml` genera CSS dinámico minificado basado en la configuración activa. Se inyecta en `head.additional` para cargar **antes que cualquier JavaScript**:

- Reserva `min-height` o `aspect-ratio` por selector → previene CLS
- Define el overlay `::before` con la animación del efecto configurado
- Incluye los `@keyframes` necesarios

### JavaScript (RequireJS)

`skeleton-preloader.js` se inicializa vía `x-magento-init` y:

1. Recorre cada target configurado
2. Si el elemento ya está cargado (imágenes completas, slider inicializado) → skip
3. Agrega la clase `rp-skeleton-active` al elemento
4. Inicia la observación según el tipo de detección:

| Selector contiene | Estrategia | Razón |
|---|---|---|
| `slider`, `carousel`, `owl`, `banner`, `swiper`, `slick`, `pagebuilder` | MutationObserver | Inicializados vía JS |
| `img` | img.onload | Imágenes estándar |
| Cualquier otro | Híbrido (Mutation + img) | Cobertura completa |

5. Al detectar carga → `rp-skeleton-loaded` (fade-out CSS) → limpieza de clases
6. Timeout de seguridad → remoción forzada si no se detectó carga

### Filtrado por página

El ViewModel detecta el tipo de página actual leyendo el `full_action_name` del request:

| Action Name | Tipo |
|---|---|
| `cms_index_index` | home |
| `catalog_category_view` | category |
| `catalog_product_view` | product |
| `cms_page_view` | cms |

Solo se generan CSS y targets JSON para la página actual.

---

## FAQ / Troubleshooting

### El skeleton no aparece

1. Verificar que el módulo esté habilitado: `bin/magento module:status Rollpix_SkeletonPreloader`
2. Verificar que el preset o custom selector esté activado en el admin
3. Verificar que la página actual esté incluida en el campo "Pages"
4. Limpiar caché: `bin/magento cache:flush`

### El skeleton no desaparece

1. Verificar que el selector CSS sea correcto y el elemento exista en el DOM
2. Revisar la consola del navegador por errores de JS
3. El timeout de seguridad (default 8s) debería removerlo automáticamente. Si no lo hace, verificar que el JS se esté cargando

### El skeleton causa layout shift al removerse

1. Si usas `height: auto`, el skeleton no reserva espacio vertical. Usar una altura fija (ej: `450px`) para el elemento
2. Si el contenedor cambia de tamaño al cargar, configurar un `aspect-ratio` en lugar de height fijo

### Interferencia con otros módulos

El módulo es **zero-coupling**: no modifica archivos de terceros. Si hay conflictos de `z-index`, ajustar el CSS del overlay (default `z-index: 10`).

### Funciona en Hyvä?

No en esta versión. Hyvä usa Alpine.js en lugar de RequireJS. El soporte para Hyvä está planificado para una versión futura.

---

## Compatibilidad

| Componente | Soportado |
|---|---|
| Magento 2.4.7 | ✅ |
| Magento 2.4.8 | ✅ |
| PHP 8.1, 8.2, 8.3 | ✅ |
| Luma theme | ✅ |
| Themes basados en Luma | ✅ |
| Hyvä theme | ❌ (planificado) |
| Mageplaza BannerSlider | ✅ (preset incluido) |
| Page Builder | ✅ (preset incluido) |
| OWL Carousel / Slick / Swiper | ✅ (detección automática) |

---

## Desinstalación

### Con Composer

```bash
bin/magento module:disable Rollpix_SkeletonPreloader
bin/magento setup:upgrade
composer remove rollpix/module-skeleton-preloader
bin/magento cache:flush
```

### Manual

```bash
bin/magento module:disable Rollpix_SkeletonPreloader
bin/magento setup:upgrade
```

Luego eliminar la carpeta `app/code/Rollpix/SkeletonPreloader/` y ejecutar `bin/magento cache:flush`.

---

## Changelog

### 1.0.0
- Release inicial
- 4 efectos de animación: Shimmer, Pulse, Wave, Static
- 6 presets predefinidos
- Custom selectors vía JSON
- CSS crítico inline para prevención de CLS
- Detección inteligente por tipo de elemento
- Configuración completa desde el admin

---

**Desarrollado por [Rollpix](https://github.com/ROLLPIX)**
