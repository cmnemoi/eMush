<?php

namespace Mush\Player\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    public function getActionModifier(Player $player, array $scopes, array $types, ?string $target = null): Collection
    {
        /** @var Collection $actions */
        $modifiers = new ArrayCollection();

        //gear modifiers
        foreach ($this->gearToolService->getApplicableGears($player, $scopes, $types, $target) as $gear) {
            $gearMechanic = $gear->getEquipment()->getMechanicByName(EquipmentMechanicEnum::GEAR);

            if ($gearMechanic) {
                foreach ($gearMechanic->getModifiers() as $modifier) {
                    if (in_array($modifier->getScope(), $scopes) &&
                        ($target === null || $modifier->getTarget() === $target) &&
                        (count($types) || in_array($modifier->getTarget(), $types))
                    ) {
                        $modifiers->add($modifier);
                    }
                }
            }
        }

        //@TODO Status modifiers

        //@TODO skill modifiers

        return $modifiers;
    }
}
