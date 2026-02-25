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
            var $el = $(el);

            if ($el.hasClass('owl-loaded') ||
                $el.hasClass('slick-initialized') ||
                $el.hasClass('swiper-initialized')) {
                return true;
            }

            var images = el.querySelectorAll('img');

            if (images.length > 0) {
                var allLoaded = true;

                images.forEach(function (img) {
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

            if ($el.hasClass('rp-skeleton-loaded')) {
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
                if (isElementLoaded(el)) {
                    return;
                }

                el.classList.add('rp-skeleton-active');

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
                    if (observer) {
                        observer.disconnect();
                    }

                    revealElement(el, config.fadeOutDuration);
                }, config.timeout);
            });
        });
    };
});
