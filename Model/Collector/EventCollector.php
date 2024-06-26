<?php

namespace Daseraf\Debug\Model\Collector;

use Daseraf\Debug\Logger\LoggableInterface;

class EventCollector implements CollectorInterface, LateCollectorInterface, LoggerCollectorInterface
{
    public const NAME = 'event';

    public const TIME = 'time';
    public const EVENTS = 'events';
    public const OBSERVERS = 'observers';

    public const OBSERVERS_COUNT = 'observers_count';
    public const DISPATCH_COUNT = 'events_count';

    /**
     * @var \Daseraf\Debug\Helper\Config
     */
    private $config;

    /**
     * @var \Daseraf\Debug\Model\DataCollector
     */
    private $dataCollector;

    /**
     * @var \Daseraf\Debug\Logger\DataLogger
     */
    private $dataLogger;

    /**
     * @var \Daseraf\Debug\Helper\Formatter
     */
    private $formatter;

    /**
     * @var \Daseraf\Debug\Helper\Debug
     */
    private $debug;

    public function __construct(
        \Daseraf\Debug\Helper\Config $config,
        \Daseraf\Debug\Model\DataCollectorFactory $dataCollectorFactory,
        \Daseraf\Debug\Logger\DataLoggerFactory $dataLoggerFactory,
        \Daseraf\Debug\Helper\Formatter $formatter,
        \Daseraf\Debug\Helper\Debug $debug
    ) {
        $this->config = $config;
        $this->dataCollector = $dataCollectorFactory->create();
        $this->dataLogger = $dataLoggerFactory->create();
        $this->formatter = $formatter;
        $this->debug = $debug;
    }

    public function collect(): CollectorInterface
    {
        $this->dataCollector->setData([
            self::TIME => 0,
            self::EVENTS => [],
            self::OBSERVERS => [],
        ]);

        return $this;
    }

    public function lateCollect(): LateCollectorInterface
    {
        $time = $this->dataCollector->getData(self::TIME);
        $observers = $this->dataCollector->getData(self::OBSERVERS);
        $events = $this->dataCollector->getData(self::EVENTS);

        /** @var \Daseraf\Debug\Model\ValueObject\EventObserver $observer */
        foreach ($this->dataLogger->getLogs() as $observer) {
            $time += $observer->getTime();
            if (!isset($events[$observer->getEvent()])) {
                $events[$observer->getEvent()] = [];
            }
            $events[$observer->getEvent()][] = $observer;
            $observers[] = $observer;
        }

        $this->dataCollector->setData([
            self::TIME => $time,
            self::EVENTS => $events,
            self::OBSERVERS => $observers,
        ]);

        return $this;
    }

    public function getTime()
    {
        return $this->formatter->microtime($this->dataCollector->getData(self::TIME) ?? 0);
    }

    public function getEvents()
    {
        return $this->dataCollector->getData(self::EVENTS) ?? [];
    }

    public function getObserversCount()
    {
        return array_sum(array_map('count', $this->getEvents()));
    }

    public function filterObservers(array $observers): array
    {
        $filtered = [];
        foreach ($observers as $observer) {
            $filtered[$observer->getClass()] = $observer;
        }

        return $filtered;
    }

    public function getEventTime(array $observers): string
    {
        $time = 0;
        /** @var \Daseraf\Debug\Model\ValueObject\EventObserver $observer */
        foreach ($observers as $observer) {
            $time += $observer->getTime();
        }

        return $this->formatter->microtime($time);
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isEventCollectorEnabled();
    }

    public function getData(): array
    {
        return $this->dataCollector->getData();
    }

    public function setData(array $data): CollectorInterface
    {
        $this->dataCollector->setData($data);

        return $this;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getStatus(): string
    {
        return self::STATUS_DEFAULT;
    }

    public function log(LoggableInterface $value): LoggerCollectorInterface
    {
        /** @var \Daseraf\Debug\Model\ValueObject\EventObserver $value */
        if ($this->debug->isDebugClass($value->getClass())) {
            return $this;
        }
        $this->dataLogger->log($value);

        return $this;
    }
}
