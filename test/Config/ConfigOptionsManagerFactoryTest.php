<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Installer\Config;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Shlinkio\Shlink\Installer\Config\ConfigOptionsManagerFactory;
use Shlinkio\Shlink\Installer\Config\Option\ConfigOptionInterface;

class ConfigOptionsManagerFactoryTest extends TestCase
{
    private ConfigOptionsManagerFactory $factory;
    private MockObject & ContainerInterface $container;

    public function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->factory = new ConfigOptionsManagerFactory();
    }

    #[Test, DataProvider('provideConfigs')]
    public function createsServiceWithExpectedPlugins(callable $configCreator, bool $servicesExist): void
    {
        $config = $configCreator($this);
        $this->container->expects($this->once())->method('get')->with('config')->willReturn($config);

        $manager = ($this->factory)($this->container);

        self::assertEquals($servicesExist, $manager->has('a'));
        self::assertEquals($servicesExist, $manager->has('b'));
        self::assertEquals($servicesExist, $manager->has('c'));
    }

    public static function provideConfigs(): iterable
    {
        yield 'config_options not defined' => [static fn (TestCase $test) => [], false];
        yield 'config_options empty' => [static fn (TestCase $test) => ['config_options' => []], false];
        yield 'config_options with values' => [
            static fn (TestCase $test) => [
                'config_options' => [
                    'services' => [
                        'a' => $test->createMock(ConfigOptionInterface::class),
                        'b' => $test->createMock(ConfigOptionInterface::class),
                        'c' => $test->createMock(ConfigOptionInterface::class),
                    ],
                ],
            ],
            true,
        ];
    }
}
