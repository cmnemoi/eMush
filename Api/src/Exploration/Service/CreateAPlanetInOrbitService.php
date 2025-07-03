<?php

declare(strict_types=1);

namespace Mush\Exploration\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Exploration\Entity\Planet;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Service\StatusServiceInterface;

final class CreateAPlanetInOrbitService implements CreateAPlanetInOrbitServiceInterface
{
    public function __construct(
        private PlanetServiceInterface $planetService,
        private StatusServiceInterface $statusService,
    ) {}

    public function execute(Daedalus $daedalus, bool $revealAllSectors = false): Planet
    {
        $this->planetService->delete($this->planetService->findAllByDaedalus($daedalus)->toArray());
        $player = $daedalus->getPlayers()->getPlayerAlive()->getFirstOrThrow();

        $planet = $this->planetService->createPlanet($player);

        if ($revealAllSectors) {
            $this->planetService->revealPlanetSectors($planet, $planet->getSize());
        }

        $this->statusService->createStatusFromName(
            statusName: DaedalusStatusEnum::IN_ORBIT,
            holder: $daedalus,
            tags: [],
            time: new \DateTime(),
        );

        return $planet;
    }
}
