<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Mechanic;
use Mush\Action\Validator\ParameterHasAction;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Shredable;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class Shred extends AbstractAction
{
    protected string $name = ActionEnum::SHRED;

    /** @var GameItem */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadVisibilityValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Mechanic(['mechanic' => EquipmentMechanicEnum::DOCUMENT]));
        $metadata->addConstraint(new Reach());
        $metadata->addConstraint(new Shredable());
        //@TODO: can shred

    }

    protected function applyEffects(): ActionResult
    {
        $equipmentEvent = new EquipmentEvent($this->parameter, VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        $this->playerService->persist($this->player);

        return new Success();
    }
}
