<?php

namespace Daseraf\Debug\Plugin\Collector;

use Daseraf\Debug\Model\ValueObject\ModelAction;
use Magento\Framework\Model\ResourceModel\AbstractResource;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class ModelCollectorPlugin
{
    /**
     * @var \Daseraf\Debug\Model\Collector\ModelCollector
     */
    private $modelCollector;

    /**
     * @var \Daseraf\Debug\Helper\Formatter
     */
    private $formatter;

    /**
     * @var \Daseraf\Debug\Helper\Debug
     */
    private $debug;

    public function __construct(
        \Daseraf\Debug\Model\Collector\ModelCollector $modelCollector,
        \Daseraf\Debug\Helper\Formatter $formatter,
        \Daseraf\Debug\Helper\Debug $debug
    ) {
        $this->modelCollector = $modelCollector;
        $this->formatter = $formatter;
        $this->debug = $debug;
    }

    public function aroundLoad(AbstractResource $subject, callable $proceed, $object, $value, $field = null)
    {
        $time = microtime(true);
        $result = $proceed($object, $value, $field);
        $trace = $this->debug->getBacktrace([
            ModelAction::LOAD,
            ModelAction::SAVE,
            ModelAction::DELETE,
        ], DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->modelCollector->log(new ModelAction(
            ModelAction::LOAD,
            get_class($object),
            $this->formatter->microtime(microtime(true) - $time),
            $trace
        ));

        return $result;
    }

    public function aroundSave(AbstractResource $subject, callable $proceed, $object)
    {
        $time = microtime(true);
        $result = $proceed($object);
        $trace = $this->debug->getBacktrace([
            ModelAction::LOAD,
            ModelAction::SAVE,
            ModelAction::DELETE,
        ], DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->modelCollector->log(new ModelAction(
            ModelAction::SAVE,
            get_class($object),
            $this->formatter->microtime(microtime(true) - $time),
            $trace
        ));

        return $result;
    }

    public function aroundDelete(AbstractResource $subject, callable $proceed, $object)
    {
        $time = microtime(true);
        $result = $proceed($object);
        $trace = $this->debug->getBacktrace([
            ModelAction::LOAD,
            ModelAction::SAVE,
            ModelAction::DELETE,
        ], DEBUG_BACKTRACE_IGNORE_ARGS);

        $this->modelCollector->log(new ModelAction(
            ModelAction::DELETE,
            get_class($object),
            $this->formatter->microtime(microtime(true) - $time),
            $trace
        ));

        return $result;
    }
}
