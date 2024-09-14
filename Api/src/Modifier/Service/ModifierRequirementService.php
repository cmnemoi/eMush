<?php

namespace Mush\Modifier\Service;

use Mush\Modifier\Entity\Collection\ModifierActivationRequirementCollection;
use Mush\Modifier\Entity\Collection\ModifierCollection;
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

            if ($modifier->isProviderActive()) {
                if ($this->checkRequirements($modifier->getModifierConfig()->getModifierActivationRequirements(), $holder)) {
                    $validatedModifiers->add($modifier);
                }
            }
        }

        return $validatedModifiers;
    }

    public function checkRequirements(
        ModifierActivationRequirementCollection $modifierRequirements,
        ModifierHolderInterface $holder
    ): bool {
        /** @var ModifierActivationRequirement $activationRequirement */
        foreach ($modifierRequirements as $activationRequirement) {
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
