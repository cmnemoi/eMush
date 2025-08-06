<?php

declare(strict_types=1);

namespace Mush\Tests\unit\Game\TestDoubles;

use Mush\MetaGame\Service\AdminServiceInterface;

final class FakeAdminService implements AdminServiceInterface
{
    private bool $isInMaintenance = false;

    public function isGameInMaintenance(): bool
    {
        return $this->isInMaintenance;
    }

    public function putGameInMaintenance(): void
    {
        $this->isInMaintenance = true;
    }

    public function removeGameFromMaintenance(): void
    {
        $this->isInMaintenance = false;
    }

    public function setIsInMaintenance(bool $isInMaintenance): void
    {
        $this->isInMaintenance = $isInMaintenance;
    }
}
