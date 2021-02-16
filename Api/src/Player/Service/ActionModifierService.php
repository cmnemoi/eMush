<?php

namespace Mush\Player\Service;

use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Player;

class ActionModifierService implements ActionModifierServiceInterface
{
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        GearToolServiceInterface $gearToolService
    ) {
        $this->gearToolService = $gearToolService;
    }

    public function getGearsModifier(Player $player, array $scopes, ?string $target = null): array
    {
        /** @var int $delta */
        $additiveDelta = 0;
        $multiplicativeDelta = 1;

        //gear modifiers
        foreach ($this->gearToolService->getApplicableGears($player, $scopes, $target) as $gear) {
            $gearMechanic = $gear->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR);

            if ($gearMechanic) {
                foreach ($gearMechanic->getModifiers() as $modifier) {
                    if (in_array($modifier->getScope(), $scopes) &&
                        ($target === null || $modifier->getTarget() === $target)
                    ) {
                        if ($modifier->isAdditive()) {
                            $additiveDelta += $modifier->getDelta();
                        } else {
                            $multiplicativeDelta *= $modifier->getDelta();
                        }
                    }
                }
            }
        }

        return ['additive' => $additiveDelta, 'multiplicative' => $multiplicativeDelta];
    }

    public function getModifiedValue(float $initValue, Player $player, array $scopes, ?string $target = null): int
    {
        /** @var int $delta */
        $additiveDelta = 0;
        $multiplicativeDelta = 1;

        //gear modifiers
        $modifiersDelta = $this->getGearsModifier($player, $scopes, $target);

        $additiveDelta += $modifiersDelta['additive'];
        $multiplicativeDelta *= $modifiersDelta['multiplicative'];

        //@TODO Status modifiers

        //@TODO skill modifiers

        return (int) $initValue * $multiplicativeDelta + $additiveDelta;
    }
}
