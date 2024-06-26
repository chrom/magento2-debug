<?php

namespace Daseraf\Debug\Model\ValueObject;

use Daseraf\Debug\Logger\LoggableInterface;
use Daseraf\Debug\Model\Collector\LayoutCollector;

class Block implements LoggableInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $module;

    /**
     * @var float
     */
    private $renderTime;

    /**
     * @var string
     */
    private $template;

    /**
     * @var array
     */
    private $children;

    /**
     * @var string
     */
    private $parentId;
    private $cacheKey;

    public function __construct(\Magento\Framework\View\Element\AbstractBlock $block)
    {
        $this->id = $block->getData(LayoutCollector::BLOCK_PROFILER_ID_KEY);
        $this->name = $block->getNameInLayout();
        $this->class = get_class($block);
        $this->module = $block->getModuleName();
        $this->renderTime = $block->getData(LayoutCollector::RENDER_TIME);
        $this->template = $block->getTemplate();
        $this->children = $block->getChildNames();
        $this->parentId = $block->getParentBlock()
            ? $block->getParentBlock()->getData(LayoutCollector::BLOCK_PROFILER_ID_KEY)
            : '';
        $this->cacheKey = ($block->getTemplate() && $block->getData('cache_lifetime')) ? $block->getCacheKey() : null;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return (string) $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return (string) $this->name;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return (string) $this->class;
    }

    /**
     * @return string
     */
    public function getModule(): string
    {
        return (string) $this->module;
    }

    /**
     * @return float
     */
    public function getRenderTime(): float
    {
        return (float) $this->renderTime;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return (string) $this->template;
    }

    /**
     * @return array
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @return string
     */
    public function getParentId(): string
    {
        return (string) $this->parentId;
    }

    /**
     * @return string
     */
    public function getCacheKey(): string
    {
        return (string) $this->cacheKey;
    }
}
