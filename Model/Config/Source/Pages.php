<?php
declare(strict_types=1);

namespace Rollpix\SkeletonPreloader\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Pages implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'home', 'label' => __('Home')],
            ['value' => 'category', 'label' => __('Category')],
            ['value' => 'product', 'label' => __('Product')],
            ['value' => 'cms', 'label' => __('CMS')],
            ['value' => 'all', 'label' => __('All Pages')],
        ];
    }
}
