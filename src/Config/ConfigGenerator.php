<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Installer\Config;

use Shlinkio\Shlink\Config\Collection\PathCollection;
use Shlinkio\Shlink\Installer\Config\Option\ConfigOptionInterface;
use Shlinkio\Shlink\Installer\Config\Option\DependentConfigOptionInterface;
use Symfony\Component\Console\Style\StyleInterface;

use function array_combine;
use function Functional\compose;
use function Functional\contains;
use function Functional\map;
use function Functional\select;
use function Functional\sort;
use function get_class;

class ConfigGenerator implements ConfigGeneratorInterface
{
    private ConfigOptionsManagerInterface $configOptionsManager;
    private array $configOptionsGroups;
    private ?array $enabledOptions;

    public function __construct(
        ConfigOptionsManagerInterface $configOptionsManager,
        array $configOptionsGroups,
        ?array $enabledOptions
    ) {
        $this->configOptionsManager = $configOptionsManager;
        $this->configOptionsGroups = $configOptionsGroups;
        $this->enabledOptions = $enabledOptions;
    }

    public function generateConfigInteractively(StyleInterface $io, array $previousConfig): PathCollection
    {
        $pluginsGroups = $this->resolveAndSortOptions();
        $answers = new PathCollection($previousConfig);
        $alreadyRenderedTitles = [];

        // FIXME Improve code quality on these nested loops
        foreach ($pluginsGroups as $title => $configOptions) {
            /** @var ConfigOptionInterface $plugin */
            foreach ($configOptions as $configOption => $plugin) {
                $optionIsEnabled = $this->enabledOptions === null || contains($this->enabledOptions, $configOption);
                $shouldAsk = $optionIsEnabled && $plugin->shouldBeAsked($answers);
                if (! $shouldAsk) {
                    continue;
                }

                // Render every title only once, and only as soon as we find a plugin that should be asked
                if (! contains($alreadyRenderedTitles, $title)) {
                    $alreadyRenderedTitles[] = $title;
                    $io->title($title);
                }

                $answers->setValueInPath($plugin->ask($io, $answers), $plugin->getConfigPath());
            }
        }

        return $answers;
    }

    /**
     * @return ConfigOptionInterface[][]
     */
    private function resolveAndSortOptions(): array
    {
        $dependentPluginSorter = static function (ConfigOptionInterface $left, ConfigOptionInterface $right): int {
            if ($left instanceof DependentConfigOptionInterface) {
                return $left->getDependentOption() === get_class($right) ? 1 : 0;
            }

            return 0;
        };
        $sortAndResolvePlugins = fn (array $configOptions) => array_combine(
            $configOptions,
            // Resolve all plugins for every config option, and then sort them
            sort(
                map(
                    $configOptions,
                    fn (string $configOption) => $this->configOptionsManager->get($configOption),
                ),
                $dependentPluginSorter,
            ),
        );
        $filterDisabledOptions = fn (array $configOptions) => select(
            $configOptions,
            fn (string $option) => $this->enabledOptions === null || contains($this->enabledOptions, $option),
        );

        return map($this->configOptionsGroups, compose($filterDisabledOptions, $sortAndResolvePlugins));
    }
}
