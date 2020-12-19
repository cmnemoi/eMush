<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Take extends Action
{
    protected string $name = ActionEnum::TAKE;

    private GameItem $gameItem;

    private RoomLogServiceInterface $roomLogService;
    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        GameConfigServiceInterface $gameConfigService,
        StatusServiceInterface $statusService
    ) {
        parent::__construct($eventDispatcher);

        $this->roomLogService = $roomLogService;
        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->gameConfig = $gameConfigService->getConfig();
        $this->statusService = $statusService;
    }

    public function loadParameters(Player $player, ActionParameters $actionParameters): void
    {
        if (!($item = $actionParameters->getItem())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
        $this->gameItem = $item;
    }

    public function canExecute(): bool
    {
        /** @var ItemConfig $item */
        $item = $this->gameItem->getEquipment();

        return $this->player->getRoom()->getEquipments()->contains($this->gameItem) &&
            $this->player->getItems()->count() < $this->gameConfig->getMaxItemInInventory() &&
            $item->isTakeable()
            ;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var ItemConfig $item */
        $item = $this->gameItem->getEquipment();

        $this->gameItem->setRoom(null);
        $this->gameItem->setPlayer($this->player);

        // add BURDENED status if item is heavy
        if ($item->isHeavy()) {
            $this->statusService->createCorePlayerStatus(PlayerStatusEnum::BURDENED, $this->player);
        }

        $this->gameEquipmentService->persist($this->gameItem);
        $this->playerService->persist($this->player);

        return new Success();
    }

    protected function createLog(ActionResult $actionResult): void
    {
        $this->roomLogService->createEquipmentLog(
            ActionEnum::TAKE,
            $this->player->getRoom(),
            $this->player,
            $this->gameItem,
            VisibilityEnum::PUBLIC,
            new \DateTime('now')
        );
    }
}
