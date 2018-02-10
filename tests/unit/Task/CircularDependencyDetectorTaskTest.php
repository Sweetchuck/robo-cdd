<?php

namespace Sweetchuck\Robo\cdd\Tests\Unit\Task;

use Codeception\Test\Unit;
use League\Container\ContainerInterface;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Robo\cdd\Task\CircularDependencyDetectorTask;
use Symfony\Component\Console\Output\OutputInterface;

class CircularDependencyDetectorTaskTest extends Unit
{
    /**
     * @var \Sweetchuck\Robo\cdd\Test\UnitTester
     */
    protected $tester;

    /**
     * @var \Sweetchuck\Robo\cdd\Task\CircularDependencyDetectorTask|\Robo\Collection\CollectionBuilder
     */
    protected $task;

    /**
     * @var null|\League\Container\ContainerInterface
     */
    protected $containerBackup;

    /**
     * {@inheritdoc}
     */
    // @codingStandardsIgnoreLine
    public function _before()
    {
        parent::_before();

        $this->containerBackup = Robo::hasContainer() ? Robo::getContainer() : null;
        if ($this->containerBackup) {
            Robo::unsetContainer();
        }
    }

    /**
     * {@inheritdoc}
     *
     */
    // @codingStandardsIgnoreLine
    protected function _after()
    {
        if ($this->containerBackup) {
            Robo::setContainer($this->containerBackup);
        } else {
            Robo::unsetContainer();
        }

        $this->containerBackup = null;

        parent::_after();
    }

    public function casesRun(): array
    {
        return [
            'success' => [
                [
                    'exitCode' => 0,
                    'exitMessage' => '',
                ],
                [
                    'a' => ['b'],
                    'b' => ['c'],
                    'c' => [],
                ],
            ],
            'fail 1' => [
                [
                    'exitCode' => 1,
                    'exitMessage' => implode(PHP_EOL, [
                        '',
                        'c',
                        '    a',
                        '        b',
                        '            c',
                    ]),
                ],
                [
                    'items' => [
                        'a' => ['b'],
                        'b' => ['c'],
                        'c' => ['a'],
                    ],
                ],
            ],
            'fail 0' => [
                [
                    'exitCode' => 0,
                    'exitMessage' => implode(PHP_EOL, [
                        '',
                        'c',
                        '    a',
                        '        b',
                        '            c',
                    ]),
                    'assets' => [
                        'myPrefix.circularDependencies' => [
                            'a|b|c' => ['c', 'a', 'b', 'c'],
                        ],
                    ],
                ],
                [
                    'items' => [
                        'a' => ['b'],
                        'b' => ['c'],
                        'c' => ['a'],
                    ],
                    'itemLabel' => 'Package',
                    'assetNamePrefix' => 'myPrefix.',
                    'haltOnError' => false,
                ],
            ],
        ];
    }

    /**
     * @dataProvider casesRun
     */
    public function testRun(array $expected, array $options)
    {
        $this->getNewContainer();
        $task = new CircularDependencyDetectorTask();
        $task->setOptions($options);

        $result = $task->run();

        $this->tester->assertEquals($expected['exitCode'], $result->getExitCode());
        $this->tester->assertEquals($expected['exitMessage'], $result->getMessage());
        if (!empty($expected['assets'])) {
            foreach ($expected['assets'] as $assetName => $asset) {
                $this->tester->assertTrue(isset($result[$assetName]));
                $this->tester->assertEquals($asset, $result[$assetName]);
            }
        }
    }

    protected function getNewContainer(): ContainerInterface
    {
        $config = [
            'verbosity' => OutputInterface::VERBOSITY_DEBUG,
            'colors' => false,
        ];
        $output = new DummyOutput($config);

        $container = Robo::createDefaultContainer(null, $output);
        $container->add('output', $output, false);

        return $container;
    }
}
