<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

class Biting extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::BITING;
    private EventServiceInterface $eventService;
    private RandomServiceInterface $randomService;

    public function __construct(
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
    ) {
        $this->eventService = $eventService;
        $this->randomService = $randomService;
    }

    public function applyEffects(string $symptomName, Player $player, \DateTime $time): void
    {
        $victims = $player->getPlace()->getPlayers()->getPlayerAlive();
        $victims->removeElement($player);

        $playerToBite = $this->randomService->getRandomPlayer($victims);

        $playerModifierEvent = new PlayerVariableEvent(
            $playerToBite,
            PlayerVariableEnum::HEALTH_POINT,
            -1,
            [$symptomName],
            $time
        );

        $this->eventService->callEvent($playerModifierEvent, VariableEventInterface::CHANGE_VARIABLE);
    }
}
