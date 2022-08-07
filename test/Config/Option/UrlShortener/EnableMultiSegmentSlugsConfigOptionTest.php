<?php

declare(strict_types=1);

namespace ShlinkioTest\Shlink\Installer\Config\Option\UrlShortener;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Shlinkio\Shlink\Installer\Config\Option\UrlShortener\EnableMultiSegmentSlugsConfigOption;
use Symfony\Component\Console\Style\StyleInterface;

class EnableMultiSegmentSlugsConfigOptionTest extends TestCase
{
    use ProphecyTrait;

    private EnableMultiSegmentSlugsConfigOption $configOption;

    public function setUp(): void
    {
        $this->configOption = new EnableMultiSegmentSlugsConfigOption();
    }

    /** @test */
    public function returnsExpectedEnvVar(): void
    {
        self::assertEquals('MULTI_SEGMENT_SLUGS_ENABLED', $this->configOption->getEnvVar());
    }

    /**
     * @test
     * @dataProvider provideAnswers
     */
    public function expectedQuestionIsAsked(string $providedAnswer, bool $expected): void
    {
        $io = $this->prophesize(StyleInterface::class);
        $choice = $io->choice(
            'Do you want to support short URLs with multi-segment custom slugs? '
            . '(for example, https://example.com/foo/bar)',
            [
                'yes' => 'Custom slugs will support multiple segments (https://example.com/foo/bar/baz). Orphan '
                    . 'visits will only have either "base_url" or "invalid_short_url" type.',
                'no' => 'Slugs and short codes will support only one segment (https://example.com/foo). Orphan '
                    . 'visits will have one of "base_url", "invalid_short_url" or "regular_404" type.',
            ],
            'no',
        )->willReturn($providedAnswer);

        $answer = $this->configOption->ask($io->reveal(), []);

        self::assertEquals($expected, $answer);
        $choice->shouldHaveBeenCalledOnce();
    }

    public function provideAnswers(): iterable
    {
        yield 'yes' => ['yes', true];
        yield 'no' => ['no', false];
    }
}