# Claude Code Prompt: Rollpix_SkeletonPreloader Module

## Contexto

Necesito que construyas un módulo completo para Magento 2 llamado `Rollpix_SkeletonPreloader`. El módulo inyecta skeletons animados (shimmer/pulse/wave/static) sobre elementos HTML configurables para prevenir Content Layout Shift (CLS) mientras cargan imágenes, sliders y otros componentes pesados.

**Restricciones técnicas obligatorias:**
- PHP 8.1 ~ 8.3
- Magento 2.4.7 ~ 2.4.8
- Vendor: Rollpix
- Theme: Luma-based (RequireJS, jQuery)
- NO debe modificar archivos de módulos de terceros
- NO tiene dependencias de módulos de terceros
- Debe seguir Magento Coding Standards (PSR-12, strict types)

## Estructura de archivos a crear

Crear TODOS estos archivos dentro de `app/code/Rollpix/SkeletonPreloader/`:

```
registration.php
composer.json
etc/module.xml
etc/config.xml
etc/adminhtml/system.xml
etc/frontend/di.xml
Model/Config.php
ViewModel/SkeletonConfig.php
view/frontend/layout/default.xml
view/frontend/templates/skeleton-critical-css.phtml
view/frontend/templates/skeleton-init.phtml
view/frontend/web/css/skeleton-preloader.css
view/frontend/web/js/skeleton-preloader.js
```

## Especificaciones por archivo

### registration.php
Registro estándar de componente Magento: `Rollpix_SkeletonPreloader`.

### composer.json
- name: `rollpix/module-skeleton-preloader`
- description: "Skeleton shimmer preloader to prevent CLS on image-heavy elements"
- type: magento2-module
- autoload psr-4: `Rollpix\\SkeletonPreloader\\`
- require: `php: ~8.1|~8.2|~8.3`, `magento/framework: *`
- version: 1.0.0

### etc/module.xml
- Module: `Rollpix_SkeletonPreloader`
- setup_version: 1.0.0
- Sequence: `Magento_Store`, `Magento_Backend`

### etc/config.xml
Valores por defecto bajo `rollpix_skeleton/`:

```xml
<default>
    <rollpix_skeleton>
        <general>
            <enabled>1</enabled>
            <pages>home,category,product,cms</pages>
            <fade_out_duration>300</fade_out_duration>
            <skeleton_timeout>8000</skeleton_timeout>
        </general>
        <presets>
            <mageplaza_banner>
                <enabled>0</enabled>
                <selector>.mageplaza-bannerslider</selector>
                <effect>shimmer</effect>
                <height>450px</height>
                <aspect_ratio></aspect_ratio>
                <border_radius>0</border_radius>
                <bg_color>#e0e0e0</bg_color>
                <hl_color>#f5f5f5</hl_color>
                <animation_speed>1500</animation_speed>
            </mageplaza_banner>
            <product_images_listing>
                <enabled>0</enabled>
                <selector>.product-image-container</selector>
                <effect>shimmer</effect>
                <height>auto</height>
                <aspect_ratio></aspect_ratio>
                <border_radius>0</border_radius>
                <bg_color>#e0e0e0</bg_color>
                <hl_color>#f5f5f5</hl_color>
                <animation_speed>1500</animation_speed>
            </product_images_listing>
            <product_image_pdp>
                <enabled>0</enabled>
                <selector>.gallery-placeholder</selector>
                <effect>shimmer</effect>
                <height>auto</height>
                <aspect_ratio></aspect_ratio>
                <border_radius>0</border_radius>
                <bg_color>#e0e0e0</bg_color>
                <hl_color>#f5f5f5</hl_color>
                <animation_speed>1500</animation_speed>
            </product_image_pdp>
            <category_image>
                <enabled>0</enabled>
                <selector>.category-image</selector>
                <effect>shimmer</effect>
                <height>auto</height>
                <aspect_ratio></aspect_ratio>
                <border_radius>0</border_radius>
                <bg_color>#e0e0e0</bg_color>
                <hl_color>#f5f5f5</hl_color>
                <animation_speed>1500</animation_speed>
            </category_image>
            <cms_block_images>
                <enabled>0</enabled>
                <selector>.widget img, .block-static-block img</selector>
                <effect>shimmer</effect>
                <height>auto</height>
                <aspect_ratio></aspect_ratio>
                <border_radius>0</border_radius>
                <bg_color>#e0e0e0</bg_color>
                <hl_color>#f5f5f5</hl_color>
                <animation_speed>1500</animation_speed>
            </cms_block_images>
            <pagebuilder_images>
                <enabled>0</enabled>
                <selector>.pagebuilder-banner-wrapper, .pagebuilder-slide-wrapper</selector>
                <effect>shimmer</effect>
                <height>400px</height>
                <aspect_ratio></aspect_ratio>
                <border_radius>0</border_radius>
                <bg_color>#e0e0e0</bg_color>
                <hl_color>#f5f5f5</hl_color>
                <animation_speed>1500</animation_speed>
            </pagebuilder_images>
        </presets>
        <custom>
            <selectors></selectors>
        </custom>
    </rollpix_skeleton>
</default>
```

### etc/adminhtml/system.xml

Path: `Stores > Configuration > Rollpix > Skeleton Preloader`

Tab: `rollpix` con label "Rollpix"

**Section `rollpix_skeleton`** con 3 groups:

**Group `general`:**
- `enabled`: Yes/No dropdown
- `pages`: Multiselect con opciones: Home, Category, Product, CMS, All. Usar un source model custom `Model\Config\Source\Pages`
- `fade_out_duration`: Text field, validación numérica, comment "Duración del fade-out en milisegundos"
- `skeleton_timeout`: Text field, validación numérica, comment "Tiempo máximo del skeleton antes de removerse (ms). Fallback de seguridad."

**Group `presets`:**
Para CADA uno de los 6 presets (mageplaza_banner, product_images_listing, product_image_pdp, category_image, cms_block_images, pagebuilder_images), crear campos:
- `enabled`: Yes/No
- `selector`: Text (readonly o con comment, solo informativo ya que el selector viene hardcoded)
- `effect`: Select con source model `Model\Config\Source\Effects` (Shimmer, Pulse, Wave, Static)
- `height`: Text, comment "Altura en px (ej: 450px) o 'auto' para no reservar"
- `aspect_ratio`: Text, comment "Aspect ratio (ej: 16:9, 3:1). Si se define, sobreescribe height."
- `border_radius`: Text, comment "Border radius en px"
- `bg_color`: Text (usaremos type text, no hay color picker nativo en Magento), comment "Color hexadecimal base (ej: #e0e0e0)"
- `hl_color`: Text, comment "Color hexadecimal highlight (ej: #f5f5f5)"
- `animation_speed`: Text, validación numérica, comment "Velocidad de animación en ms"

Organizar los presets cada uno en un sub-grupo con label descriptivo y `<comment>` mostrando el selector CSS que usan.

**Group `custom`:**
- `selectors`: Textarea field, comment "JSON array de custom selectors. Formato: ver documentación."

**IMPORTANTE:** Como Magento no soporta dynamic rows nativamente en system.xml sin backend models complejos, usar un textarea con formato JSON para los custom selectors. El JSON esperado es:
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
  }
]
```

Agregar un `<comment>` largo en el campo explicando el formato JSON con un ejemplo.

### Source Models

Crear:
- `Model/Config/Source/Pages.php` — options: home, category, product, cms, all
- `Model/Config/Source/Effects.php` — options: shimmer, pulse, wave, static

### Model/Config.php

Clase que encapsula lectura de toda la configuración:

```php
<?php
declare(strict_types=1);

namespace Rollpix\SkeletonPreloader\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Serialize\Serializer\Json;
```

Métodos requeridos:
- `isEnabled(): bool` — lee `rollpix_skeleton/general/enabled`
- `getAllowedPages(): array` — explode por coma del value de `pages`
- `getFadeOutDuration(): int`
- `getSkeletonTimeout(): int`
- `getPresets(): array` — itera los 6 presets, retorna solo los enabled con todos sus campos
- `getCustomSelectors(): array` — parsea el JSON del textarea, retorna array de targets
- `getAllTargets(): array` — merge getPresets() + getCustomSelectors() en un array unificado

Cada target en el array unificado tiene esta estructura:
```php
[
    'id' => string,
    'selector' => string,
    'effect' => string,
    'height' => string,
    'aspectRatio' => string|null,
    'borderRadius' => string,
    'bgColor' => string,
    'hlColor' => string,
    'speed' => int,
    'pages' => array, // ['home', 'category', ...]
]
```

Los presets usan las `pages` del campo general. Los custom selectors tienen su propio campo `pages`.

### etc/frontend/di.xml

Declarar el ViewModel:
```xml
<type name="Rollpix\SkeletonPreloader\ViewModel\SkeletonConfig">
    <arguments>
        <argument name="config" xsi:type="object">Rollpix\SkeletonPreloader\Model\Config</argument>
    </arguments>
</type>
```

### ViewModel/SkeletonConfig.php

```php
<?php
declare(strict_types=1);

namespace Rollpix\SkeletonPreloader\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
```

Métodos:
- `isEnabled(): bool`
- `getCriticalCss(): string` — genera CSS inline con min-heights y pseudo-element overlays para cada target. Solo genera CSS para targets cuyas pages incluyen la página actual.
- `getJsConfigJson(): string` — serializa a JSON la config completa para el frontend
- `getCurrentPageType(): string` — detecta el page type leyendo el full action name del request (`cms_index_index` → home, `catalog_category_view` → category, etc.)

El `getCriticalCss()` genera este CSS por cada target:

```css
/* Target: {id} */
{selector} {
    min-height: {height}; /* omitir si es "auto" */
    aspect-ratio: {aspectRatio}; /* solo si está definido */
    position: relative;
    overflow: hidden;
}

{selector}.rp-skeleton-active::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    z-index: 10;
    border-radius: {borderRadius}px;
    /* el background y animation se definen según el effect */
}
```

Para el effect en `::before`:
- **shimmer**: `background: linear-gradient(90deg, {bgColor} 25%, {hlColor} 50%, {bgColor} 75%); background-size: 200% 100%; animation: rp-skeleton-shimmer {speed}ms infinite linear;`
- **pulse**: `background: {bgColor}; animation: rp-skeleton-pulse {speed}ms ease-in-out infinite;`
- **wave**: `background: linear-gradient(90deg, {bgColor} 40%, {hlColor} 50%, {bgColor} 60%); background-size: 300% 100%; animation: rp-skeleton-wave {speed}ms ease-in-out infinite;`
- **static**: `background: {bgColor};` (sin animation)

Agregar al final del CSS crítico los keyframes generales:

```css
@keyframes rp-skeleton-shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
@keyframes rp-skeleton-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.4; }
}
@keyframes rp-skeleton-wave {
    0% { background-position: 300% 0; }
    100% { background-position: -300% 0; }
}
@keyframes rp-skeleton-fadeout {
    0% { opacity: 1; }
    100% { opacity: 0; visibility: hidden; }
}

.rp-skeleton-loaded::before {
    animation: rp-skeleton-fadeout {fadeOutDuration}ms forwards !important;
}
```

### view/frontend/layout/default.xml

```xml
<?xml version="1.0"?>
<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">
    <head>
        <css src="Rollpix_SkeletonPreloader::css/skeleton-preloader.css"/>
    </head>
    <body>
        <!-- CSS crítico inline en head -->
        <referenceContainer name="head.additional">
            <block class="Magento\Framework\View\Element\Template"
                   name="rollpix.skeleton.critical.css"
                   template="Rollpix_SkeletonPreloader::skeleton-critical-css.phtml">
                <arguments>
                    <argument name="skeleton_config" xsi:type="object">Rollpix\SkeletonPreloader\ViewModel\SkeletonConfig</argument>
                </arguments>
            </block>
        </referenceContainer>
        <!-- JS init al final del body -->
        <referenceContainer name="before.body.end">
            <block class="Magento\Framework\View\Element\Template"
                   name="rollpix.skeleton.init"
                   template="Rollpix_SkeletonPreloader::skeleton-init.phtml">
                <arguments>
                    <argument name="skeleton_config" xsi:type="object">Rollpix\SkeletonPreloader\ViewModel\SkeletonConfig</argument>
                </arguments>
            </block>
        </referenceContainer>
    </body>
</page>
```

### view/frontend/templates/skeleton-critical-css.phtml

```php
<?php
/** @var \Magento\Framework\View\Element\Template $block */
/** @var \Rollpix\SkeletonPreloader\ViewModel\SkeletonConfig $config */
$config = $block->getData('skeleton_config');
if (!$config->isEnabled()) {
    return;
}
$criticalCss = $config->getCriticalCss();
if (empty($criticalCss)) {
    return;
}
?>
<style id="rp-skeleton-critical"><?= /* @noEscape */ $criticalCss ?></style>
```

### view/frontend/templates/skeleton-init.phtml

```php
<?php
/** @var \Magento\Framework\View\Element\Template $block */
/** @var \Rollpix\SkeletonPreloader\ViewModel\SkeletonConfig $config */
$config = $block->getData('skeleton_config');
if (!$config->isEnabled()) {
    return;
}
?>
<script type="text/x-magento-init">
{
    "*": {
        "Rollpix_SkeletonPreloader/js/skeleton-preloader": <?= /* @noEscape */ $config->getJsConfigJson() ?>
    }
}
</script>
```

### view/frontend/web/css/skeleton-preloader.css

Estilos base (no-críticos, complementarios):

```css
/**
 * Rollpix_SkeletonPreloader - Base styles
 * Los estilos críticos se inyectan inline en <head> vía phtml
 */

/* Asegurar que los elementos con skeleton tengan positioning context */
.rp-skeleton-active {
    position: relative !important;
    overflow: hidden !important;
}

/* Asegurar z-index del pseudo-element */
.rp-skeleton-active::before {
    pointer-events: none;
}

/* Clase de utilidad para ocultar contenido durante skeleton */
.rp-skeleton-active > * {
    /* No ocultar, solo el overlay cubre */
}

/* Cuando está loaded, preparar transición de min-height */
.rp-skeleton-loaded {
    min-height: auto !important;
    transition: min-height 0.3s ease;
}
```

### view/frontend/web/js/skeleton-preloader.js

Componente RequireJS (`define`). Lógica completa:

```javascript
define(['jquery'], function ($) {
    'use strict';

    return function (config) {
        // config contiene: fadeOutDuration, timeout, targets[]

        if (!config || !config.targets || !config.targets.length) {
            return;
        }

        var SLIDER_KEYWORDS = ['slider', 'carousel', 'owl', 'banner', 'swiper', 'slick', 'pagebuilder'];

        function getDetectionType(selector) {
            var lower = selector.toLowerCase();
            for (var i = 0; i < SLIDER_KEYWORDS.length; i++) {
                if (lower.indexOf(SLIDER_KEYWORDS[i]) !== -1) {
                    return 'mutation';
                }
            }
            if (lower.indexOf('img') !== -1) {
                return 'imgload';
            }
            return 'hybrid';
        }

        function isElementLoaded(el) {
            // Check si ya tiene imágenes cargadas o clases de slider inicializado
            var $el = $(el);
            if ($el.hasClass('owl-loaded') || $el.hasClass('slick-initialized') || $el.hasClass('swiper-initialized')) {
                return true;
            }
            var images = el.querySelectorAll('img');
            if (images.length > 0) {
                var allLoaded = true;
                images.forEach(function(img) {
                    if (!img.complete || img.naturalWidth === 0) {
                        allLoaded = false;
                    }
                });
                return allLoaded;
            }
            return false;
        }

        function revealElement(el, fadeOutDuration) {
            var $el = $(el);
            if ($el.hasClass('rp-skeleton-loaded')) return;
            $el.addClass('rp-skeleton-loaded');
            setTimeout(function () {
                $el.removeClass('rp-skeleton-active rp-skeleton-loaded');
            }, fadeOutDuration + 50);
        }

        function observeMutation(el, fadeOutDuration) {
            var observer = new MutationObserver(function (mutations) {
                if (isElementLoaded(el)) {
                    observer.disconnect();
                    revealElement(el, fadeOutDuration);
                }
            });
            observer.observe(el, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class', 'src']
            });
            return observer;
        }

        function observeImgLoad(el, fadeOutDuration) {
            var images = el.querySelectorAll('img');
            if (images.length === 0) {
                // Esperar a que aparezcan imágenes via mutation
                return observeMutation(el, fadeOutDuration);
            }
            var pending = images.length;
            images.forEach(function (img) {
                if (img.complete && img.naturalWidth > 0) {
                    pending--;
                } else {
                    $(img).one('load error', function () {
                        pending--;
                        if (pending <= 0) {
                            revealElement(el, fadeOutDuration);
                        }
                    });
                }
            });
            if (pending <= 0) {
                revealElement(el, fadeOutDuration);
            }
            return null;
        }

        // Process each target
        config.targets.forEach(function (target) {
            var elements = document.querySelectorAll(target.selector);
            if (!elements.length) return;

            var detectionType = getDetectionType(target.selector);

            elements.forEach(function (el) {
                // Skip si ya está cargado
                if (isElementLoaded(el)) return;

                // Activar skeleton
                el.classList.add('rp-skeleton-active');

                // Iniciar observación
                var observer = null;
                if (detectionType === 'mutation') {
                    observer = observeMutation(el, config.fadeOutDuration);
                } else if (detectionType === 'imgload') {
                    observer = observeImgLoad(el, config.fadeOutDuration);
                } else {
                    // hybrid
                    observer = observeMutation(el, config.fadeOutDuration);
                    observeImgLoad(el, config.fadeOutDuration);
                }

                // Timeout fallback
                setTimeout(function () {
                    if (observer) observer.disconnect();
                    revealElement(el, config.fadeOutDuration);
                }, config.timeout);
            });
        });
    };
});
```

## Instrucciones adicionales

1. **Todos los archivos PHP deben tener `declare(strict_types=1);`** como primera línea después del tag PHP.

2. **El ViewModel debe detectar la página actual** usando `\Magento\Framework\App\Request\Http` inyectado en constructor. Mapeo de full action name:
   - `cms_index_index` → `home`
   - `catalog_category_view` → `category`
   - `catalog_product_view` → `product`
   - `cms_page_view` → `cms`

3. **El filtrado de targets por página** se hace en el ViewModel: `getCriticalCss()` y `getJsConfigJson()` solo incluyen targets cuyas `pages` incluyen la página actual o incluyen `all`.

4. **La config de presets en el admin** debe organizarse con un fieldset por preset, cada uno con un `<label>` descriptivo y un `<comment>` mostrando el selector CSS.

5. **Manejo de errores en custom selectors JSON**: Si el JSON del textarea es inválido, loguear un warning y retornar array vacío. No romper la página.

6. **Para los colores en system.xml**, usar `<frontend_type>text</frontend_type>` con un comment pidiendo formato hexadecimal. Magento no tiene color picker nativo en system.xml sin JS custom.

7. **El CSS inline generado en skeleton-critical-css.phtml** debe estar minificado (sin saltos de línea innecesarios en producción). Usar el método del ViewModel para generar el CSS como un string compacto.

8. **Testing**: Después de crear todos los archivos, verificar que no haya errores de sintaxis PHP ejecutando `php -l` en cada archivo PHP.

## Resultado esperado

Un módulo 100% funcional que al habilitar y activar el preset "Mageplaza Banner Slider" con height 450px, muestre un skeleton shimmer animado en el contenedor del banner de la home page de Magento, previniendo el CLS, y que desaparezca con un fade suave cuando el slider termine de cargar. El módulo debe ser extensible para cualquier otro elemento vía la configuración de custom selectors.
