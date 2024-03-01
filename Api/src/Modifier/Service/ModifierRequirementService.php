<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;

class ModifierRequirementService implements ModifierRequirementServiceInterface
{
    private ModifierRequirementHandlerServiceInterface $modifierRequirementHandlerService;

    public function __construct(
        ModifierRequirementHandlerServiceInterface $modifierRequirementHandlerService,
    ) {
        $this->modifierRequirementHandlerService = $modifierRequirementHandlerService;
    }

    public function getActiveModifiers(ModifierCollection $modifiers): ModifierCollection
    {
        $validatedModifiers = new ModifierCollection();

        foreach ($modifiers as $modifier) {
            $holder = $modifier->getModifierHolder();
            $chargeStatus = $modifier->getCharge();
            if (
                $chargeStatus === null
                || $chargeStatus->getCharge() > 0
            ) {
                if ($this->checkModifier($modifier->getModifierConfig(), $holder)) {
                    $validatedModifiers->add($modifier);
                }
            }
        }

        return $validatedModifiers;
    }

    public function checkModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder
    ): bool {
        foreach ($modifierConfig->getModifierActivationRequirements() as $activationRequirement) {
            if (!$this->checkActivationRequirement($activationRequirement, $holder)) {
                return false;
            }
        }

        return true;
    }

    private function checkActivationRequirement(ModifierActivationRequirement $activationRequirement, ModifierHolderInterface $holder): bool
    {
        $handler = $this->modifierRequirementHandlerService->getModifierRequirementHandler($activationRequirement);
        if ($handler === null) {
            throw new \LogicException("This modifier requirement Strategy ({$activationRequirement->getActivationRequirementName()}) is not handled");
        }

        return $handler->checkRequirement($activationRequirement, $holder);
    }
}
