<?php
declare(strict_types=1);

namespace Rollpix\SkeletonPreloader\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class Config
{
    private const XML_PATH_PREFIX = 'rollpix_skeleton/';
    private const XML_PATH_GENERAL = 'rollpix_skeleton/general/';
    private const XML_PATH_PRESETS = 'rollpix_skeleton/presets/';
    private const XML_PATH_CUSTOM = 'rollpix_skeleton/custom/selectors';

    private const PRESET_IDS = [
        'mageplaza_banner',
        'product_images_listing',
        'product_image_pdp',
        'category_image',
        'cms_block_images',
        'pagebuilder_images',
    ];

    private const PRESET_FIELDS = [
        'enabled',
        'selector',
        'effect',
        'height',
        'aspect_ratio',
        'border_radius',
        'bg_color',
        'hl_color',
        'animation_speed',
    ];

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly Json $json,
        private readonly LoggerInterface $logger
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_GENERAL . 'enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getAllowedPages(): array
    {
        $value = (string) $this->scopeConfig->getValue(
            self::XML_PATH_GENERAL . 'pages',
            ScopeInterface::SCOPE_STORE
        );

        if ($value === '') {
            return [];
        }

        return array_map('trim', explode(',', $value));
    }

    public function getFadeOutDuration(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_GENERAL . 'fade_out_duration',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getSkeletonTimeout(): int
    {
        return (int) $this->scopeConfig->getValue(
            self::XML_PATH_GENERAL . 'skeleton_timeout',
            ScopeInterface::SCOPE_STORE
        );
    }

    public function getPresets(): array
    {
        $presets = [];

        foreach (self::PRESET_IDS as $presetId) {
            $basePath = self::XML_PATH_PRESETS . $presetId . '/';

            $enabled = $this->scopeConfig->isSetFlag(
                $basePath . 'enabled',
                ScopeInterface::SCOPE_STORE
            );

            if (!$enabled) {
                continue;
            }

            $presets[] = [
                'id' => $presetId,
                'selector' => (string) $this->scopeConfig->getValue(
                    $basePath . 'selector',
                    ScopeInterface::SCOPE_STORE
                ),
                'effect' => (string) $this->scopeConfig->getValue(
                    $basePath . 'effect',
                    ScopeInterface::SCOPE_STORE
                ),
                'height' => (string) $this->scopeConfig->getValue(
                    $basePath . 'height',
                    ScopeInterface::SCOPE_STORE
                ),
                'aspectRatio' => $this->normalizeAspectRatio(
                    (string) $this->scopeConfig->getValue(
                        $basePath . 'aspect_ratio',
                        ScopeInterface::SCOPE_STORE
                    )
                ),
                'borderRadius' => (string) $this->scopeConfig->getValue(
                    $basePath . 'border_radius',
                    ScopeInterface::SCOPE_STORE
                ),
                'bgColor' => (string) $this->scopeConfig->getValue(
                    $basePath . 'bg_color',
                    ScopeInterface::SCOPE_STORE
                ),
                'hlColor' => (string) $this->scopeConfig->getValue(
                    $basePath . 'hl_color',
                    ScopeInterface::SCOPE_STORE
                ),
                'speed' => (int) $this->scopeConfig->getValue(
                    $basePath . 'animation_speed',
                    ScopeInterface::SCOPE_STORE
                ),
                'pages' => $this->getAllowedPages(),
            ];
        }

        return $presets;
    }

    public function getCustomSelectors(): array
    {
        $value = (string) $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOM,
            ScopeInterface::SCOPE_STORE
        );

        if ($value === '') {
            return [];
        }

        try {
            $items = $this->json->unserialize($value);
        } catch (\InvalidArgumentException $e) {
            $this->logger->warning(
                'Rollpix_SkeletonPreloader: Invalid JSON in custom selectors configuration.',
                ['exception' => $e]
            );
            return [];
        }

        if (!is_array($items)) {
            return [];
        }

        $targets = [];
        $index = 0;

        foreach ($items as $item) {
            if (!is_array($item) || empty($item['selector'])) {
                continue;
            }

            $index++;
            $pages = [];
            if (!empty($item['pages'])) {
                $pages = array_map('trim', explode(',', (string) $item['pages']));
            }

            $targets[] = [
                'id' => 'custom_' . $index,
                'selector' => (string) $item['selector'],
                'effect' => (string) ($item['effect'] ?? 'shimmer'),
                'height' => (string) ($item['height'] ?? 'auto'),
                'aspectRatio' => $this->normalizeAspectRatio(
                    (string) ($item['aspect_ratio'] ?? '')
                ),
                'borderRadius' => (string) ($item['border_radius'] ?? '0'),
                'bgColor' => (string) ($item['bg_color'] ?? '#e0e0e0'),
                'hlColor' => (string) ($item['hl_color'] ?? '#f5f5f5'),
                'speed' => (int) ($item['animation_speed'] ?? 1500),
                'pages' => $pages,
            ];
        }

        return $targets;
    }

    public function getAllTargets(): array
    {
        return array_merge($this->getPresets(), $this->getCustomSelectors());
    }

    private function normalizeAspectRatio(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        // Convert "16:9" to "16/9" for CSS aspect-ratio property
        if (str_contains($value, ':')) {
            return str_replace(':', '/', $value);
        }

        return $value;
    }
}
