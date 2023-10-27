<?php

declare(strict_types=1);

namespace Mush\User\Service;

use Mush\User\Entity\LegacyUser;
use Mush\User\Entity\LegacyUserTwinoidProfile;
use Mush\User\Repository\LegacyUserTwinoidProfileRepository;

final class LegacyUserTwinoidProfileService implements LegacyUserTwinoidProfileServiceInterface
{
    private LegacyUserTwinoidProfileRepository $legacyUserTwinoidProfileRepository;

    public function __construct(LegacyUserTwinoidProfileRepository $legacyUserTwinoidProfileRepository)
    {
        $this->legacyUserTwinoidProfileRepository = $legacyUserTwinoidProfileRepository;
    }

    public function findByLegacyUser(LegacyUser $legacyUser): ?LegacyUserTwinoidProfile
    {
        return $this->legacyUserTwinoidProfileRepository->findOneByLegacyUser($legacyUser);
    }
}
