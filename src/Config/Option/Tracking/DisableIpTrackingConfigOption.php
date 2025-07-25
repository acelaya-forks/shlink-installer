<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Installer\Config\Option\Tracking;

use Symfony\Component\Console\Style\StyleInterface;

class DisableIpTrackingConfigOption extends AbstractDisableTrackingDependentConfigOption
{
    public const string ENV_VAR = 'DISABLE_IP_TRACKING';

    public function getEnvVar(): string
    {
        return self::ENV_VAR;
    }

    public function ask(StyleInterface $io, array $currentOptions): bool
    {
        return $io->confirm('Do you want to disable tracking of visitors\' IP addresses?', false);
    }
}
