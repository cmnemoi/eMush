<?php

namespace Mush\Player\Listener;

use Mush\Action\Event\ActionVariableEvent;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerStatisticsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ActionVariableEvent::APPLY_COST => 'onApplyCost',
        ];
    }

    public function onApplyCost(ActionVariableEvent $event): void
    {
        if ($event->getVariableName() !== PlayerVariableEnum::ACTION_POINT) {
            return;
        }

        $player = $event->getAuthorOrThrow();
        $playerStatistics = $player->getPlayerInfo()->getStatistics();
        $cost = $event->getRoundedQuantity();
        $playerStatistics->incrementActionPointsUsed($cost);

        $this->playerRepository->save($player);
    }
}
