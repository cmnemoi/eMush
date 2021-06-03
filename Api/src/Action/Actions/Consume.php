<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Action\Validator\Reach;
use Mush\Action\Validator\Status;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Player\Event\PlayerModifierEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Consume extends AbstractAction
{
    protected string $name = ActionEnum::CONSUME;

    protected PlayerServiceInterface $playerService;
    protected EquipmentEffectServiceInterface $equipmentServiceEffect;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        EquipmentEffectServiceInterface $equipmentServiceEffect
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
        $this->equipmentServiceEffect = $equipmentServiceEffect;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Status([
            'status' => PlayerStatusEnum::FULL_STOMACH,
            'contain' => false,
            'target' => Status::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::CONSUME_FULL_BELLY,
        ]));
    }

    protected function applyEffects(): ActionResult
    {
        /** @var GameItem $parameter */
        $parameter = $this->parameter;

        $rationType = $parameter->getEquipment()->getRationsMechanic();

        if (null === $rationType) {
            throw new \Exception('Cannot consume this equipment');
        }

        // @TODO add disease, cures and extra effects
        $equipmentEffect = $this->equipmentServiceEffect->getConsumableEffect($rationType, $this->player->getDaedalus());

        $this->dispatchConsumableEffects($equipmentEffect);

        $this->playerService->persist($this->player);

        // if no charges consume equipment
        $equipmentEvent = new EquipmentEvent($parameter, VisibilityEnum::HIDDEN, new \DateTime());
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        return new Success();
    }

    protected function dispatchConsumableEffects(ConsumableEffect $consumableEffect): void
    {
        if (($delta = $consumableEffect->getActionPoint()) !== null && !$this->player->isMush()) {
            $playerModifierEvent = new PlayerModifierEvent($this->player, $delta, new \DateTime());
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::ACTION_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getMovementPoint()) !== null && !$this->player->isMush()) {
            $playerModifierEvent = new PlayerModifierEvent($this->player, $delta, new \DateTime());
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MOVEMENT_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getHealthPoint()) !== null && !$this->player->isMush()) {
            $playerModifierEvent = new PlayerModifierEvent($this->player, $delta, new \DateTime());
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::HEALTH_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getMoralPoint()) !== null && !$this->player->isMush()) {
            $playerModifierEvent = new PlayerModifierEvent($this->player, $delta, new \DateTime());
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::MORAL_POINT_MODIFIER);
        }
        if (($delta = $consumableEffect->getSatiety()) !== null) {
            $playerModifierEvent = new PlayerModifierEvent($this->player, $delta, new \DateTime());
            $this->eventDispatcher->dispatch($playerModifierEvent, PlayerModifierEvent::SATIETY_POINT_MODIFIER);
        }
    }
}
