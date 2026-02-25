<?php
declare(strict_types=1);

namespace Rollpix\SkeletonPreloader\ViewModel;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Rollpix\SkeletonPreloader\Model\Config;

class SkeletonConfig implements ArgumentInterface
{
    private const ACTION_TO_PAGE = [
        'cms_index_index' => 'home',
        'catalog_category_view' => 'category',
        'catalog_product_view' => 'product',
        'cms_page_view' => 'cms',
    ];

    public function __construct(
        private readonly Config $config,
        private readonly HttpRequest $request,
        private readonly Json $json
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }

    public function getCurrentPageType(): string
    {
        $fullActionName = $this->request->getFullActionName();
        return self::ACTION_TO_PAGE[$fullActionName] ?? '';
    }

    public function getCriticalCss(): string
    {
        $targets = $this->getFilteredTargets();

        if (empty($targets)) {
            return '';
        }

        $css = '';
        $fadeOutDuration = $this->config->getFadeOutDuration();

        foreach ($targets as $target) {
            $selector = $target['selector'];
            $bgColor = $target['bgColor'];
            $hlColor = $target['hlColor'];
            $speed = $target['speed'];
            $borderRadius = $target['borderRadius'];
            $effect = $target['effect'];

            // All styles scoped to .rp-skeleton-active so they are removed cleanly
            $css .= $selector . '.rp-skeleton-active{position:relative;overflow:hidden;';

            if ($target['aspectRatio'] !== null) {
                $css .= 'aspect-ratio:' . $target['aspectRatio'] . ';';
            } elseif ($target['height'] !== 'auto') {
                $css .= 'min-height:' . $target['height'] . ';';
            }

            $css .= '}';

            // Pseudo-element overlay
            $css .= $selector . '.rp-skeleton-active::before{';
            $css .= "content:'';position:absolute;top:0;left:0;right:0;bottom:0;z-index:10;";
            $css .= 'border-radius:' . $borderRadius . 'px;';

            switch ($effect) {
                case 'shimmer':
                    $css .= 'background:linear-gradient(90deg,'
                        . $bgColor . ' 25%,' . $hlColor . ' 50%,' . $bgColor . ' 75%);';
                    $css .= 'background-size:200% 100%;';
                    $css .= 'animation:rp-skeleton-shimmer ' . $speed . 'ms infinite linear;';
                    break;
                case 'pulse':
                    $css .= 'background:' . $bgColor . ';';
                    $css .= 'animation:rp-skeleton-pulse ' . $speed . 'ms ease-in-out infinite;';
                    break;
                case 'wave':
                    $css .= 'background:linear-gradient(90deg,'
                        . $bgColor . ' 40%,' . $hlColor . ' 50%,' . $bgColor . ' 60%);';
                    $css .= 'background-size:300% 100%;';
                    $css .= 'animation:rp-skeleton-wave ' . $speed . 'ms ease-in-out infinite;';
                    break;
                case 'static':
                    $css .= 'background:' . $bgColor . ';';
                    break;
            }

            $css .= '}';
        }

        // Keyframes
        $css .= '@keyframes rp-skeleton-shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}';
        $css .= '@keyframes rp-skeleton-pulse{0%,100%{opacity:1}50%{opacity:.4}}';
        $css .= '@keyframes rp-skeleton-wave{0%{background-position:300% 0}100%{background-position:-300% 0}}';
        $css .= '@keyframes rp-skeleton-fadeout{0%{opacity:1}100%{opacity:0;visibility:hidden}}';
        $css .= '.rp-skeleton-loaded::before{animation:rp-skeleton-fadeout ' . $fadeOutDuration . 'ms forwards!important}';

        return $css;
    }

    public function getJsConfigJson(): string
    {
        $targets = $this->getFilteredTargets();

        $jsTargets = [];
        foreach ($targets as $target) {
            $jsTargets[] = [
                'id' => $target['id'],
                'selector' => $target['selector'],
                'effect' => $target['effect'],
                'height' => $target['height'],
                'aspectRatio' => $target['aspectRatio'],
                'borderRadius' => $target['borderRadius'],
                'bgColor' => $target['bgColor'],
                'hlColor' => $target['hlColor'],
                'speed' => $target['speed'],
            ];
        }

        return $this->json->serialize([
            'fadeOutDuration' => $this->config->getFadeOutDuration(),
            'timeout' => $this->config->getSkeletonTimeout(),
            'targets' => $jsTargets,
        ]);
    }

    private function getFilteredTargets(): array
    {
        $currentPage = $this->getCurrentPageType();
        $allTargets = $this->config->getAllTargets();

        return array_filter($allTargets, function (array $target) use ($currentPage): bool {
            $pages = $target['pages'] ?? [];

            if (in_array('all', $pages, true)) {
                return true;
            }

            if ($currentPage === '') {
                return false;
            }

            return in_array($currentPage, $pages, true);
        });
    }
}
