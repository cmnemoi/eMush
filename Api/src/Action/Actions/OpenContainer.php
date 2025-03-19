<?php

namespace Mush\Action\Actions;

use Mush\Action\Entity\ActionResult\ActionResult;
use Mush\Action\Entity\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\PlaceType;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Open" action on containers.
 * This action is granted by Survival Kit, Lunchbox, Coffee Thermos, Christmas Gifts.
 */
class OpenContainer extends AbstractAction
{
    private const array CONTAINER_LIST = [
        ItemEnum::ANNIVERSARY_GIFT => ActionLogEnum::OPEN_ANNIVERSARY_GIFT,
        ItemEnum::COFFEE_THERMOS => ActionLogEnum::OPEN_COFFEE_THERMOS,
        ItemEnum::LUNCHBOX => ActionLogEnum::OPEN_LUNCHBOX,
    ];
    protected ActionEnum $name = ActionEnum::OPEN_CONTAINER;

    protected RandomServiceInterface $randomService;
    protected GameEquipmentServiceInterface $gameEquipmentService;
    private RoomLogServiceInterface $roomLogService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        GameEquipmentServiceInterface $gameEquipmentService,
        RoomLogServiceInterface $roomLogService,
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->randomService = $randomService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->roomLogService = $roomLogService;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new PlaceType(['groups' => ['execute'], 'type' => 'planet', 'allowIfTypeMatches' => false, 'message' => ActionImpossibleCauseEnum::ON_PLANET]));
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
        $target = $this->gameEquipmentTarget();

        $containerType = $target->getContainerMechanicOrThrow();

        $contentName = (string) $this->randomService->getSingleRandomElementFromProbaCollection($containerType->getContentWeights($this->player));
        $contentQuantity = $containerType->getQuantityOfItemOrThrow($contentName);

        $this->createOpeningLog($contentName, $contentQuantity);

        if ($target->isOnLastChargeOrSingleUse()) {
            $this->destroyEmptyContainer();
        }

        $this->createContents($contentName, $contentQuantity);
    }

    private function createOpeningLog(string $contentName, int $contentQuantity): void
    {
        $logKey = $this->gameEquipmentTarget()->getName();
        $content = $this->gameEquipmentService->findGameEquipmentConfigFromNameAndDaedalus($contentName, $this->gameEquipmentTarget()->getDaedalus());
        $logParameters = [
            $this->player->getLogKey() => $this->player->getLogName(),
            $content->getLogKey() => $content->getEquipmentShortName(),
            'quantity' => $contentQuantity,
        ];
        $this->roomLogService->createLog(
            logKey: self::CONTAINER_LIST[$logKey],
            place: $this->player->getPlace(),
            visibility: VisibilityEnum::PUBLIC,
            type: 'actions_log',
            player: $this->player,
            parameters: $logParameters,
            dateTime: new \DateTime(),
        );
    }

    private function createContents(string $equipmentName, int $quantity): void
    {
        $this->gameEquipmentService->createGameEquipmentsFromName(
            $equipmentName,
            $this->player,
            $quantity,
            $this->getTags(),
            new \DateTime(),
            VisibilityEnum::PUBLIC
        );
    }

    private function destroyEmptyContainer(): void
    {
        $equipmentEvent = new InteractWithEquipmentEvent(
            $this->gameEquipmentTarget(),
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getTags(),
            new \DateTime(),
        );
        $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
