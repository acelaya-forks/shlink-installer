<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Installer\Config\Option\Database;

use Shlinkio\Shlink\Config\Collection\PathCollection;
use Symfony\Component\Console\Style\StyleInterface;

class DatabaseSqlitePathConfigOption extends AbstractDriverDependentConfigOption
{
    public function getConfigPath(): array
    {
        return ['entity_manager', 'connection', 'path'];
    }

    public function ask(StyleInterface $io, PathCollection $currentOptions): string
    {
        return 'data/database.sqlite';
    }

    protected function shouldBeAskedForDbDriver(string $dbDriver): bool
    {
        return $dbDriver === DatabaseDriverConfigOption::SQLITE_DRIVER;
    }
}
