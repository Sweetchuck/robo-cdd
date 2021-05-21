# Robo - Circular Dependency Detector

[![CircleCI](https://circleci.com/gh/Sweetchuck/robo-cdd/tree/2.x.svg?style=svg)](https://circleci.com/gh/Sweetchuck/robo-cdd/?branch=2.x)
[![codecov](https://codecov.io/gh/Sweetchuck/robo-cdd/branch/2.x/graph/badge.svg?token=Y6GIX9ovAG)](https://app.codecov.io/gh/Sweetchuck/robo-cdd/branch/2.x)


@todo


## Install

    composer require --dev sweetchuck/robo-cdd


## Usage

**RoboFile.php**
```php
<?php

use Robo\Collection\CollectionBuilder;
use Robo\State\Data as RoboStateData;
use Sweetchuck\Robo\cdd\CircularDependencyTaskLoader;

class RoboFile extends \Robo\Tasks
{
    use CircularDependencyTaskLoader;

    /**
     * @command validate:module-dependencies
     */
    public function validateModuleDependencies(): CollectionBuilder
    {
        return $this
            ->collectionBuilder()
            ->addCode(function (RoboStateData $data): int {
                $data['moduleDependencies'] = $this->collectModuleDependencies();

                return 0;
            })
            ->addTask(
                $this
                    ->taskCircularDependencyDetector()
                    ->setItemLabel('module')
                    ->deferTaskConfiguration('setItems', 'moduleDependencies')
            );
    }

    protected function collectModuleDependencies(): array
    {
        return [
            'a' => ['b'],
            'b' => ['c'],
            'c' => ['a'],
            'd' => ['e'],
            'e' => ['d'],
        ];
    }
}
```

**CLI command**

`vendor/bin/robo validate:module-dependencies`

**Output is something like this:**
```
 [Circular Dependency Detector] Detect dependencies among 5 of module items
 [Sweetchuck\Robo\cdd\Task\CircularDependencyDetectorTask]
c
    a
        b
            c

e
    d
        e
 [Sweetchuck\Robo\cdd\Task\CircularDependencyDetectorTask]  Exit code 1
```
