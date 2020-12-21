<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Drug;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\EquipmentEffectServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\ActionModifier;
use Mush\Player\Entity\Player;
use Mush\Player\Event\PlayerEvent;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\ChargeStrategyTypeEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Consume extends Action
{
    protected string $name = ActionEnum::CONSUME;

    private GameEquipment $gameEquipment;

    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private EquipmentEffectServiceInterface $equipmentServiceEffect;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        EquipmentEffectServiceInterface $equipmentServiceEffect,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->equipmentServiceEffect = $equipmentServiceEffect;
        $this->statusService = $statusService;
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        if (!($equipment = $actionParameters->getItem()) &&
            !($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
        $this->gameEquipment = $equipment;
    }

    public function canExecute(): bool
    {
        return !($this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::DRUG) &&
                $this->player->canReachEquipment($this->gameEquipment) &&
                $this->player->getStatusByName(PlayerStatusEnum::DRUG_EATEN)) &&
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

        $actionModifier = new ActionModifier();

        if (!$this->player->isMush()) {
            $actionModifier
                ->setActionPointModifier($equipmentEffect->getActionPoint())
                ->setMovementPointModifier($equipmentEffect->getMovementPoint())
                ->setHealthPointModifier($equipmentEffect->getHealthPoint())
                ->setMoralPointModifier($equipmentEffect->getMoralPoint())
            ;
        }
        $actionModifier->setSatietyModifier($equipmentEffect->getSatiety());

        $playerEvent = new PlayerEvent($this->player);
        $playerEvent->setActionModifier($actionModifier);
        $this->eventManager->dispatch($playerEvent, PlayerEvent::MODIFIER_PLAYER);

        // If the ration is a drug player get Drug_Eaten status that prevent it from eating another drug this cycle.
        if ($rationType instanceof Drug) {
            $drugEatenStatus = $this->statusService
                ->createChargePlayerStatus(
                    PlayerStatusEnum::DRUG_EATEN,
                    $this->player,
                    ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                    1,
                    0,
                    true
                );
            $drugEatenStatus->setVisibility(VisibilityEnum::HIDDEN);
        }

        $this->playerService->persist($this->player);

        // if no charges consume equipment
        $this->gameEquipment->removeLocation();
        $this->gameEquipmentService->delete($this->gameEquipment);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createEquipmentLog(
            ActionEnum::CONSUME,
            $this->player->getRoom(),
            $this->player,
            $this->gameEquipment,
            VisibilityEnum::COVERT,
            new \DateTime('now')
        );
    }
}
