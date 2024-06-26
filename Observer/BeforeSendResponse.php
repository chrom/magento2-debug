<?php

namespace Daseraf\Debug\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class BeforeSendResponse implements ObserverInterface
{
    /**
     * @var \Daseraf\Debug\Helper\Config
     */
    private $config;

    /**
     * @var \Daseraf\Debug\Model\Profiler
     */
    private $profiler;

    public function __construct(
        \Daseraf\Debug\Helper\Config $config,
        \Daseraf\Debug\Model\Profiler $profiler
    ) {
        $this->config = $config;
        $this->profiler = $profiler;
    }

    public function execute(Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $response = $observer->getEvent()->getResponse();
        if ($this->isProfilerAction($request) || !$this->config->isEnabled()) {
            return;
        }

        $this->profiler->run($request, $response);
    }

    private function isProfilerAction(\Magento\Framework\HTTP\PhpEnvironment\Request $request)
    {
        if (preg_match('/\/debug\/profiler*/s', $request->getPathInfo())) {
            return true;
        }
        if (preg_match('/\/debug\/xhprof*/s', $request->getPathInfo())) {
            return true;
        }

        return $request->getModuleName() === 'debug';
    }
}
