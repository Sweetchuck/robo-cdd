<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\cdd\Task;

use Robo\Result;
use Robo\Task\BaseTask as RoboBaseTask;
use Robo\TaskInfo;
use Sweetchuck\cdd\CircularDependencyDetector;

class CircularDependencyDetectorTask extends RoboBaseTask
{

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    protected string $taskName = 'Circular Dependency Detector';

    protected string $detectorClass = CircularDependencyDetector::class;

    protected array $assets = [];

    protected string $assetNamePrefix = '';

    public function getAssetNamePrefix(): string
    {
        return $this->assetNamePrefix;
    }

    public function setAssetNamePrefix(string $value): static
    {
        $this->assetNamePrefix = $value;

        return $this;
    }

    protected array $items = [];

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $value): static
    {
        $this->items = $value;

        return $this;
    }

    protected string $itemLabel = 'Unknown';

    public function getItemLabel(): string
    {
        return $this->itemLabel;
    }

    public function setItemLabel(string $value): static
    {
        $this->itemLabel = $value;

        return $this;
    }

    protected bool $haltOnError = true;

    public function getHaltOnError(): bool
    {
        return $this->haltOnError;
    }

    public function setHaltOnError(bool $value): static
    {
        $this->haltOnError = $value;

        return $this;
    }

    public function setOptions(array $options): static
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

    protected function runHeader(): static
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

    protected function runDoIt(): static
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
