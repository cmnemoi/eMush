<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Item\Entity\GameItem;
use Mush\Item\Entity\Items\Drug;
use Mush\Item\Enum\ItemTypeEnum;
use Mush\Item\Service\GameItemServiceInterface;
use Mush\Item\Service\ItemEffectServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Status\Enum\ChargeStrategyTypeEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Consume extends Action
{
    protected const NAME = ActionEnum::CONSUME;

    private GameItem $item;

    private RoomLogServiceInterface $roomLogService;
    private GameItemServiceInterface $gameItemService;
    private PlayerServiceInterface $playerService;
    private ItemEffectServiceInterface $itemServiceEffect;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameItemServiceInterface $gameItemService,
        PlayerServiceInterface $playerService,
        ItemEffectServiceInterface $itemServiceEffect,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameItemService = $gameItemService;
        $this->playerService = $playerService;
        $this->itemServiceEffect = $itemServiceEffect;
        $this->statusService = $statusService;
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters)
    {
        if (!$item = $actionParameters->getItem()) {
            throw new \InvalidArgumentException('Invalid item parameter');
        }
        $this->player = $player;
        $this->item = $item;
    }

    public function canExecute(): bool
    {
        return !($this->item->getItem()->getItemType(ItemTypeEnum::DRUG) &&
                $this->player->getStatusByName(PlayerStatusEnum::DRUG_EATEN)) &&
            $this->item->getItem()->hasAction(ActionEnum::CONSUME) &&
            !$this->player->getStatusByName(PlayerStatusEnum::FULL_STOMACH);
    }

    protected function applyEffects(): ActionResult
    {
        $rationType = $this->item->getItem()->getRationsType();

        if (null === $rationType) {
            throw new \Exception('Cannot consume this item');
        }

        $itemEffect = $this->itemServiceEffect->getConsumableEffect($rationType, $this->player->getDaedalus());
        $this->player
            ->addActionPoint($itemEffect->getActionPoint())
            ->addMovementPoint($itemEffect->getMovementPoint())
            ->addHealthPoint($itemEffect->getHealthPoint())
            ->addMoralPoint($itemEffect->getMoralPoint())
        ;

        // If the ration is a drug player get Drug_Eaten status that prevent it from eating another drug this cycle.
        if ($rationType instanceof Drug) {
            $drugEatenStatus = $this->statusService
                ->createChargePlayerStatus(
                    PlayerStatusEnum::DRUG_EATEN,
                    $this->player,
                    ChargeStrategyTypeEnum::CYCLE_DECREMENT,
                    1,
                    null,
                    true
                );
            $drugEatenStatus->setVisibility(VisibilityEnum::HIDDEN);
        }

        $this->playerService->persist($this->player);

        // if no charges consume item
        $this->item->setPlayer(null);
        $this->item->setRoom(null);
        $this->gameItemService->delete($this->item);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createItemLog(
            ActionEnum::CONSUME,
            $this->player->getRoom(),
            $this->item,
            VisibilityEnum::COVERT,
            new \DateTime('now')
        );
    }

    public function getActionName(): string
    {
        return self::NAME;
    }
}
