<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\ConsumableEffect;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\ModifierTargetEnum;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Consume extends AbstractAction
{
    protected string $name = ActionEnum::CONSUME;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private EquipmentEffectServiceInterface $equipmentServiceEffect;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        EquipmentEffectServiceInterface $equipmentServiceEffect,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->equipmentServiceEffect = $equipmentServiceEffect;
        $this->statusService = $statusService;
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($equipment = $actionParameters->getItem()) &&
            !($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->gameEquipment = $equipment;
    }

    public function canExecute(): bool
    {
        return !($this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::DRUG) &&
                $this->player->getStatusByName(PlayerStatusEnum::DRUG_EATEN)) &&
                $this->player->canReachEquipment($this->gameEquipment) &&
                $this->gameEquipment->getEquipment()->hasAction(ActionEnum::CONSUME) &&
                !$this->player->getStatusByName(PlayerStatusEnum::FULL_STOMACH);
    }

    protected function applyEffects(): ActionResult
    {
        $rationType = $this->gameEquipment->getEquipment()->getRationsMechanic();

        if (null === $rationType) {
            throw new \Exception('Cannot consume this equipment');
        }

        // @TODO add disease, cures and extra effects
        $equipmentEffect = $this->equipmentServiceEffect->getConsumableEffect($rationType, $this->player->getDaedalus());

        if (!$this->player->isMush()) {
            $this->dispatchConsumableEffects($equipmentEffect);
        }

        // If the ration is a drug player get Drug_Eaten status that prevent it from eating another drug this cycle.
        if ($rationType instanceof Drug) {
            $drugEatenStatus = $this->statusService
                ->createChargePlayerStatus(
                    PlayerStatusEnum::DRUG_EATEN,
                    $this->player,
                    ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                    VisibilityEnum::HIDDEN,
                    1,
                    0,
                    true
                );
        }

        $this->playerService->persist($this->player);

        // if no charges consume equipment
        $equipmentEvent = new EquipmentEvent($this->gameEquipment, VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);

        return new Success(ActionLogEnum::CONSUME_SUCCESS, VisibilityEnum::COVERT);
    }

    private function dispatchConsumableEffects(ConsumableEffect $consumableEffect): void
    {
        $modifier = new Modifier();
        if ($consumableEffect->getActionPoint() !== 0) {
            $modifier
                ->setDelta($consumableEffect->getActionPoint())
                ->setTarget(ModifierTargetEnum::ACTION_POINT)
            ;
            $playerEvent = new PlayerEvent($this->player);
            $playerEvent->setModifier($modifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }
        if ($consumableEffect->getMovementPoint() !== 0) {
            $modifier
                ->setDelta($consumableEffect->getMovementPoint())
                ->setTarget(ModifierTargetEnum::MOVEMENT_POINT)
            ;
            $playerEvent = new PlayerEvent($this->player);
            $playerEvent->setModifier($modifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }
        if ($consumableEffect->getHealthPoint() !== 0) {
            $modifier
                ->setDelta($consumableEffect->getHealthPoint())
                ->setTarget(ModifierTargetEnum::HEALTH_POINT)
            ;
            $playerEvent = new PlayerEvent($this->player);
            $playerEvent->setModifier($modifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }
        if ($consumableEffect->getMovementPoint() !== 0) {
            $modifier
                ->setDelta($consumableEffect->getMoralPoint())
                ->setTarget(ModifierTargetEnum::MORAL_POINT)
            ;
            $playerEvent = new PlayerEvent($this->player);
            $playerEvent->setModifier($modifier);
            $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);
        }
    }
}
