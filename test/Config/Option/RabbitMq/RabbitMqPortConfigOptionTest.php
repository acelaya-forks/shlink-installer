<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Installer\Config\Option\RabbitMq;

use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Shlinkio\Shlink\Installer\Config\Option\RabbitMq\RabbitMqPortConfigOption;
use Symfony\Component\Console\Style\StyleInterface;

class RabbitMqPortConfigOptionTest extends TestCase
{
    use ProphecyTrait;

    private RabbitMqPortConfigOption $configOption;

    public function setUp(): void
    {
        $this->configOption = new RabbitMqPortConfigOption(fn () => true);
    }

    /** @test */
    public function returnsExpectedEnvVar(): void
    {
        self::assertEquals('RABBITMQ_PORT', $this->configOption->getEnvVar());
    }

    /** @test */
    public function expectedQuestionIsAsked(): void
    {
        $io = $this->prophesize(StyleInterface::class);
        $ask = $io->ask('RabbitMQ port', '5672', Argument::any())->willReturn('5672');

        $answer = $this->configOption->ask($io->reveal(), []);

        self::assertEquals(5672, $answer);
        $ask->shouldHaveBeenCalledOnce();
    }
}