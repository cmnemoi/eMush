<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Event\ApplyEffectEvent;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\FullHealth;
use Mush\Action\Validator\Reach;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Event\InteractWithEquipmentEvent;
use Mush\Game\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UltraHeal extends AbstractAction
{
    protected string $name = ActionEnum::ULTRAHEAL;

    private PlayerServiceInterface $playerService;
    private PlayerVariableServiceInterface $playerVariableService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        PlayerVariableServiceInterface $playerVariableService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
        $this->playerVariableService = $playerVariableService;
    }

    protected function support(?LogParameterInterface $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new FullHealth(['target' => FullHealth::PLAYER, 'groups' => ['visible']]));
    }

    protected function checkResult(): ActionResult
    {
        return new Success();
    }

    protected function applyEffect(ActionResult $result): void
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;
        $time = new \DateTime();

        $this->playerVariableService->setPlayerVariableToMax($this->player, PlayerVariableEnum::HEALTH_POINT);

        $this->playerService->persist($this->player);

        $healEvent = new ApplyEffectEvent(
            $this->player,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            $time
        );
        $this->eventService->dispatch($healEvent, ApplyEffectEvent::HEAL);

        $equipmentEvent = new InteractWithEquipmentEvent(
            $parameter,
            $this->player,
            VisibilityEnum::HIDDEN,
            $this->getActionName(),
            $time
        );
        $this->eventService->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
