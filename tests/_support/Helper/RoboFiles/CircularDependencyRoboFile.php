<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\cdd\Test\Helper\RoboFiles;

use Robo\Tasks;
use Sweetchuck\Robo\cdd\CircularDependencyTaskLoader;
use Robo\Contract\TaskInterface;

class CircularDependencyRoboFile extends Tasks
{
    use CircularDependencyTaskLoader;

    protected $items = [
        'success' => [
            'a' => ['b'],
            'b' => [],
        ],
        'fail' => [
            'a' => ['b'],
            'b' => ['a'],
        ],
    ];

    public function detect(string $itemSetName): TaskInterface
    {
        return $this
            ->taskCircularDependencyDetector()
            ->setItemLabel('Packages')
            ->setItems($this->items[$itemSetName]);
    }
}
