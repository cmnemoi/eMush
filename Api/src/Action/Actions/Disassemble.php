<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Event\EquipmentEvent;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\RoomLog\Entity\Target;
use Mush\RoomLog\Enum\VisibilityEnum;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Disassemble extends AttemptAction
{
    protected string $name = ActionEnum::DISASSEMBLE;

    private GameEquipment $gameEquipment;

    private GameEquipmentServiceInterface $gameEquipmentService;
    private PlayerServiceInterface $playerService;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        GameEquipmentServiceInterface $gameEquipmentService,
        PlayerServiceInterface $playerService,
        RandomServiceInterface $randomService,
        ActionServiceInterface $actionService
    ) {
        parent::__construct(
            $randomService,
            $eventDispatcher,
            $actionService
        );

        $this->gameEquipmentService = $gameEquipmentService;
        $this->playerService = $playerService;
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
        //Check that the item is reachable
        return $this->gameEquipment->getActions()->contains($this->action) &&
            $this->player->canReachEquipment($this->gameEquipment)
            //@TODO uncomment when skill are ready
            //&&
            //in_array(SkillEnum::TECHNICIAN, $this->player->getSkills())
        ;
    }

    protected function applyEffects(): ActionResult
    {
        $response = $this->makeAttempt();

        if ($response instanceof Success) {
            $this->disasemble();
        }

        $this->playerService->persist($this->player);

        $target = new Target($this->gameEquipment->getName(), 'items');
        $response->setTarget($target);

        return $response;
    }

    private function disasemble(): void
    {
        // add the item produced by disassembling
        foreach ($this->gameEquipment->getEquipment()->getDismountedProducts() as $productString => $number) {
            for ($i = 0; $i < $number; ++$i) {
                $productEquipment = $this
                    ->gameEquipmentService
                    ->createGameEquipmentFromName($productString, $this->player->getDaedalus())
                ;
                $equipmentEvent = new EquipmentEvent($productEquipment, VisibilityEnum::HIDDEN);
                $equipmentEvent->setPlayer($this->player);
                $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_CREATED);

                $this->gameEquipmentService->persist($productEquipment);
            }
        }

        // remove the dismanteled equipment
        $equipmentEvent = new EquipmentEvent($this->gameEquipment, VisibilityEnum::HIDDEN);
        $this->eventDispatcher->dispatch($equipmentEvent, EquipmentEvent::EQUIPMENT_DESTROYED);
    }
}
