<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Installer\Service;

use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\PhpExecutableFinder;

use function explode;
use function implode;
use function sprintf;

class InstallationCommandsRunner implements InstallationCommandsRunnerInterface
{
    private ProcessHelper $processHelper;
    private array $commandsMapping;
    private string $phpBinary;

    public function __construct(ProcessHelper $processHelper, PhpExecutableFinder $phpFinder, array $commandsMapping)
    {
        $this->processHelper = $processHelper;
        $this->commandsMapping = $commandsMapping;
        $this->phpBinary = $phpFinder->find(false) ?: 'php';
    }

    public function execPhpCommand(string $name, SymfonyStyle $io): bool
    {
        $commandConfig = $this->commandsMapping[$name] ?? null;
        if ($commandConfig === null) {
            return false;
        }

        [
            'command' => $command,
            'initMessage' => $initMessage,
            'errorMessage' => $errorMessage,
            'failOnError' => $failOnError,
        ] = $commandConfig;
        $io->write($initMessage);

        $command = [$this->phpBinary, ...explode(' ', $command)];
        $io->write(
            sprintf(' <options=bold>[Running "%s"]</> ', implode(' ', $command)),
            false,
            OutputInterface::VERBOSITY_VERBOSE,
        );

        $process = $this->processHelper->run($io, $command);
        if (! $failOnError || $process->isSuccessful()) {
            $io->writeln(' <info>Success!</info>');
            return true;
        }

        if (! $io->isVerbose()) {
            $io->error(sprintf('%s. Run this command with -vvv to see specific error info.', $errorMessage));
        }

        return false;
    }
}
