<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\cdd\Tests\Acceptance\Task;

use Codeception\Attribute\DataProvider;
use Codeception\Example;
use Sweetchuck\Robo\cdd\Tests\AcceptanceTester;
use Sweetchuck\Robo\cdd\Tests\Helper\RoboFiles\CircularDependencyRoboFile;

class CircularDependencyDetectorTaskCest
{

    public static function detectCases(): array
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

    #[DataProvider('detectCases')]
    public function detect(AcceptanceTester $tester, Example $example): void
    {
        $tester->runRoboTask($example['id'], CircularDependencyRoboFile::class, ...$example['cli']);
        $exitCode = $tester->getRoboTaskExitCode($example['id']);
        $stdOutput = $tester->getRoboTaskStdOutput($example['id']);
        $stdError = $tester->getRoboTaskStdError($example['id']);

        $tester->assertSame($example['expectedExitCode'], $exitCode);
        $tester->assertSame($example['expectedStdOutput'], $stdOutput);
        $tester->assertSame($example['expectedStdError'], $stdError);
    }
}
