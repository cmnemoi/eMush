<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Fruit;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Transplant extends AbstractAction
{
    protected string $name = ActionEnum::TRANSPLANT;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->actionCost->setActionPointCost(1);
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
        return $this->player->getReachableEquipmentsByName(ItemEnum::HYDROPOT)->count() > 0 &&
                    $this->player->canReachEquipment($this->gameEquipment) &&
                    $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::FRUIT)
                    ;
    }

    protected function applyEffects(): ActionResult
    {
        //@TODO fail transplant
        /** @var Fruit $fruitType */
        $fruitType = $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::FRUIT);

        $hydropot = $this->player->getReachableEquipmentsByName(ItemEnum::HYDROPOT)->first();
        $place = $hydropot->getRoom() ?? $hydropot->getPlayer();

        /** @var GameItem $plantEquipment */
        $plantEquipment = $this->gameEquipmentService
                    ->createGameEquipmentFromName($fruitType->getPlantName(), $this->player->getDaedalus());

        if ($place instanceof Player && $plantEquipment instanceof GameEquipment) {
            $plantEquipment->setPlayer($place);
        } else {
            $plantEquipment->setRoom($place);
        }

        $hydropot->removeLocation();
        $this->gameEquipment->removeLocation();
        $this->gameEquipmentService->delete($hydropot);
        $this->gameEquipmentService->delete($this->gameEquipment);

        $this->gameEquipmentService->persist($plantEquipment);

        $this->playerService->persist($this->player);

        $type = $this->gameEquipment instanceof GameItem ? 'items' : 'equipments';
        $target = new Target($this->gameEquipment->getName(), $type);

        return new Success(ActionLogEnum::TRANSPLANT_SUCCESS, VisibilityEnum::PUBLIC, $target);
    }
}
