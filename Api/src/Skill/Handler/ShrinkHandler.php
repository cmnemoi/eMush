<?php

declare(strict_types=1);

namespace Mush\Skill\Handler;

use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Enum\PlayerStatusEnum;

final class ShrinkHandler
{
    public function __construct(private EventServiceInterface $eventService) {}

    public function execute(Place $place): void
    {
        if (!$place->hasAlivePlayerWithSkill(SkillEnum::SHRINK)) {
            return;
        }

        $this->addMoraleToLaidDownNonShrinks($place);
        $this->addMoraleToLaidDownShrinks($place);
    }

    private function addMoraleToLaidDownNonShrinks(Place $place): void
    {
        $shrink = $place->getAlivePlayers()->getOnePlayerWithSkillOrThrow(SkillEnum::SHRINK);

        $this->giveMoraleToLaidDownPlayers(
            players: $place->getAlivePlayers()->getPlayersWithoutSkill(SkillEnum::SHRINK),
            therapist: $shrink
        );
    }

    private function addMoraleToLaidDownShrinks(Place $place): void
    {
        $shrinks = $place->getAlivePlayers()->getPlayersWithSkill(SkillEnum::SHRINK);

        // Need at least 2 shrinks for shrinks to gain moral
        if ($shrinks->count() < 2) {
            return;
        }

        /** @var Player $firstShrink */
        $firstShrink = $shrinks->first();

        /** @var Player $lastShrink */
        $lastShrink = $shrinks->last();

        // The first shrink gives morale to all other shrinks
        $this->giveMoraleToLaidDownPlayers(
            players: $shrinks->getAllExcept($firstShrink),
            therapist: $firstShrink
        );

        // The last shrink gives morale to the first shrink
        $this->giveMoraleToLaidDownPlayers(
            players: new PlayerCollection($shrinks->slice(0, 1)),
            therapist: $lastShrink
        );
    }

    private function giveMoraleToLaidDownPlayers(PlayerCollection $players, Player $therapist): void
    {
        $moraleBonus = $this->calculateMoraleBonus($therapist);

        $players
            ->filter(static fn (Player $patient) => $patient->hasStatus(PlayerStatusEnum::LYING_DOWN))
            ->map(fn (Player $patient) => $this->addMoralePointsToPlayer($moraleBonus, $patient));
    }

    private function calculateMoraleBonus(Player $shrink): int
    {
        return (int) $shrink
            ->getModifiers()
            ->getByModifierNameOrThrow(ModifierNameEnum::SHRINK_MODIFIER)
            ->getVariableModifierConfigOrThrow()
            ->getDelta();
    }

    private function addMoralePointsToPlayer(int $quantity, Player $player): void
    {
        $this->eventService->callEvent(
            event: new PlayerVariableEvent(
                player: $player,
                variableName: PlayerVariableEnum::MORAL_POINT,
                quantity: $quantity,
                tags: [],
                time: new \DateTime(),
            ),
            name: VariableEventInterface::CHANGE_VARIABLE,
        );
    }
}
