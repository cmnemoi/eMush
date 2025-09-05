<?php

declare(strict_types=1);

namespace Mush\Notification\Cli;

use Minishlink\WebPush\VAPID;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(name: 'mush:generate-web-push-keys')]
final class WebPushGenerateKeysCli extends Command
{
    private KernelInterface $kernel;

    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();
        $this->kernel = $kernel;
    }

    /**
     * @throws \ErrorException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $envFiles = $this->getEnvFiles();
        $filesToUpdate = $this->getFilesToUpdate($envFiles, $io);

        if (empty($filesToUpdate)) {
            $io->success('No VAPID keys to generate. All env files already have values.');

            return 0;
        }

        $keys = $this->generateVapidKeys();
        $this->displayKeys($io, $keys);
        $this->writeKeysToFiles($filesToUpdate, $keys, $io);

        $io->success('VAPID keys updated in .env files.');

        return 0;
    }

    /**
     * @return string[]
     */
    private function getEnvFiles(): array
    {
        $projectDir = $this->kernel->getProjectDir();

        return [
            $projectDir . '/.env',
            $projectDir . '/.env.local',
            $projectDir . '/.env.test.local',
        ];
    }

    /**
     * @param string[] $envFiles
     *
     * @return string[]
     */
    private function getFilesToUpdate(array $envFiles, SymfonyStyle $io): array
    {
        $filesToUpdate = [];
        foreach ($envFiles as $file) {
            if (!file_exists($file)) {
                throw new \RuntimeException("File {$file} does not exist.");
            }
            $content = file_get_contents($file);
            if ($content === false) {
                throw new \RuntimeException("Could not read file {$file}.");

                continue;
            }
            $public = $this->getEnvVarValue($content, 'VAPID_PUBLIC_KEY');
            $private = $this->getEnvVarValue($content, 'VAPID_PRIVATE_KEY');
            if ($public === '__GENERATED_ON_FIRST_INSTALL__' || $private === '__GENERATED_ON_FIRST_INSTALL__') {
                $filesToUpdate[] = $file;
            } else {
                $io->note("{$file}: VAPID keys already set, skipping.");
            }
        }

        return $filesToUpdate;
    }

    /**
     * @return array{publicKey: string, privateKey: string}
     */
    private function generateVapidKeys(): array
    {
        return VAPID::createVapidKeys();
    }

    /**
     * @param array{publicKey: string, privateKey: string} $keys
     */
    private function displayKeys(SymfonyStyle $io, array $keys): void
    {
        $io->success('Your VAPID keys have been generated!');
        $io->writeln(\sprintf('Your public key is: <info>%s</info>', $keys['publicKey']));
        $io->writeln(\sprintf('Your private key is: <info>%s</info>', $keys['privateKey']));
        $io->newLine(2);
    }

    /**
     * @param string[]                                     $filesToUpdate
     * @param array{publicKey: string, privateKey: string} $keys
     */
    private function writeKeysToFiles(array $filesToUpdate, array $keys, SymfonyStyle $io): void
    {
        foreach ($filesToUpdate as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                throw new \RuntimeException("Could not read file {$file}.");
            }
            $content = $this->replaceOrAddEnvVar($content, 'VAPID_PUBLIC_KEY', $keys['publicKey']);
            $content = $this->replaceOrAddEnvVar($content, 'VAPID_PRIVATE_KEY', $keys['privateKey']);
            file_put_contents($file, $content);
            $io->writeln("<info>Updated {$file}</info>");
        }
    }

    private function getEnvVarValue(string $content, string $key): ?string
    {
        if (preg_match('/^' . preg_quote($key, '/') . '=(.*)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function replaceOrAddEnvVar(string $content, string $key, string $value): string
    {
        $pattern = '/^' . preg_quote($key, '/') . '=.*/m';
        if (preg_match($pattern, $content)) {
            $result = preg_replace($pattern, $key . '=' . $value, $content);
            if ($result === null) {
                throw new \RuntimeException('Could not replace or add env var.');
            }

            return $result;
        }

        // Add at the end
        return rtrim($content) . "\n" . $key . '=' . $value . "\n";
    }
}
