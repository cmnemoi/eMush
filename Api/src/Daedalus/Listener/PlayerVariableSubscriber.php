<?php

namespace Mush\Daedalus\Listener;

use Mush\Daedalus\Repository\DaedalusRepositoryInterface;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerVariableSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DaedalusRepositoryInterface $daedalusRepository,
    ) {}

    public static function getSubscribedEvents()
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => 'onChangeVariable',
            VariableEventInterface::SET_VALUE => 'onSetValue',
        ];
    }

    public function onChangeVariable(VariableEventInterface $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        $player = $playerEvent->getPlayer();

        if ($player->isMush() && $playerEvent->getVariableName() === PlayerVariableEnum::SPORE) {
            $change = $playerEvent->getRoundedQuantity();

            $playerEvent->getDaedalusStatistics()->changeSporesCreated($change);
            $this->daedalusRepository->save($playerEvent->getDaedalus());
        }
    }

    public function onSetValue(VariableEventInterface $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        $player = $playerEvent->getPlayer();

        if ($player->isMush() && $playerEvent->getVariableName() === PlayerVariableEnum::SPORE) {
            $newValue = $playerEvent->getRoundedQuantity();
            $variable = $playerEvent->getVariable();

            $delta = $newValue - $variable->getValue();

            $playerEvent->getDaedalusStatistics()->changeSporesCreated($delta);
            $this->daedalusRepository->save($playerEvent->getDaedalus());
        }
    }
}
