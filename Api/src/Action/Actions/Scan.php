<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Fail;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\NumberOfDiscoverablePlanets;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Scan extends AttemptAction
{
    protected ActionEnum $name = ActionEnum::SCAN;
    private PlanetServiceInterface $planetService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        PlanetServiceInterface $planetService,
        private DaedalusRepositoryInterface $daedalusRepository,
    ) {
        parent::__construct($eventService, $actionService, $validator, $randomService);
        $this->planetService = $planetService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new HasStatus([
            'status' => PlayerStatusEnum::FOCUSED,
            'target' => HasStatus::PLAYER,
            'statusTargetName' => EquipmentEnum::ASTRO_TERMINAL,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new HasStatus([
            'status' => DaedalusStatusEnum::IN_ORBIT,
            'target' => HasStatus::DAEDALUS,
            'contain' => false,
            'groups' => ['visibility'],
        ]));
        $metadata->addConstraint(new NumberOfDiscoverablePlanets(['groups' => ['visibility']]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function applyEffect(ActionResult $result): void
    {
        if ($result instanceof Fail) {
            return;
        }

        $planet = $this->planetService->createPlanet($this->player);

        $planet->getDaedalusStatistics()->incrementPlanetsFound();
        $this->daedalusRepository->save($this->player->getDaedalus());

        $numberOfSectorsToReveal = $this->getOutputQuantity();
        if ($numberOfSectorsToReveal <= 0) {
            return;
        }

        $this->planetService->revealPlanetSectors($planet, $numberOfSectorsToReveal);
    }
}
