/**
 * Copyright © Rollpix. All rights reserved.
 *
 * Skeleton Preloader - Detects element loading and removes skeleton overlays.
 */
define(['jquery'], function ($) {
    'use strict';

    return function (config) {
        if (!config || !config.targets || !config.targets.length) {
            return;
        }

        var MIN_SIZE = 50; // Minimum px in both dimensions to apply skeleton
        var SLIDER_KEYWORDS = ['slider', 'carousel', 'owl', 'banner', 'swiper', 'slick', 'pagebuilder'];
        var SLIDER_CLASSES = ['owl-loaded', 'slick-initialized', 'swiper-initialized'];

        function getDetectionType(selector) {
            var lower = selector.toLowerCase();
            for (var i = 0; i < SLIDER_KEYWORDS.length; i++) {
                if (lower.indexOf(SLIDER_KEYWORDS[i]) !== -1) {
                    return 'mutation';
                }
            }
            return 'imgload';
        }

        function isSliderInitialized(el) {
            // Check the element itself AND its descendants for slider init classes
            var $el = $(el);
            for (var i = 0; i < SLIDER_CLASSES.length; i++) {
                if ($el.hasClass(SLIDER_CLASSES[i]) || $el.find('.' + SLIDER_CLASSES[i]).length > 0) {
                    return true;
                }
            }
            return false;
        }

        function areImagesLoaded(el) {
            var images = el.querySelectorAll('img');
            if (images.length === 0) {
                return false;
            }
            var allLoaded = true;
            images.forEach(function (img) {
                if (!img.complete || img.naturalWidth === 0) {
                    allLoaded = false;
                }
            });
            return allLoaded;
        }

        function isElementLoaded(el) {
            return isSliderInitialized(el) || areImagesLoaded(el);
        }

        function isTooSmall(el) {
            var rect = el.getBoundingClientRect();
            return rect.width < MIN_SIZE || rect.height < MIN_SIZE;
        }

        function isVoidElement(el) {
            // ::before does not work on replaced/void elements
            var tag = el.tagName.toLowerCase();
            return tag === 'img' || tag === 'input' || tag === 'br' || tag === 'hr' ||
                   tag === 'embed' || tag === 'source' || tag === 'track' || tag === 'area';
        }

        function revealElement(el, fadeOutDuration) {
            var $el = $(el);
            if ($el.hasClass('rp-skeleton-loaded') || !$el.hasClass('rp-skeleton-active')) {
                return;
            }

            $el.addClass('rp-skeleton-loaded');

            setTimeout(function () {
                $el.removeClass('rp-skeleton-active rp-skeleton-loaded');
            }, fadeOutDuration + 50);
        }

        function observeMutation(el, fadeOutDuration) {
            var observer = new MutationObserver(function () {
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
                // No images yet — watch for them to appear via mutation
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

            if (!elements.length) {
                return;
            }

            var detectionType = getDetectionType(target.selector);

            elements.forEach(function (el) {
                // Skip void/replaced elements (::before doesn't work on <img>, etc.)
                if (isVoidElement(el)) {
                    return;
                }

                // Skip elements that are too small (icons, thumbnails, etc.)
                if (isTooSmall(el)) {
                    return;
                }

                // Skip if already loaded
                if (isElementLoaded(el)) {
                    return;
                }

                // Activate skeleton
                el.classList.add('rp-skeleton-active');

                var observer = null;

                if (detectionType === 'mutation') {
                    observer = observeMutation(el, config.fadeOutDuration);
                    // Also watch image loads inside slider containers
                    observeImgLoad(el, config.fadeOutDuration);
                } else {
                    observer = observeImgLoad(el, config.fadeOutDuration);
                }

                // Timeout fallback — always remove skeleton
                setTimeout(function () {
                    if (observer) {
                        observer.disconnect();
                    }
                    revealElement(el, config.fadeOutDuration);
                }, config.timeout);
            });
        });
    };
});
