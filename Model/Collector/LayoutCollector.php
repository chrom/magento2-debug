<?php

namespace Daseraf\Debug\Model\Collector;

use Daseraf\Debug\Logger\LoggableInterface;

class LayoutCollector implements CollectorInterface, LoggerCollectorInterface
{
    public const NAME = 'layout';

    public const BLOCK_PROFILER_ID_KEY = 'debug_profiler_id';
    public const BLOCK_START_RENDER_KEY = 'debug_start_render';
    public const BLOCK_STOP_RENDER_KEY = 'debug_stop_render';
    public const BLOCK_RENDER_TIME_KEY = 'debug_render_time';
    public const BLOCK_HASH_KEY = 'debug_hash';
    public const BLOCK_PARENT_PROFILER_ID_KEY = 'debug_profiler_parent_id';

    public const HANDLES = 'handles';
    public const BLOCKS = 'blocks';
    public const BLOCKS_CREATED = 'blocks_created';
    public const BLOCKS_RENDERED = 'blocks_rendered';
    public const BLOCKS_NOT_RENDERED = 'blocks_not_rendered';
    public const TOTAL_RENDER_TIME = 'total_render_time';
    public const RENDER_TIME = 'render_time';

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
     * @var \Daseraf\Debug\Model\Info\LayoutInfo
     */
    private $layoutInfo;

    /**
     * @var \Daseraf\Debug\Helper\Formatter
     */
    private $formatter;

    public function __construct(
        \Daseraf\Debug\Helper\Config $config,
        \Daseraf\Debug\Model\DataCollectorFactory $dataCollectorFactory,
        \Daseraf\Debug\Logger\DataLoggerFactory $dataLogger,
        \Daseraf\Debug\Model\Info\LayoutInfo $layoutInfo,
        \Daseraf\Debug\Helper\Formatter $formatter
    ) {
        $this->config = $config;
        $this->dataCollector = $dataCollectorFactory->create();
        $this->dataLogger = $dataLogger->create();
        $this->layoutInfo = $layoutInfo;
        $this->formatter = $formatter;
    }

    public function collect(): CollectorInterface
    {
        $renderTime = 0;

        /** @var \Daseraf\Debug\Model\ValueObject\Block $block */
        foreach ($this->dataLogger->getLogs() as $block) {
            $renderTime += $block->getRenderTime();
        }

        $this->dataCollector->setData([
            self::TOTAL_RENDER_TIME => $renderTime,
            self::HANDLES => $this->layoutInfo->getHandles(),
            self::BLOCKS_CREATED => $this->layoutInfo->getCreatedBlocks(),
            self::BLOCKS_RENDERED => $this->dataLogger->getLogs(),
            self::BLOCKS_NOT_RENDERED => $this->layoutInfo->getNotRenderedBlocks(),
        ]);

        return $this;
    }

    public function getRenderTime(): string
    {
        return $this->formatter->microtime($this->dataCollector->getData(self::TOTAL_RENDER_TIME));
    }

    public function getHandles(): array
    {
        return $this->dataCollector->getData(self::HANDLES) ?? [];
    }

    public function getCreatedBlocks(): array
    {
        return $this->dataCollector->getData(self::BLOCKS_CREATED) ?? [];
    }

    public function getRenderedBlocks(): array
    {
        $blocks = $this->dataCollector->getData(self::BLOCKS_RENDERED) ?? [];

        uasort($blocks, [$this, 'sortByTime']);

        return $blocks;
    }

    protected function sortByTime($a, $b)
    {
        $a = $a->getRenderTime();
        $b = $b->getRenderTime();
        if ($a == $b) {
            return 0;
        }
        return ($a > $b) ? -1 : 1;
    }
    public function getNotRenderedBlocks(): array
    {
        return $this->dataCollector->getData(self::BLOCKS_NOT_RENDERED) ?? [];
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->config->isLayoutCollectorEnabled();
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
        if (!empty($this->getNotRenderedBlocks())) {
            return self::STATUS_WARNING;
        }

        return self::STATUS_DEFAULT;
    }

    public function log(LoggableInterface $value): LoggerCollectorInterface
    {
        $this->dataLogger->log($value);

        return $this;
    }
}
