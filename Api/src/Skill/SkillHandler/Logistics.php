<?php

declare(strict_types=1);

namespace Mush\Skill\SkillHandler;

use Mush\Game\Entity\Collection\EventChain;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\GameModifier;
use Mush\Modifier\ModifierHandler\AbstractModifierHandler;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Skill\Enum\SkillEnum;

final class Logistics extends AbstractModifierHandler
{
    private const int LOGISTICS_BONUS = 1;
    protected string $name = SkillEnum::LOGISTICS_EXPERT->value;

    private RandomServiceInterface $randomService;

    public function __construct(
        RandomServiceInterface $randomService,
    ) {
        $this->randomService = $randomService;
    }

    public function handleEventModifier(
        GameModifier $modifier,
        EventChain $events,
        string $eventName,
        array $tags,
        \DateTime $time
    ): EventChain {
        /** @var Player $player */
        $player = $modifier->getModifierHolder();

        if (!($player instanceof Player) || $player->isAloneInRoom()) {
            return $events;
        }

        $availableTargets = $player->getAlivePlayersInRoomExceptSelf();
        $target = $this->randomService->getRandomPlayer($availableTargets);

        $actionPointEvent = new PlayerVariableEvent(
            $target,
            PlayerVariableEnum::ACTION_POINT,
            self::LOGISTICS_BONUS,
            [$this->name],
            $time
        );
        $actionPointEvent->setAuthor($player);
        $actionPointEvent->setEventName(VariableEventInterface::CHANGE_VARIABLE);

        $events = $events->addEvent($actionPointEvent);

        return $this->addModifierEvent($events, $modifier, $tags, $time);
    }
}
