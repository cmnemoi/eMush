<?php

declare(strict_types=1);

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\CriticalSuccess;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Enum\ActionVariableEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Service\PatrolShipManoeuvreServiceInterface;
use Mush\Action\Validator\HasStatus;
use Mush\Action\Validator\NeronCrewLock;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\MoveEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Enum\PlayerNotificationEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\UpdatePlayerNotificationService;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\DaedalusStatusEnum;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class Takeoff extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::TAKEOFF;

    private PatrolShipManoeuvreServiceInterface $patrolShipManoeuvreService;
    private PlayerServiceInterface $playerService;
    private RandomServiceInterface $randomService;
    private RoomLogServiceInterface $roomLogService;
    private UpdatePlayerNotificationService $updatePlayerNotification;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PatrolShipManoeuvreServiceInterface $patrolShipManoeuvreService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        RoomLogServiceInterface $roomLogService,
        UpdatePlayerNotificationService $updatePlayerNotification,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->patrolShipManoeuvreService = $patrolShipManoeuvreService;
        $this->playerService = $playerService;
        $this->randomService = $randomService;
        $this->roomLogService = $roomLogService;
        $this->updatePlayerNotification = $updatePlayerNotification;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['visibility'], 'type' => PlaceTypeEnum::ROOM]));
        $metadata->addConstraint(new HasStatus([
            'status' => DaedalusStatusEnum::TRAVELING,
            'contain' => false,
            'target' => HasStatus::DAEDALUS,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::DAEDALUS_TRAVELING,
        ]));
        $metadata->addConstraint(new NeronCrewLock([
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::TERMINAL_NERON_LOCK,
        ]));
    }

    public function support(?LogParameterInterface $target, array $parameters): bool
    {
        return $target instanceof GameEquipment;
    }

    protected function checkResult(): ActionResult
    {
        // a successful landing still create damage to the hull, only critical success avoid any damage
        $criticalSuccessRate = $this->actionService->getActionModifiedActionVariable(
            $this->player,
            $this->actionConfig,
            $this->actionProvider,
            $this->target,
            ActionVariableEnum::PERCENTAGE_CRITICAL
        );
        $isSuccessCritical = $this->randomService->isSuccessful($criticalSuccessRate);

        return $isSuccessCritical ? new CriticalSuccess() : new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        $daedalus = $this->player->getDaedalus();

        /** @var GameEquipment $patrolShip */
        $patrolShip = $this->target;

        $this->createDropCriticalItemsLogs();
        $this->dropCriticalItems();
        $this->playerService->changePlace($this->player, $daedalus->getPlaceByNameOrThrow($patrolShip->getName()));
        $this->patrolShipManoeuvreService->handleTakeoff(
            patrolShip: $patrolShip,
            pilot: $this->player,
            actionResult: $result,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
    }

    private function createDropCriticalItemsLogs(): void
    {
        foreach ($this->player->getAllCriticalItems() as $item) {
            $this->roomLogService->createLog(
                logKey: ActionLogEnum::DROP,
                place: $this->player->getPlace(),
                visibility: VisibilityEnum::PUBLIC,
                type: 'actions_log',
                player: $this->player,
                parameters: [
                    $this->player->getLogKey() => $this->player->getLogName(),
                    \sprintf('target_%s', $item->getLogKey()) => $item->getLogName(),
                ],
            );
        }
    }

    private function dropCriticalItems(): void
    {
        $this->player->getCriticalItemsForMe()->map(fn (GameItem $item) => $this->dropItem($item));
    }

    private function createDroppedCriticalItemNotification(): void
    {
        $this->updatePlayerNotification->execute(
            player: $this->player,
            message: PlayerNotificationEnum::DROPPED_CRITICAL_ITEM->toString(),
        );
    }

    private function dropItem(GameItem $item): void
    {
        $equipmentEvent = new MoveEquipmentEvent(
            equipment: $item,
            newHolder: $this->player->getPlace(),
            author: $this->player,
            visibility: VisibilityEnum::HIDDEN,
            tags: $this->getTags(),
            time: new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::CHANGE_HOLDER);
        $this->createDroppedCriticalItemNotification();
    }
}
