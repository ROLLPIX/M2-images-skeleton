<?php
declare(strict_types=1);

namespace Rollpix\SkeletonPreloader\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Effects implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'shimmer', 'label' => __('Shimmer')],
            ['value' => 'pulse', 'label' => __('Pulse')],
            ['value' => 'wave', 'label' => __('Wave')],
            ['value' => 'static', 'label' => __('Static')],
        ];
    }
}
