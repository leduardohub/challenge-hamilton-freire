<?php

/**
 * @copyright  Copyright (c) 2023 Digital Hub (https://digitalhub.com.br)
 * @author     Dev Team <devteam@digitalhub.com.br>
 */

declare(strict_types=1);

namespace DigitalHub\RuleByDevice\Test\Unit\Model\Rule\Condition;

use DigitalHub\RuleByDevice\Api\System\ConfigInterface;
use DigitalHub\RuleByDevice\Model\Rule\Condition\Device;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Model\AbstractModel;
use Magento\Rule\Model\Condition\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DeviceTest extends TestCase
{
    const HEADER_ANDROID = 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36';
    const HEADER_IOS = 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1';
    const HEADER_WEB= 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36';

    private Device $device;
    private ConfigInterface $configMock;
    private Header $headerMock;
    private Context $contextMock;

    /**
     * Set up test
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configMock = $this->createMock(ConfigInterface::class);
        $this->headerMock = $this->createMock(Header::class);
        $this->contextMock = $this->createMock(Context::class);

        $this->device = $this->getMockBuilder(Device::class)
            ->setConstructorArgs([
                $this->configMock,
                $this->headerMock,
                $this->contextMock,
                []
            ])
            ->onlyMethods(['validateAttribute'])
            ->getMock();
    }

    /**
     * @return void
     */
    public function testGetCurrentDeviceTypeIos(): void
    {
        $this->headerMock
            ->method('getHttpUserAgent')
            ->willReturn(static::HEADER_IOS);

        $this->assertEquals('IOS', $this->device->getCurrentDeviceType());
    }

    /**
     * @return void
     */
    public function testGetCurrentDeviceTypeAndroid(): void
    {
        $this->headerMock
            ->method('getHttpUserAgent')
            ->willReturn(static::HEADER_ANDROID);

        $this->assertEquals('ANDROID', $this->device->getCurrentDeviceType());
    }

    /**
     * @return void
     */
    public function testGetCurrentDeviceTypeWeb(): void
    {
        $this->headerMock
            ->method('getHttpUserAgent')
            ->willReturn(static::HEADER_WEB);

        $this->assertEquals('WEB', $this->device->getCurrentDeviceType());
    }

    /**
     * @return void
     */
    public function testGetRuleByDeviceOptions(): void
    {
        $this->configMock
            ->method('getDeviceList')
            ->willReturn('IOS, ANDROID ,WEB');

        $this->assertEquals(
            ['IOS', 'ANDROID', 'WEB'],
            $this->device->getRuleByDeviceOptions()
        );
    }

    /**
     * @return void
     */
    public function testGetValueSelectOptions(): void
    {
        $this->configMock
            ->method('getDeviceList')
            ->willReturn('IOS,ANDROID');

        $this->assertEquals(
            [
                ['value' => 'IOS', 'label' => 'IOS'],
                ['value' => 'ANDROID', 'label' => 'ANDROID'],
            ],
            $this->device->getValueSelectOptions()
        );
    }

    /**
     * @return void
     */
    public function testValidateReturnsTrueWhenEnabledAndDeviceAllowed(): void
    {
        $modelMock = $this->createMock(AbstractModel::class);

        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('getDeviceList')->willReturn('IOS, ANDROID, WEB');

        $this->headerMock
            ->method('getHttpUserAgent')
            ->willReturn(static::HEADER_IOS);

        $this->device
            ->method('validateAttribute')
            ->with('IOS')
            ->willReturn(true);

        $this->assertTrue($this->device->validate($modelMock));
    }

    /**
     * @return void
     */
    public function testValidateReturnsTrueWhenEnabledAndDeviceNotAllowed(): void
    {
        $modelMock = $this->createMock(AbstractModel::class);

        $this->configMock->method('isEnabled')->willReturn(true);
        $this->configMock->method('getDeviceList')->willReturn('ANDROID, WEB');

        $this->headerMock
            ->method('getHttpUserAgent')
            ->willReturn(static::HEADER_IOS);

        $this->device
            ->method('validateAttribute')
            ->with('IOS')
            ->willReturn(true);

        $this->assertFalse($this->device->validate($modelMock));
    }

    /**
     * @return void
     */
    public function testValidateReturnsFalseWhenEnabledAndDeviceNotAllowed(): void
    {
        $modelMock = $this->createMock(AbstractModel::class);

        $this->configMock->method('isEnabled')->willReturn(false);
        $this->configMock->method('getDeviceList')->willReturn('ANDROID, WEB');

        $this->headerMock
            ->method('getHttpUserAgent')
            ->willReturn(static::HEADER_ANDROID);

        $this->device
            ->method('validateAttribute')
            ->with('ANDROID')
            ->willReturn(true);

        $this->assertFalse($this->device->validate($modelMock));
    }
}
