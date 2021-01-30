<?php

namespace Mush\Action\Actions;

use Mush\Action\ActionResult\ActionResult;
use Mush\Action\ActionResult\Success;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ReadDocument extends AbstractAction
{
    protected string $name = ActionEnum::READ_DOCUMENT;

    private GameEquipment $gameEquipment;

    public function __construct(
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct($eventDispatcher);
    }

    public function loadParameters(Action $action, Player $player, ActionParameters $actionParameters): void
    {
        parent::loadParameters($action, $player, $actionParameters);

        if (!($equipment = $actionParameters->getItem()) &&
            !($equipment = $actionParameters->getEquipment())) {
            throw new \InvalidArgumentException('Invalid equipment parameter');
        }

        $this->player = $player;
    }

    public function canExecute(): bool
    {
        return null !== $this->gameEquipment->getEquipment()->getMechanicByName(EquipmentMechanicEnum::DOCUMENT) &&
            $this->player->canReachEquipment($this->gameEquipment)
            ;
    }

    protected function applyEffects(): ActionResult
    {
        return new Success();
    }
}
