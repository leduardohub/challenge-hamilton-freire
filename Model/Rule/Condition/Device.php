<?php

/**
 * @copyright  Copyright (c) 2023 Digital Hub (https://digitalhub.com.br)
 * @author     Dev Team <devteam@digitalhub.com.br>
 */

declare(strict_types=1);

namespace DigitalHub\RuleByDevice\Model\Rule\Condition;

use DigitalHub\RuleByDevice\Api\System\ConfigInterface;
use Laminas\Http\Header\UserAgent;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\AbstractCondition;
use Magento\Rule\Model\Condition\Context;

class Device extends AbstractCondition
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly Header $header,
        Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption([
            'dh_rule_by_device' => __('Device type')
        ]);

        return $this;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType(): string
    {
        return 'select';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType(): string
    {
        return 'select';
    }

    /**
     * Get value select options
     *
     * @return array|mixed
     */
    public function getValueSelectOptions(): mixed
    {
        if (!$this->hasData('value_select_options')) {
            $options = [];

            foreach ($this->getRuleByDeviceOptions() as $ruleByDeviceOption) {
                $options[] = ['value' => $ruleByDeviceOption, 'label' => $ruleByDeviceOption];
            }

            $this->setData(
                'value_select_options',
                $options
            );
        }

        return $this->getData('value_select_options');
    }

    /**
     * Validate Device Type Rule Condition
     *
     * @param AbstractModel $model
     * @return bool
     */
    public function validate(AbstractModel $model): bool
    {
        $currentDeviceType = $this->getCurrentDeviceType();

        if ($this->isAvailable($currentDeviceType)) {
            return $this->validateAttribute($currentDeviceType);
        }

        return false;
    }

    /**
     * Get current Device Type
     *
     * @return string
     */
    public function getCurrentDeviceType(): string
    {
        $userAgent = $this->getUserAgent();

        return match (true) {
            (bool)preg_match('/iPhone|iPad|iPod/i', $userAgent) => 'IOS',
            (bool)preg_match('/android/i', $userAgent) => 'ANDROID',
            default => 'WEB',
        };
    }

    /**
     * Get Options
     *
     * @return array
     */
    public function getRuleByDeviceOptions(): array
    {
        $optionsByConfig = $this->config->getDeviceList();

        return array_map('trim',
            explode(',', $optionsByConfig)
        );
    }

    /**
     * @return string
     */
    private function getUserAgent(): string
    {
        return $this->header->getHttpUserAgent();
    }

    /**
     * @param $deviceType
     * @return bool
     */
    private function isAvailable($deviceType): bool
    {
        return $this->config->isEnabled() && in_array($deviceType, $this->getRuleByDeviceOptions());
    }
}
