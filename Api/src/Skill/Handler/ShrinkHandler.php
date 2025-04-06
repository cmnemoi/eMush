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
        foreach ($shrinks as $shrink) {
            $this->giveMoraleToLaidDownPlayers(
                players: $shrinks->getAllExcept($shrink),
                therapist: $shrink
            );
        }
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
        // @TODO: Feature toggle for retro-compatibility with old shrink modifier
        // can be removed safely after all Daedaluses created before 2025-04-13 6PM UTC+1 are finished
        if (!$shrink->hasModifierByModifierName(ModifierNameEnum::SHRINK_MODIFIER)) {
            return 1;
        }

        return (int) $shrink
            ->getModifiers()
            ->getModifierByModifierNameOrThrow(ModifierNameEnum::SHRINK_MODIFIER)
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
