<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\cdd;

use Robo\Collection\CollectionBuilder;

trait CircularDependencyTaskLoader
{
    /**
     * @return \Sweetchuck\Robo\cdd\Task\CircularDependencyDetectorTask|\Robo\Collection\CollectionBuilder
     */
    protected function taskCircularDependencyDetector(array $options = []): CollectionBuilder
    {
        /** @var \Sweetchuck\Robo\cdd\Task\CircularDependencyDetectorTask|\Robo\Collection\CollectionBuilder $task */
        $task = $this->task(Task\CircularDependencyDetectorTask::class);
        $task->setOptions($options);

        return $task;
    }
}
