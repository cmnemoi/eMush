<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Service\PatrolShipManoeuvreServiceInterface;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\PatrolShip;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Land extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::LAND;

    private PatrolShipManoeuvreServiceInterface $patrolShipManoeuvreService;
    private PlayerServiceInterface $playerService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PatrolShipManoeuvreServiceInterface $patrolShipManoeuvreService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->patrolShipManoeuvreService = $patrolShipManoeuvreService;
        $this->playerService = $playerService;
        $this->randomService = $randomService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['visibility'], 'type' => PlaceTypeEnum::PATROL_SHIP]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        // a successful landing still create damage to the hull, only critical success avoid any damage
        $criticalSuccessRate = $this->actionService->getActionModifiedActionVariable(
            player: $this->player,
            actionConfig: $this->actionConfig,
            actionProvider: $this->actionProvider,
            actionTarget: $this->target,
            variableName: ActionVariableEnum::PERCENTAGE_CRITICAL,
            tags: $this->getTags()
        );
        $isSuccessCritical = $this->randomService->isSuccessful($criticalSuccessRate);

        return $isSuccessCritical ? new CriticalSuccess() : new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameEquipment $patrolShip */
        $patrolShip = $this->target;

        $daedalus = $patrolShip->getDaedalus();

        /** @var PatrolShip $patrolShipMechanic */
        $patrolShipMechanic = $patrolShip->getMechanicByNameOrThrow(EquipmentMechanicEnum::PATROL_SHIP);
        $patrolShipDockingPlace = $daedalus->getPlaceByNameOrThrow($patrolShipMechanic->getDockingPlace());

        foreach ($this->player->getPlace()->getPlayers()->getPlayerAlive() as $player) {
            $this->playerService->changePlace($player, $patrolShipDockingPlace);
        }

        $this->patrolShipManoeuvreService->handleLand(
            patrolShip: $patrolShip,
            pilot: $this->player,
            actionResult: $result,
            tags: $this->getActionConfig()->getActionTags(),
            time: new \DateTime(),
        );
    }
}
