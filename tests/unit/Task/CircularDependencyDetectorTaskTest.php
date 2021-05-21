<?php

declare(strict_types = 1);

namespace Sweetchuck\Robo\cdd\Tests\Unit\Task;

use Codeception\Test\Unit;
use League\Container\Container as LeagueContainer;
use Psr\Container\ContainerInterface;
use Robo\Collection\CollectionBuilder;
use Robo\Config\Config as RoboConfig;
use Robo\Robo;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyOutput;
use Sweetchuck\Codeception\Module\RoboTaskRunner\DummyProcessHelper;
use Sweetchuck\Robo\cdd\Test\Helper\Dummy\DummyTaskBuilder;
use Sweetchuck\Robo\cdd\Test\UnitTester;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\BufferingLogger;

class CircularDependencyDetectorTaskTest extends Unit
{
    protected UnitTester $tester;

    protected ContainerInterface $container;

    protected RoboConfig $config;

    protected CollectionBuilder $builder;

    protected DummyTaskBuilder $taskBuilder;

    /**
     * @SuppressWarnings("CamelCaseMethodName")
     */
    public function _before()
    {
        parent::_before();

        Robo::unsetContainer();

        $this->container = new LeagueContainer();
        $application = new SymfonyApplication('Sweetchuck - Robo Git', '1.0.0');
        $application->getHelperSet()->set(new DummyProcessHelper(), 'process');
        $this->config = (new RoboConfig());
        $input = null;
        $output = new DummyOutput([
            'verbosity' => DummyOutput::VERBOSITY_DEBUG,
        ]);

        $this->container->add('container', $this->container);

        Robo::configureContainer($this->container, $application, $this->config, $input, $output);
        $this->container->share('logger', BufferingLogger::class);

        $this->builder = CollectionBuilder::create($this->container, null);
        $this->taskBuilder = new DummyTaskBuilder();
        $this->taskBuilder->setContainer($this->container);
        $this->taskBuilder->setBuilder($this->builder);
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
        $taskBuilder = new DummyTaskBuilder();
        $taskBuilder->setContainer($this->getNewContainer());
        $task = $taskBuilder->taskCircularDependencyDetector($options);
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

        $container = Robo::createContainer();
        $container->add('output', $output, false);

        return $container;
    }
}
