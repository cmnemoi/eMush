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
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GearItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Scan extends AttemptAction
{
    protected string $name = ActionEnum::SCAN;
    private RoomLogServiceInterface $roomLogService;
    private PlanetServiceInterface $planetService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        PlanetServiceInterface $planetService
    ) {
        parent::__construct($eventService, $actionService, $validator, $randomService);
        $this->roomLogService = $roomLogService;
        $this->planetService = $planetService;
    }

    protected function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
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

    protected function applyEffect(ActionResult $result): void
    {
        if ($result instanceof Fail) {
            return;
        }

        $planet = $this->planetService->createPlanet($this->player);

        if ($this->player->getPlace()->hasEquipmentByName(GearItemEnum::MAGELLAN_LIQUID_MAP)) {
            $numberOfSectorsToReveal = $this->randomService->random(1, $this->getOutputQuantity());

            $this->planetService->revealPlanetSectors($planet, $numberOfSectorsToReveal);

            $this->roomLogService->createLog(
                logKey: LogEnum::LIQUID_MAP_HELPED,
                place: $this->player->getPlace(),
                visibility: VisibilityEnum::PUBLIC,
                type: 'event_log',
                player: $this->player,
                parameters: [],
                dateTime: new \DateTime()
            );
        }
    }
}
