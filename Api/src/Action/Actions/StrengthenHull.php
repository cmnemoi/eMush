<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\FullHull;
use Mush\Action\Validator\Reach;
use Mush\Daedalus\Event\DaedalusModifierEvent;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Game\Service\RandomServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class StrengthenHull extends AttemptAction
{
    protected string $name = ActionEnum::STRENGTHEN_HULL;

    private const BASE_REPAIR = 5;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        RandomServiceInterface $randomService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator,
            $randomService,
        );
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new FullHull(['groups' => ['execute']]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        $parameter->setPlayer(null);

        $response = $this->makeAttempt();

        if ($response instanceof Success) {
            $quantity = self::BASE_REPAIR;

            $daedalusEvent = new DaedalusModifierEvent(
                $this->player->getDaedalus(),
                $quantity,
                $this->getActionName(),
                new \DateTime()
            );
            $this->eventDispatcher->dispatch($daedalusEvent, DaedalusModifierEvent::CHANGE_HULL);

            $equipmentEvent = new EquipmentEvent(
                $parameter,
                $this->player->getPlace(),
                VisibilityEnum::HIDDEN,
                $this->getActionName(),
                new \DateTime()
            );
            $equipmentEvent->setPlayer($this->player);
            $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
        }

        return $response;
    }
}
