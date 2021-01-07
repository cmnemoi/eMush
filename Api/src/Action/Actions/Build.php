<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Blueprint;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Service\GameConfigServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\ActionLogEnum;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Build extends AbstractAction
{
    protected string $name = ActionEnum::BUILD;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;
    private GameConfig $gameConfig;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        GameConfigServiceInterface $gameConfigService
    ) {
        parent::__construct($eventDispatcher);

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
        $this->gameConfig = $gameConfigService->getConfig();
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
        /** @var Blueprint $blueprintMechanic */
        $blueprintMechanic = $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT);
        //Check that the equipment is a blueprint and is reachable
        if (
            $blueprintMechanic === null ||
            !$this->player->canReachEquipment($this->gameEquipment)
        ) {
            return false;
        }
        //Check the availlability of the ingredients
        foreach ($blueprintMechanic->getIngredients() as $name => $number) {
            if ($this->player->getReachableEquipmentsByName($name)->count() < $number) {
                return false;
            }
        }

        return true;
    }

    protected function applyEffects(): ActionResult
    {
        /** @var Blueprint $blueprintMechanic */
        $blueprintMechanic = $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::BLUEPRINT);

        // remove the used ingredients starting from the player inventory
        foreach ($blueprintMechanic->getIngredients() as $name => $number) {
            for ($i = 0; $i < $number; ++$i) {
                if ($this->player->hasItemByName($name)) {
                    // @FIXME change to a random choice of the item
                    $ingredient = $this->player->getItems()
                        ->filter(fn (GameItem $gameItem) => $gameItem->getName() === $name)->first();
                    $this->player->removeItem($ingredient);
                } else {
                    // @FIXME change to a random choice of the equipment
                    $ingredient = $this->player->getRoom()->getEquipments()
                        ->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getName() === $name)->first();
                    $ingredient->setRoom(null);
                }

                $ingredient->removeLocation();
                $this->gameEquipmentService->delete($ingredient);
            }
        }

        // remove the blueprint
        $this->gameEquipment
            ->setRoom(null)
        ;

        $this->gameEquipment->removeLocation();
        $this->gameEquipmentService->delete($this->gameEquipment);

        //create the equipment
        $blueprintEquipment = $this->gameEquipmentService->createGameEquipment(
            $blueprintMechanic->getEquipment(),
            $this->player->getDaedalus()
        );
        $equipmentEvent = new EquipmentEvent($blueprintEquipment);
        $equipmentEvent->setPlayer($this->player);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

        $this->gameEquipmentService->persist($blueprintEquipment);

        $this->playerService->persist($this->player);

        $target = new Target($blueprintEquipment->getName(), 'items');

        return new Success(ActionLogEnum::BUILD_SUCCESS, VisibilityEnum::PUBLIC, $target);
    }
}
