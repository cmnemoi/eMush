<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\HasEquipment;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\InventoryFull;
use Mush\Action\Validator\NumberOfAttackingHunters;
use Mush\Action\Validator\NumberPlayersAliveInRoom;
use Mush\Action\Validator\PlaceName;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\GamePlantEnum;
use Mush\Equipment\Enum\GameRationEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Play with" action (playing with Pavlov, the April Fools dog).
 * This action is granted by Pavlov.
 *
 * For 2 PA, "Play with" supposedly gives 3 Morale Points
 * to the player committing the action, if they haven't
 * done it before.
 * However, this action has a million conditions, and its true effect is to make Pavlov disappear. Happy April Fools!
 */
class PlayWithDogFake extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::PLAY_WITH_DOG;

    protected StatusServiceInterface $statusService;

    protected DeleteEquipmentServiceInterface $deleteEquipmentService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        StatusServiceInterface $statusService,
        DeleteEquipmentServiceInterface $deleteEquipmentService,
    ) {
        parent::__construct($eventService, $actionService, $validator);

        $this->statusService = $statusService;
        $this->deleteEquipmentService = $deleteEquipmentService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new HasStatus(['status' => PlayerStatusEnum::GERMAPHOBE, 'target' => HasStatus::PLAYER, 'contain' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PLAYER_IS_GERMAPHOBIC]));
        $metadata->addConstraint(new HasStatus(['status' => DaedalusStatusEnum::TRAVELING, 'target' => HasStatus::DAEDALUS, 'contain' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::DOG_IS_SEASICK]));
        $metadata->addConstraint(new NumberOfAttackingHunters(['mode' => NumberOfAttackingHunters::GREATER_THAN, 'number' => 0, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::DOG_IS_WORRIED]));
        $metadata->addConstraint(new HasEquipment(['reach' => ReachEnum::DAEDALUS, 'equipments' => [EquipmentEnum::FUEL_TANK], 'checkIfOperational' => true, 'number' => 2, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::DISTRACTED_BY_BROKEN_FUEL_TANK]));
        $metadata->addConstraint(new HasEquipment(['reach' => ReachEnum::ROOM, 'equipments' => [ItemEnum::SCHRODINGER], 'contains' => false, 'target' => HasEquipment::PLAYER, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::BOTHERING_CAT]));
        $metadata->addConstraint(new HasEquipment(['reach' => ReachEnum::ROOM, 'equipments' => GameRationEnum::getAllRations(), 'all' => false, 'contains' => false, 'target' => HasEquipment::PLAYER, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::DISTRACTED_BY_FOOD]));
        $metadata->addConstraint(new HasEquipment(['reach' => ReachEnum::SHELVE_NOT_HIDDEN, 'equipments' => GamePlantEnum::getAll(), 'all' => false, 'contains' => false, 'target' => HasEquipment::PLAYER, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::DISTRACTED_BY_PLANT]));
        $metadata->addConstraint(new HasEquipment(['reach' => ReachEnum::INVENTORY, 'equipments' => [ToolItemEnum::MAD_KUBE], 'contains' => true, 'checkIfOperational' => true, 'target' => HasEquipment::PLAYER, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::NEED_A_BALL]));
        $metadata->addConstraint(new HasStatus(['status' => DaedalusStatusEnum::IN_ORBIT, 'target' => HasStatus::DAEDALUS, 'contain' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::WANTS_WALKIES]));
        $metadata->addConstraint(new InventoryFull(['groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::HANDS_FULL]));
        $metadata->addConstraint(new PlaceName(['places' => RoomEnum::getStorages(), 'isAt' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::ROOM_TOO_MESSY]));
        $metadata->addConstraint(new PlaceName(['places' => RoomEnum::getTurrets()->toArray(), 'isAt' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::ROOM_TOO_SMALL]));
        $metadata->addConstraint(new PlaceName(['places' => RoomEnum::getCorridors()->toArray(), 'isAt' => false, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::ROOM_TOO_TIGHT]));
        $metadata->addConstraint(new NumberPlayersAliveInRoom(['mode' => NumberPlayersAliveInRoom::GREATER_THAN, 'number' => 1, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::PLAYTIME_SOLO]));
        $metadata->addConstraint(new PlaceName(['places' => [RoomEnum::ENGINE_ROOM], 'isAt' => true, 'groups' => ['execute'], 'message' => ActionImpossibleCauseEnum::ROOM_TOO_SAD]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameItem;
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $this->deleteEquipmentService->execute(gameEquipment: $this->gameItemTarget(), tags: $this->getTags());
    }
}
