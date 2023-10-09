<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Mush\User\Entity\User;
use Symfony\Component\HttpKernel\KernelInterface;

final class AdminService implements AdminServiceInterface
{
    private const MAINTENANCE_FILE = '/var/maintenance.lock';

    private KernelInterface $kernel;
    private string $maintenanceFile;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->maintenanceFile = $this->kernel->getProjectDir() . self::MAINTENANCE_FILE;
    }

    public function isGameInMaintenance(): bool
    {
        return file_exists($this->maintenanceFile);
    }

    public function putGameInMaintenance(): void
    {
        $handle = fopen($this->maintenanceFile, 'w');
        fclose($handle);
    }

    public function removeGameFromMaintenance(): void
    {
        unlink($this->maintenanceFile);
    }
}
