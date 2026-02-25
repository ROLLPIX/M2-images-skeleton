# PRD: Rollpix_SkeletonPreloader

## Módulo de Skeleton/Shimmer Preloader para Magento 2

**Vendor:** Rollpix
**Module:** SkeletonPreloader
**Compatibilidad:** Magento 2.4.7 ~ 2.4.8 | PHP 8.1 ~ 8.3
**Theme target:** Luma-based (RequireJS)
**Versión:** 1.0.0

---

## 1. Problema

Cuando una página de Magento carga (especialmente la home), los elementos con imágenes como banner sliders (Mageplaza BannerSlider, OWL Carousel, etc.), imágenes de producto, imágenes de categoría y bloques CMS generan un Content Layout Shift (CLS) severo. Los contenedores aparecen vacíos, luego las imágenes cargan y empujan todo el contenido hacia abajo, provocando una experiencia visual pobre y penalizando Core Web Vitals.

## 2. Solución

Un módulo Magento 2 independiente que inyecta skeletons animados (shimmer/pulse/wave) sobre elementos configurables, reservando espacio visual antes de que carguen las imágenes o se inicialice el JavaScript de terceros (como OWL Carousel). El módulo NO modifica archivos de módulos de terceros.

## 3. Principios de diseño

- **Zero coupling:** No toca código de Mageplaza ni de ningún módulo de terceros.
- **CSS-first:** El CSS crítico se carga inline para actuar antes que cualquier JS.
- **Performance:** JS mínimo, sin dependencias externas más allá de RequireJS nativo de Magento.
- **Configurable:** Todo se maneja desde el admin sin tocar código.
- **Non-blocking:** Si el skeleton falla o el JS no carga, la página funciona normalmente.

## 4. Arquitectura de archivos

```
app/code/Rollpix/SkeletonPreloader/
├── registration.php
├── composer.json
├── etc/
│   ├── module.xml
│   ├── config.xml                          # Valores por defecto
│   ├── adminhtml/
│   │   └── system.xml                      # Configuración admin completa
│   └── frontend/
│       └── di.xml                          # ViewModel injection
├── Model/
│   └── Config.php                          # Lectura de config del admin
├── ViewModel/
│   └── SkeletonConfig.php                  # Pasa config como JSON al frontend
├── view/
│   └── frontend/
│       ├── layout/
│       │   └── default.xml                 # Inyecta template en <head> y before.body.end
│       ├── templates/
│       │   ├── skeleton-critical-css.phtml  # CSS inline crítico (en <head>)
│       │   └── skeleton-init.phtml          # Config JSON + carga del JS
│       └── web/
│           ├── css/
│           │   └── skeleton-preloader.css   # Estilos de animaciones (no-críticos)
│           └── js/
│               └── skeleton-preloader.js    # Lógica de detección y remoción
```

## 5. Configuración Admin

### Ruta: Stores > Configuration > Rollpix > Skeleton Preloader

### 5.1 General Settings

| Campo | Tipo | Default | Descripción |
|-------|------|---------|-------------|
| Enable Module | Yes/No | Yes | Habilita/deshabilita globalmente |
| Pages | Multiselect | All | Home, Category, Product, CMS, All |
| Fade Out Duration | Input (ms) | 300 | Duración del fade al revelar el contenido |
| Skeleton Timeout | Input (ms) | 8000 | Tiempo máximo que se muestra el skeleton antes de removerse como fallback |

### 5.2 Target Elements

Sección con dos partes: presets conocidos y custom selectors.

#### 5.2.1 Presets (checkboxes)

Cada preset activa un selector CSS conocido con configuración predefinida. Al activarlo, aparecen sus opciones individuales:

| Preset | Selector CSS | Default Height | Descripción |
|--------|-------------|----------------|-------------|
| Mageplaza Banner Slider | `.mageplaza-bannerslider` | 450px | Banner slider de Mageplaza |
| Product Images (listing) | `.product-image-container` | auto | Imágenes en listado de categoría |
| Product Image (PDP) | `.gallery-placeholder` | auto | Galería de producto |
| Category Image | `.category-image` | auto | Imagen de cabecera de categoría |
| CMS Block Images | `.widget img, .block-static-block img` | auto | Imágenes en bloques CMS |
| Page Builder Images | `.pagebuilder-banner-wrapper, .pagebuilder-slide-wrapper` | 400px | Elementos de Page Builder |

Para cada preset activado se muestran los campos:

| Campo | Tipo | Default | Descripción |
|-------|------|---------|-------------|
| Enable | Yes/No | No | Activa este preset |
| Effect | Select | Shimmer | Shimmer / Pulse / Wave / Static |
| Reserved Height | Input (px o "auto") | (según preset) | Altura a reservar. "auto" no reserva. |
| Aspect Ratio | Input | (vacío) | Ratio como "16:9", "3:1". Si se define, sobreescribe height. |
| Border Radius | Input (px) | 0 | Redondeo de esquinas del skeleton |
| Background Color | Color picker | #e0e0e0 | Color base del skeleton |
| Highlight Color | Color picker | #f5f5f5 | Color del brillo/highlight del efecto |
| Animation Speed | Input (ms) | 1500 | Velocidad de la animación |

#### 5.2.2 Custom Selectors

Campo tipo Dynamic Rows (repeater) donde el admin puede agregar filas con:

| Campo | Tipo | Descripción |
|-------|------|-------------|
| Label | Input text | Nombre identificador (ej: "Mi otro slider") |
| CSS Selector | Input text | Selector CSS del contenedor (ej: `.my-custom-slider`) |
| Effect | Select | Shimmer / Pulse / Wave / Static |
| Reserved Height | Input | Altura en px o "auto" |
| Aspect Ratio | Input | Ej: "16:9" o vacío |
| Border Radius | Input (px) | Default 0 |
| Background Color | Color picker | Default #e0e0e0 |
| Highlight Color | Color picker | Default #f5f5f5 |
| Animation Speed | Input (ms) | Default 1500 |
| Pages | Multiselect | Home / Category / Product / CMS / All |

## 6. Efectos disponibles

### 6.1 Shimmer
Gradiente lineal animado que se desplaza horizontalmente de izquierda a derecha simulando un reflejo de luz.

```
background: linear-gradient(90deg, {base} 25%, {highlight} 50%, {base} 75%);
background-size: 200% 100%;
animation: rp-skeleton-shimmer {speed}ms infinite;
```

### 6.2 Pulse
Efecto de opacidad que alterna entre el color base y una versión más clara.

```
animation: rp-skeleton-pulse {speed}ms ease-in-out infinite;
/* alterna opacity 1 → 0.4 → 1 */
```

### 6.3 Wave
Similar a shimmer pero con un gradiente más angosto y rápido, simula una onda.

```
background: linear-gradient(90deg, {base} 40%, {highlight} 50%, {base} 60%);
background-size: 300% 100%;
animation: rp-skeleton-wave {speed}ms ease-in-out infinite;
```

### 6.4 Static
Color sólido sin animación. Solo reserva espacio y muestra un placeholder gris.

## 7. Lógica de funcionamiento (JS)

### 7.1 Inicialización

1. El módulo inyecta CSS crítico inline en `<head>` vía `skeleton-critical-css.phtml`. Este CSS:
   - Define las alturas reservadas por selector (esto es lo que previene el CLS)
   - Agrega un pseudo-elemento `::before` o un div overlay con la animación del skeleton
   - Se aplica **antes** de que cargue cualquier JS

2. En `before.body.end`, `skeleton-init.phtml` inyecta:
   - Un objeto JSON con toda la configuración de selectores y sus opciones
   - La carga de `skeleton-preloader.js` vía RequireJS

### 7.2 Detección de carga y remoción

El JS (`skeleton-preloader.js`) ejecuta la siguiente lógica:

```
Para cada selector configurado:
  1. Buscar elementos en el DOM que matcheen
  2. Si el elemento ya está cargado (tiene imágenes loaded), skip
  3. Si no:
     a. Agregar clase CSS `rp-skeleton-active` al elemento
     b. Iniciar observación:
        - Para <img>: escuchar evento "load"
        - Para contenedores de slider (.mageplaza-bannerslider, OWL):
          usar MutationObserver esperando que se agreguen clases
          como "owl-loaded" o que aparezcan <img> dentro
        - Para lazy-load: IntersectionObserver
     c. Cuando se detecta carga:
        - Agregar clase `rp-skeleton-loaded` (trigger del fade-out CSS)
        - Después de {fadeOutDuration}ms, remover las clases del skeleton
     d. Si pasa {timeout}ms sin carga:
        - Forzar remoción del skeleton (fallback de seguridad)
```

### 7.3 Detección específica para Mageplaza BannerSlider

El slider de Mageplaza usa OWL Carousel y el bloque `Mageplaza\BannerSlider\Block\Slider` con template `bannerslider.phtml`. El contenedor principal tiene la clase `.mageplaza-bannerslider` y usa un componente RequireJS que inicializa OWL.

Detección específica:
- **MutationObserver** en `.mageplaza-bannerslider` esperando:
  - La aparición de la clase `owl-loaded` en el carrusel interno
  - O la aparición de `<img>` con `complete === true`
- Se reserva la altura configurada inmediatamente via CSS inline critical

### 7.4 Detección page type

El JS necesita saber en qué tipo de página está para filtrar selectores. Se resuelve leyendo las clases del `<body>` que Magento ya inyecta:

- Home: `body.cms-index-index` o `body.cms-home`
- Category: `body.catalog-category-view`
- Product: `body.catalog-product-view`
- CMS: `body.cms-page-view`

## 8. CSS Crítico (inline en head)

El template `skeleton-critical-css.phtml` genera CSS dinámico basado en la config:

```css
/* Generado dinámicamente por Rollpix_SkeletonPreloader */

/* Reserva de espacio - ESTO previene CLS */
.mageplaza-bannerslider {
    min-height: 450px;
    position: relative;
    overflow: hidden;
}

/* Overlay skeleton */
.mageplaza-bannerslider.rp-skeleton-active::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10;
    background: #e0e0e0;
    background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 50%, #e0e0e0 75%);
    background-size: 200% 100%;
    animation: rp-skeleton-shimmer 1500ms infinite;
    border-radius: 0px;
}

/* Fade-out al cargar */
.rp-skeleton-loaded::before {
    animation: rp-skeleton-fadeout 300ms forwards !important;
}

/* Keyframes */
@keyframes rp-skeleton-shimmer { ... }
@keyframes rp-skeleton-pulse { ... }
@keyframes rp-skeleton-wave { ... }
@keyframes rp-skeleton-fadeout {
    to { opacity: 0; visibility: hidden; }
}
```

## 9. ViewModel: SkeletonConfig.php

Responsable de:
- Leer toda la configuración del admin via `Model\Config.php`
- Determinar si el módulo está activo
- Serializar la configuración como JSON para el frontend
- Generar el CSS crítico dinámico como string

Métodos principales:
- `isEnabled(): bool`
- `getTargetElements(): array` — fusiona presets activos + custom selectors
- `getCriticalCss(): string` — genera el CSS inline
- `getJsConfig(): string` — JSON con toda la config para el JS
- `isAllowedOnPage(string $pageType): bool`

## 10. Model\Config.php

Helper que encapsula todas las lecturas de `Magento\Framework\App\Config\ScopeConfigInterface`:

- `isEnabled(): bool`
- `getAllowedPages(): array`
- `getFadeOutDuration(): int`
- `getSkeletonTimeout(): int`
- `getPresets(): array` — lee cada preset con sus opciones
- `getCustomSelectors(): array` — lee los dynamic rows serializados
- `getAllTargets(): array` — merge de presets + custom

## 11. Estructura de datos JSON para el frontend

```json
{
    "enabled": true,
    "fadeOutDuration": 300,
    "timeout": 8000,
    "targets": [
        {
            "id": "mageplaza_banner",
            "selector": ".mageplaza-bannerslider",
            "effect": "shimmer",
            "height": "450px",
            "aspectRatio": null,
            "borderRadius": "0px",
            "bgColor": "#e0e0e0",
            "hlColor": "#f5f5f5",
            "speed": 1500,
            "pages": ["home"],
            "detection": "mutation"
        },
        {
            "id": "product_images_listing",
            "selector": ".product-image-container",
            "effect": "pulse",
            "height": "auto",
            "aspectRatio": null,
            "borderRadius": "4px",
            "bgColor": "#e0e0e0",
            "hlColor": "#f5f5f5",
            "speed": 1500,
            "pages": ["category"],
            "detection": "imgload"
        },
        {
            "id": "custom_1",
            "selector": ".my-custom-slider",
            "effect": "wave",
            "height": "300px",
            "aspectRatio": "16:9",
            "borderRadius": "8px",
            "bgColor": "#eeeeee",
            "hlColor": "#fafafa",
            "speed": 1200,
            "pages": ["home", "cms"],
            "detection": "mutation"
        }
    ]
}
```

## 12. Detección automática del tipo de observador

El JS determina automáticamente qué estrategia de observación usar:

| Selector contiene | Estrategia | Razón |
|---|---|---|
| `slider`, `carousel`, `owl`, `banner`, `swiper`, `slick` | MutationObserver | Son inyectados via JS, no basta con img load |
| `img` directo o `product-image`, `category-image` | img.onload | Imágenes estándar |
| Cualquier otro | Híbrido: MutationObserver + img.onload | Busca imágenes dentro y también observa cambios |

## 13. Notas de implementación

### CSS specificity
- Los estilos del skeleton usan `::before` pseudo-element con `position: absolute` y `z-index: 10` para no interferir con el contenido real.
- La clase `rp-skeleton-active` se agrega por JS inmediatamente al cargar, pero el `min-height` está en el CSS crítico inline que carga antes.

### RequireJS
- El JS se registra como `Rollpix_SkeletonPreloader/js/skeleton-preloader`
- Se inicializa via `data-mage-init` o `x-magento-init` en el template

### Serialización de Custom Selectors
- Los Dynamic Rows del admin se serializan como JSON en la config
- Se usa `\Magento\Framework\Serialize\Serializer\Json` para leer/escribir

### No interferencia
- Si un elemento ya tiene `owl-loaded` o imágenes cargadas al momento de inicializarse el JS, se salta sin agregar skeleton
- Si JavaScript está deshabilitado, el CSS inline solo agrega min-height (que no es destructivo) pero no agrega el overlay porque la clase `rp-skeleton-active` nunca se agrega

## 14. Testing manual

1. Habilitar el módulo, activar preset "Mageplaza Banner Slider" con height 450px
2. Cargar la home — verificar que el espacio se reserva inmediatamente (sin CLS)
3. Verificar que el shimmer se muestra y desaparece con fade al cargar el slider
4. Probar timeout: configurar a 2000ms y verificar que el skeleton desaparece aunque la imagen no haya cargado
5. Deshabilitar módulo y verificar que no queda ningún residuo visual
6. Probar con cada efecto: shimmer, pulse, wave, static
7. Probar custom selector: agregar uno apuntando a un bloque CMS con imagen
8. Verificar en mobile que las alturas se mantienen proporcionales
9. Probar la config de páginas: activar solo en Home, verificar que en categoría no aparece

## 15. Dependencias

- `magento/framework` (core)
- `magento/module-store`
- `magento/module-backend` (para system.xml)
- Ninguna dependencia de terceros
- Ninguna dependencia de Mageplaza (el módulo es agnóstico)

## 16. Futuras mejoras (fuera de v1.0)

- Breakpoints responsive por target element
- Skeleton para elementos lazy-loaded con IntersectionObserver
- Preview del skeleton en el admin
- Integración con Hyvä theme (Alpine.js en vez de RequireJS)
- Métricas de CLS antes/después en el admin
