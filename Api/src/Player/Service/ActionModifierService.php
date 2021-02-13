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

    public function getAdditiveModifier(Player $player, array $scopes, array $types, ?string $target = null): int
    {
        /** @var int $delta */
        $delta = 0;

        //gear modifiers
        foreach ($this->gearToolService->getApplicableGears($player, $scopes, $types, $target) as $gear) {
            $gearMechanic = $gear->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR);

            if ($gearMechanic) {
                foreach ($gearMechanic->getModifiers() as $modifier) {
                    if (in_array($modifier->getScope(), $scopes) &&
                        ($target === null || $modifier->getTarget() === $target) &&
                        (count($types) || in_array($modifier->getTarget(), $types))
                    ) {
                        $delta += $modifier->getDelta();
                    }
                }
            }
        }

        //@TODO Status modifiers

        //@TODO skill modifiers

        return $delta;
    }

    public function getMultiplicativeModifier(Player $player, array $scopes, array $types, ?string $target = null): int
    {
        /** @var int $delta */
        $delta = 0;

        //gear modifiers
        foreach ($this->gearToolService->getApplicableGears($player, $scopes, $types, $target) as $gear) {
            $gearMechanic = $gear->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR);

            if ($gearMechanic) {
                foreach ($gearMechanic->getModifiers() as $modifier) {
                    if (in_array($modifier->getScope(), $scopes) &&
                        ($target === null || $modifier->getTarget() === $target) &&
                        (count($types) || in_array($modifier->getTarget(), $types))
                    ) {
                        $delta *= $modifier->getDelta();
                    }
                }
            }
        }

        //@TODO Status modifiers

        //@TODO skill modifiers

        return $delta;
    }
}
