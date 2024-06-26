<?php

namespace Daseraf\Debug\Plugin\Collector;

use Magento\Framework\Profiler;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TimeCollectorPlugin
{
    /**
     * @var \Daseraf\Debug\Model\Profiler\Driver\StopwatchDriver
     */
    private $stopwatchDriver;

    public function __construct(
        \Daseraf\Debug\Model\Profiler\Driver\StopwatchDriver $stopwatchDriver
    ) {
        $this->stopwatchDriver = $stopwatchDriver;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @param \Magento\Framework\App\Http $subject
     */
    public function beforeLaunch(\Magento\Framework\App\Http $subject)
    {
        Profiler::reset();
        Profiler::add($this->stopwatchDriver);
        Profiler::start($this->stopwatchDriver::ROOT_EVENT);
    }
}
