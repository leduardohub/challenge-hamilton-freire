<?php

/**
 * @copyright  Copyright (c) 2023 Digital Hub (https://digitalhub.com.br)
 * @author     Dev Team <devteam@digitalhub.com.br>
 */

declare(strict_types=1);

namespace DigitalHub\RuleByDevice\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use DigitalHub\RuleByDevice\Model\Rule\Condition\Device;

class RuleConditionCombineObserver implements ObserverInterface
{
    public function __construct(
        protected readonly Device $deviceFactory
    ) {}

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $transport = $observer->getAdditional();
        $cond = $transport->getConditions();
        if (!is_array($cond)) {
            $cond = [];
        }

        $types = [
            'Device' => __('Custom Attributes')
        ];

        foreach ($types as $typeCode => $typeLabel) {
            $conditionAttributes = $this->deviceFactory->loadAttributeOptions()->getAttributeOption();
            $attributes = [];

            foreach ($conditionAttributes as $code => $label) {
                $attributes[] = [
                    'value' => 'DigitalHub\RuleByDevice\Model\Rule\Condition\\' . $typeCode . '|' . $code,
                    'label' => $label,
                ];
            }

            $cond[] = [
                'value' => $attributes,
                'label' => __($typeLabel),
            ];
        }

        $transport->setConditions($cond);

        return $this;
    }
}
