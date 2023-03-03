<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\cdd\Tests\Acceptance\Task;

use Codeception\Example;
use Sweetchuck\Robo\cdd\Test\AcceptanceTester;
use Sweetchuck\Robo\cdd\Test\Helper\RoboFiles\CircularDependencyRoboFile;

class CircularDependencyDetectorTaskCest
{

    protected function detectCases(): array
    {
        return [
            [
                'id' => 'detect success',
                'expectedExitCode' => 0,
                'expectedStdOutput' => '',
                'expectedStdError' => implode(PHP_EOL, [
                    ' [Circular Dependency Detector] Detect dependencies among 2 of Packages items',
                    ''
                ]),
                'cli' => ['detect', 'success'],
            ],
            [
                'id' => 'detect fail',
                'expectedExitCode' => 1,
                'expectedStdOutput' => '',
                'expectedStdError' => implode(PHP_EOL, [
                    ' [Circular Dependency Detector] Detect dependencies among 2 of Packages items',
                    ' [Sweetchuck\\Robo\\cdd\\Task\\CircularDependencyDetectorTask]  ',
                    'b',
                    '    a',
                    '        b ',
                    ' [Sweetchuck\Robo\cdd\Task\CircularDependencyDetectorTask]  Exit code 1 ',
                    ''
                ]),
                'cli' => ['detect', 'fail'],
            ],
        ];
    }

    /**
     * @dataProvider detectCases
     */
    public function detect(AcceptanceTester $tester, Example $example): void
    {
        $tester->runRoboTask($example['id'], CircularDependencyRoboFile::class, ...$example['cli']);
        $exitCode = $tester->getRoboTaskExitCode($example['id']);
        $stdOutput = $tester->getRoboTaskStdOutput($example['id']);
        $stdError = $tester->getRoboTaskStdError($example['id']);

        $tester->assertEquals($example['expectedExitCode'], $exitCode);
        $tester->assertEquals($example['expectedStdOutput'], $stdOutput);
        $tester->assertEquals($example['expectedStdError'], $stdError);
    }
}
