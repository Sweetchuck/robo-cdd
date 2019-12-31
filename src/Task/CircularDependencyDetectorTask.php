<?php

namespace Sweetchuck\Robo\cdd\Task;

use Robo\Result;
use Robo\Task\BaseTask as RoboBaseTask;
use Robo\TaskInfo;
use Sweetchuck\cdd\CircularDependencyDetector;

class CircularDependencyDetectorTask extends RoboBaseTask
{

    /**
     * {@inheritdoc}
     */
    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * @var string
     */
    protected $taskName = 'Circular Dependency Detector';

    /**
     * @var string
     */
    protected $detectorClass = CircularDependencyDetector::class;

    /**
     * @var array
     */
    protected $assets = [];

    /**
     * @var string
     */
    protected $assetNamePrefix = '';

    public function getAssetNamePrefix(): string
    {
        return $this->assetNamePrefix;
    }

    /**
     * @return $this
     */
    public function setAssetNamePrefix(string $value)
    {
        $this->assetNamePrefix = $value;

        return $this;
    }

    /**
     * @var array
     */
    protected $items = [];

    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return $this
     */
    public function setItems(array $value)
    {
        $this->items = $value;

        return $this;
    }

    /**
     * @var string
     */
    protected $itemLabel = 'Unknown';

    public function getItemLabel(): string
    {
        return $this->itemLabel;
    }

    /**
     * @return $this
     */
    public function setItemLabel(string $value)
    {
        $this->itemLabel = $value;

        return $this;
    }

    /**
     * @var bool
     */
    protected $haltOnError = true;

    public function getHaltOnError(): bool
    {
        return $this->haltOnError;
    }

    /**
     * @return $this
     */
    public function setHaltOnError(bool $value)
    {
        $this->haltOnError = $value;

        return $this;
    }

    /**
     * @return $this
     */
    public function setOptions(array $options)
    {
        if (array_key_exists('assetNamePrefix', $options)) {
            $this->setAssetNamePrefix($options['assetNamePrefix']);
        }

        if (array_key_exists('items', $options)) {
            $this->setItems($options['items']);
        }

        if (array_key_exists('itemLabel', $options)) {
            $this->setItemLabel($options['itemLabel']);
        }

        if (array_key_exists('haltOnError', $options)) {
            $this->setHaltOnError($options['haltOnError']);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        return $this
            ->runHeader()
            ->runDoIt()
            ->runReturn();
    }

    /**
     * @return $this
     */
    protected function runHeader()
    {
        $this->printTaskInfo(
            'Detect dependencies among {itemCount} of {itemLabel} items',
            [
                'itemCount' => count($this->getItems()),
                'itemLabel' => $this->getItemLabel(),
            ]
        );

        return $this;
    }

    /**
     * @return $this
     */
    protected function runDoIt()
    {
        /** @var \Sweetchuck\cdd\CircularDependencyDetectorInterface $detector */
        $detector = new $this->detectorClass();
        $this->assets['circularDependencies'] = $detector->detect($this->getItems());

        return $this;
    }

    protected function runReturn(): Result
    {
        return new Result(
            $this,
            $this->getTaskResultCode(),
            $this->getTaskResultMessage(),
            $this->getAssetsWithPrefixedNames()
        );
    }

    protected function getTaskResultCode(): int
    {
        return $this->assets['circularDependencies'] && $this->getHaltOnError() ? 1 : 0;
    }

    protected function getTaskResultMessage(): string
    {
        if (!$this->assets['circularDependencies']) {
            return '';
        }

        $lines = [''];
        $indent = 4;
        foreach ($this->assets['circularDependencies'] as $chain) {
            $currentIndent = 0;
            foreach ($chain as $itemId) {
                $lines[] = str_repeat(' ', $currentIndent) . $itemId;
                $currentIndent += $indent;
            }

            $lines[] = '';
        }

        array_pop($lines);

        return implode(PHP_EOL, $lines);
    }

    protected function getAssetsWithPrefixedNames(): array
    {
        $prefix = $this->getAssetNamePrefix();
        if (!$prefix) {
            return $this->assets;
        }

        $assets = [];
        foreach ($this->assets as $key => $value) {
            $assets["{$prefix}{$key}"] = $value;
        }

        return $assets;
    }

    /**
     * {@inheritdoc}
     */
    protected function getTaskContext($context = null)
    {
        if (empty($context['name'])) {
            $context['name'] = $this->getTaskName();
        }

        return parent::getTaskContext($context);
    }

    public function getTaskName(): string
    {
        return $this->taskName ?: TaskInfo::formatTaskName($this);
    }
}
