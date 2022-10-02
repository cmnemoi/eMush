<?php

namespace Mush\Player\Listener;

use Mush\Disease\Event\DiseaseEvent;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Service\PlayerServiceInterface;
use Mush\Player\Service\PlayerVariableServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DiseaseSubscriber implements EventSubscriberInterface
{
    private PlayerServiceInterface $playerService;
    private PlayerVariableServiceInterface $playerVariableService;

    public function __construct(
        PlayerServiceInterface $playerService,
        PlayerVariableServiceInterface $playerVariableService,
    ) {
        $this->playerService = $playerService;
        $this->playerVariableService = $playerVariableService;
    }

    public static function getSubscribedEvents()
    {
        return [
            DiseaseEvent::APPEAR_DISEASE => ['onDiseaseAppear', -10], // apply after the modifiers had been applied
        ];
    }

    public function onDiseaseAppear(DiseaseEvent $event): void
    {
        $player = $event->getPlayer();

        foreach (PlayerVariableEnum::getCappedPlayerVariables() as $variableName) {
            $maxAmount = $this->playerVariableService->getMaxPlayerVariable($player, $variableName);

            if ($player->getVariableFromName($variableName) > $maxAmount) {
                $player->setVariableFromName($variableName, $maxAmount);
                $this->playerService->persist($player);
            }
        }
    }
}
