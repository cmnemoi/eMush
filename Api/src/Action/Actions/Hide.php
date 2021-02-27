<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameter;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\ItemConfig;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\VisibilityEnum;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Hide extends AbstractAction
{
    protected string $name = ActionEnum::HIDE;

    /** @var GameItem */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        StatusServiceInterface $statusService,
        PlayerServiceInterface $playerService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->statusService = $statusService;
        $this->playerService = $playerService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public function isVisible(): bool
    {
        /** @var ItemConfig $itemConfig */
        $itemConfig = $this->parameter->getEquipment();

        return parent::isVisible() &&
            $this->parameter->getStatusByName(EquipmentStatusEnum::HIDDEN) === null &&
            $itemConfig->isHideable() &&
            $this->player->canReachEquipment($this->parameter);
    }

    public function cannotExecuteReason(): ?string
    {
        if ($this->player->getPlace()->getType() !== PlaceTypeEnum::ROOM) {
            return ActionImpossibleCauseEnum::NO_SHELVING_UNIT;
        }

        if ($this->player->getDaedalus()->getGameStatus() === GameStatusEnum::STARTING) {
            return ActionImpossibleCauseEnum::PRE_MUSH_RESTRICTED;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        $this->statusService->createCoreStatus(
            EquipmentStatusEnum::HIDDEN,
            $this->parameter,
            $this->player,
            VisibilityEnum::PRIVATE,
        );

        if ($this->parameter->getPlayer()) {
            $this->parameter->setPlayer(null);
            $this->parameter->setPlace($this->player->getPlace());
        }

        $this->gameEquipmentService->persist($this->parameter);
        $this->playerService->persist($this->player);

        $target = new Target($this->parameter->getName(), 'items');

        return new Success($target);
    }
}
