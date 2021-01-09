<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Modifier;
use Mush\Player\Entity\Player;
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

        $actionModifier = new Modifier();

        if (!$this->player->isMush()) {
            //@TODO
//            $actionModifier
//                ->setActionPointModifier($equipmentEffect->getActionPoint())
//                ->setMovementPointModifier($equipmentEffect->getMovementPoint())
//                ->setHealthPointModifier($equipmentEffect->getHealthPoint())
//                ->setMoralPointModifier($equipmentEffect->getMoralPoint())
//            ;
        }
//        $actionModifier->setSatietyModifier($equipmentEffect->getSatiety());

        $playerEvent = new PlayerEvent($this->player);
        $playerEvent->setModifier($actionModifier);
        $this->eventDispatcher->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

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
        $this->gameEquipment->removeLocation();
        $this->gameEquipmentService->delete($this->gameEquipment);

        return new Success(ActionLogEnum::CONSUME_SUCCESS, VisibilityEnum::COVERT);
    }
}
