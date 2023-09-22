<?php

namespace Mush\Disease\SymptomHandler;

use Mush\Disease\Enum\SymptomEnum;
use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;

class Biting extends AbstractSymptomHandler
{
    protected string $name = SymptomEnum::BITING;
    private RandomServiceInterface $randomService;

    public function __construct(
        RandomServiceInterface $randomService,
    ) {
        $this->randomService = $randomService;
    }

    public function applyEffects(
        Player $player,
        int $priority,
        array $tags,
        \DateTime $time
    ): EventChain {
        $victims = $player->getPlace()->getPlayers()->getPlayerAlive();
        $victims->removeElement($player);

        $playerToBite = $this->randomService->getRandomPlayer($victims);

        $playerModifierEvent = new PlayerVariableEvent(
            $playerToBite,
            PlayerVariableEnum::HEALTH_POINT,
            -1,
            [$this->name],
            $time
        );

        $playerModifierEvent
            ->setPriority($priority)
            ->setEventName(VariableEventInterface::CHANGE_VARIABLE)
        ;

        return new EventChain([$playerModifierEvent]);
    }
}
