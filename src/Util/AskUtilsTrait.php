<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Installer\Util;

use Shlinkio\Shlink\Installer\Exception\MissingRequiredOptionException;
use Symfony\Component\Console\Style\StyleInterface;

trait AskUtilsTrait
{
    private function askRequired(StyleInterface $io, string $optionName, string|null $question = null): string
    {
        return $io->ask($question ?? $optionName, null, static function ($value) use ($optionName) {
            if (empty($value)) {
                throw MissingRequiredOptionException::fromOption($optionName);
            }

            return $value;
        });
    }
}
