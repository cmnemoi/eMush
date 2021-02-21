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
use Mush\Place\Enum\PlaceTypeEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\Status\Enum\PlayerStatusEnum;
use Mush\Status\Service\StatusServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Drop extends AbstractAction
{
    protected string $name = ActionEnum::DROP;

    /** @var GameItem */
    protected $parameter;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private StatusServiceInterface $statusService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        StatusServiceInterface $statusService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->statusService = $statusService;
    }

    protected function support(?ActionParameter $parameter): bool
    {
        return $parameter instanceof GameItem;
    }

    public function isVisible(): bool
    {
        $itemConfig = $this->parameter->getEquipment();

        return parent::isVisible() &&
            $itemConfig->hasAction(ActionEnum::DROP) &&
            ($itemConfig instanceof ItemConfig) &&
            $this->player->getItems()->contains($this->parameter);
    }

    public function cannotExecuteReason(): ?string
    {
        if ($this->player->getPlace()->getType() !== PlaceTypeEnum::ROOM) {
            return ActionImpossibleCauseEnum::NO_SHELVING_UNIT;
        }

        return parent::cannotExecuteReason();
    }

    protected function applyEffects(): ActionResult
    {
        $this->parameter->setPlace($this->player->getPlace());
        $this->parameter->setPlayer(null);

        // Remove BURDENED status if no other heavy item in the inventory
        if (($burdened = $this->player->getStatusByName(PlayerStatusEnum::BURDENED)) &&
            $this->player->getItems()->filter(function (GameItem $item) {
                /** @var ItemConfig $itemConfig */
                $itemConfig = $item->getEquipment();

                return $itemConfig->isHeavy();
            })->isEmpty()
        ) {
            $this->player->removeStatus($burdened);
        }

        $this->gameEquipmentService->persist($this->parameter);
        $this->playerService->persist($this->player);

        $target = new Target($this->parameter->getName(), 'items');

        return new Success($target);
    }
}
