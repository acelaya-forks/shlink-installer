<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Installer\Config\Option\Tracking;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Shlinkio\Shlink\Installer\Config\Option\Tracking\DisableTrackingFromConfigOption;
use Symfony\Component\Console\Style\StyleInterface;

class DisableTrackingFromConfigOptionTest extends TestCase
{
    use ProphecyTrait;

    private DisableTrackingFromConfigOption $configOption;

    public function setUp(): void
    {
        $this->configOption = new DisableTrackingFromConfigOption();
    }

    /** @test */
    public function returnsExpectedEnvVar(): void
    {
        self::assertEquals('DISABLE_TRACKING_FROM', $this->configOption->getEnvVar());
    }

    /**
     * @test
     * @dataProvider provideAnswers
     */
    public function expectedQuestionIsAsked(?string $answer): void
    {
        $io = $this->prophesize(StyleInterface::class);
        $ask = $io->ask(
            'Provide a comma-separated list of IP addresses, CIDR blocks or wildcard addresses (1.2.*.*) from '
            . 'which you want tracking to be disabled',
        )->willReturn($answer);

        $result = $this->configOption->ask($io->reveal(), []);

        self::assertEquals($answer, $result);
        $ask->shouldHaveBeenCalledOnce();
    }

    public function provideAnswers(): iterable
    {
        yield 'no addresses' => [null];
        yield 'some addresses' => ['192.168.1.1,192.168.0.0/24'];
    }
}