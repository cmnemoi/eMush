<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Shredable;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Shred extends AbstractAction
{
    protected string $name = ActionEnum::SHRED;

    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Mechanic(['mechanic' => EquipmentMechanicEnum::DOCUMENT, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Shredable(['groups' => ['visibility']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        $equipmentEvent = new EquipmentEvent(
            $parameter,
            $this->player->getPlace(),
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            new \DateTime());
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $this->playerService->persist($this->player);

        return new Success();
    }
}
