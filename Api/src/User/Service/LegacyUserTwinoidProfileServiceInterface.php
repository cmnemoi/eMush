<?php

declare(strict_types=1);

namespace Mush\User\Service;

use Mush\User\Entity\LegacyUser;
use Mush\User\Entity\LegacyUserTwinoidProfile;

interface LegacyUserTwinoidProfileServiceInterface
{
    public function findByLegacyUser(LegacyUser $legacyUser): ?LegacyUserTwinoidProfile;
}