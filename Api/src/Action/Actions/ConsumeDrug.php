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
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ConsumeDrug extends AbstractAction
{
    protected string $name = ActionEnum::CONSUME_DRUG;

    /** @var GameItem */
    protected $parameter;

    private PlayerServiceInterface $playerService;
    private EquipmentEffectServiceInterface $equipmentServiceEffect;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        ActionServiceInterface $actionService,
        ValidatorInterface $validator,
        PlayerServiceInterface $playerService,
        EquipmentEffectServiceInterface $equipmentServiceEffect,
        StatusServiceInterface $statusService,
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService,
            $validator
        );

        $this->playerService = $playerService;
        $this->equipmentServiceEffect = $equipmentServiceEffect;
        $this->statusService = $statusService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Reach(['reach' => ReachEnum::ROOM, 'groups' => ['visibility']]));
        $metadata->addConstraint(new Status([
            'status' => PlayerStatusEnum::DRUG_EATEN,
            'contain' => false,
            'target' => Status::PLAYER,
            'groups' => ['execute'],
            'message' => ActionImpossibleCauseEnum::CONSUME_DRUG_TWICE,
        ]));
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
        /** @var Drug $drugMechanic */
        $drugMechanic = $this->parameter->getEquipment()->getMechanicByName(EquipmentMechanicEnum::DRUG);

        if (null === $drugMechanic) {
            throw new \Exception('Cannot consume this equipment');
        }

        // @TODO add disease, cures and extra effects
        $equipmentEffect = $this->equipmentServiceEffect->getConsumableEffect($drugMechanic, $this->player->getDaedalus());

        if (!$this->player->isMush()) {
            $this->dispatchConsumableEffects($equipmentEffect);
            $this->statusService
                ->createChargeStatus(
                    PlayerStatusEnum::DRUG_EATEN,
                    $this->player,
                    ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                    null,
                    VisibilityEnum::HIDDEN,
                    VisibilityEnum::HIDDEN,
                    1,
                    0,
                    true
                );
        }

        $this->playerService->persist($this->player);

        // if no charges consume equipment
        $equipmentEvent = new EquipmentEvent($this->parameter, VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        return new Success();
    }

    private function dispatchConsumableEffects(ConsumableEffect $consumableEffect): void
    {
        $modifier = new Modifier();
        if ($consumableEffect->getActionPoint() !== 0) {
            $modifier
                ->setDelta($consumableEffect->getActionPoint())
                ->setTarget(ModifierTargetEnum::ACTION_POINT);
            $playerEvent = new PlayerEvent($this->player);
            $playerEvent->setModifier($modifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }
        if ($consumableEffect->getMovementPoint() !== 0) {
            $modifier
                ->setDelta($consumableEffect->getMovementPoint())
                ->setTarget(ModifierTargetEnum::MOVEMENT_POINT);
            $playerEvent = new PlayerEvent($this->player);
            $playerEvent->setModifier($modifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }
        if ($consumableEffect->getHealthPoint() !== 0) {
            $modifier
                ->setDelta($consumableEffect->getHealthPoint())
                ->setTarget(ModifierTargetEnum::HEALTH_POINT);
            $playerEvent = new PlayerEvent($this->player);
            $playerEvent->setModifier($modifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }
        if ($consumableEffect->getMoralPoint() !== 0) {
            $modifier
                ->setDelta($consumableEffect->getMoralPoint())
                ->setTarget(ModifierTargetEnum::MORAL_POINT);
            $playerEvent = new PlayerEvent($this->player);
            $playerEvent->setModifier($modifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }
        if ($consumableEffect->getSatiety() !== 0) {
            $modifier
                ->setDelta($consumableEffect->getSatiety())
                ->setTarget(ModifierTargetEnum::SATIETY);
            $playerEvent = new PlayerEvent($this->player);
            $playerEvent->setModifier($modifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }
    }
}
