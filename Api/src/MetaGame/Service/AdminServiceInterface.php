<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Mush\MetaGame\Entity\AdminSecret;

interface AdminServiceInterface
{   
    public function editSecret(string $name, string $value): void;

    public function findAllSecrets(): array;

    public function findSecretByName(string $name): ?AdminSecret;

    public function isGameInMaintenance(): bool;

    public function putGameInMaintenance(): void;

    public function removeGameFromMaintenance(): void;
}
