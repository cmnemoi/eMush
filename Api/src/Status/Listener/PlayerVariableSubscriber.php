<?php

namespace Mush\Status\Listener;

use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Service\PlayerStatusServiceInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PlayerVariableSubscriber implements EventSubscriberInterface
{
    private PlayerStatusServiceInterface $playerStatus;

    public function __construct(
        PlayerStatusServiceInterface $playerStatus
    ) {
        $this->playerStatus = $playerStatus;
    }

    public static function getSubscribedEvents()
    {
        return [
            VariableEventInterface::CHANGE_VARIABLE => ['onChangeVariable', -10], // Applied after player modification
            VariableEventInterface::CHANGE_VALUE_MAX => ['onChangeVariable', -10], // Applied after player modification
            VariableEventInterface::SET_VALUE => ['onChangeVariable', -10], // Applied after player modification
        ];
    }

    public function onChangeVariable(VariableEventInterface $playerEvent): void
    {
        if (!$playerEvent instanceof PlayerVariableEvent) {
            return;
        }

        $player = $playerEvent->getPlayer();
        $date = $playerEvent->getTime();

        switch ($playerEvent->getVariableName()) {
            case PlayerVariableEnum::MORAL_POINT:
                if (!$player->isMush()) {
                    $this->playerStatus->handleMoralStatus($player, $date);
                }

                return;

            case PlayerVariableEnum::SATIETY:
                $this->playerStatus->handleSatietyStatus($player, $date);

                return;
        }
    }
}
