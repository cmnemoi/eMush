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
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class implementing the "Open" action on containers.
 * This action is granted by Survival Kit, Lunchbox, Coffee Thermos, Christmas Gifts.
 */
class OpenContainer extends AbstractAction
{
    protected ActionEnum $name = ActionEnum::OPEN_CONTAINER;

    protected RandomServiceInterface $randomService;
    protected GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(
        EventServiceInterface $eventService,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
        GameEquipmentServiceInterface $gameEquipmentService
    ) {
        parent::__construct(
            $eventService,
            $actionService,
            $validator
        );

        $this->randomService = $randomService;
        $this->gameEquipmentService = $gameEquipmentService;
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
        /** @var GameItem $target */
        $target = $this->target;

        /** @var Container $containerType */
        $containerType = $target->getEquipment()->getMechanicByName(EquipmentMechanicEnum::CONTAINER);
        if (null === $containerType) {
            throw new \Exception('Cannot open this equipment');
        }
        $time = new \DateTime();

        $contentName = $this->randomService->getSingleRandomElementFromProbaCollection($containerType->getContentWeights());

        for ($i = 0; $i < $containerType->getQuantityOfItemOrThrow($contentName); ++$i) {
            $this->gameEquipmentService->createGameEquipmentFromName(
                $contentName,
                $this->player,
                $this->getActionConfig()->getActionTags(),
                new \DateTime(),
                VisibilityEnum::PUBLIC
            );
        }

        if ($target->isOutOfChargesOrSingleUse()) {
            // remove the container
            $equipmentEvent = new InteractWithEquipmentEvent(
                $target,
                $this->player,
                VisibilityEnum::HIDDEN,
                $this->getActionConfig()->getActionTags(),
                $time
            );
            $this->eventService->callEvent($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }
    }
}