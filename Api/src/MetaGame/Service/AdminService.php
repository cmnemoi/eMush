<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\MetaGame\Entity\AdminSecret;
use Symfony\Component\HttpKernel\KernelInterface;

final class AdminService implements AdminServiceInterface
{
    private const MAINTENANCE_FILE = '/var/maintenance.lock';

    private KernelInterface $kernel;
    private string $maintenanceFile;
    private EntityManagerInterface $entityManager;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $entityManager)
    {
        $this->kernel = $kernel;
        $this->entityManager = $entityManager;
        $this->maintenanceFile = $this->kernel->getProjectDir() . self::MAINTENANCE_FILE;
    }

    public function editSecret(string $name, string $value): void
    {
        $secret = $this->entityManager->getRepository(AdminSecret::class)->findOneBy(['name' => $name]);
        if (!$secret instanceof AdminSecret) {
            $secret = new AdminSecret($name, $value);
        }

        $this->entityManager->persist($secret);
        $this->entityManager->flush();
    }

    public function findAllSecrets(): array
    {
        return $this->entityManager->getRepository(AdminSecret::class)->findAll();
    }

    public function findSecretByName(string $name): ?AdminSecret
    {
        return $this->entityManager->getRepository(AdminSecret::class)->findOneBy(['name' => $name]);
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
