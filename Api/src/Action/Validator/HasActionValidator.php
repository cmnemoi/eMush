<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionConfig;
use Mush\Action\Entity\ActionProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasActionValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasAction) {
            throw new UnexpectedTypeException($constraint, HasAction::class);
        }

        $actionConfig = $value->getActionConfig();
        $actionProvider = $value->getActionProvider();

        $player = $value->getPlayer();


        if (!($this->providerHasActionConfig($actionProvider, $actionConfig)
            && $actionProvider->canPlayerReach($player))
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function providerHasActionConfig(ActionProviderInterface $actionProvider, ActionConfig $actionConfig): bool
    {
        $providerActions = $actionProvider->getProvidedActions($actionConfig->getDisplayHolder(), [$actionConfig->getRange()]);

        return $providerActions->filter(static fn (Action $action) => $action->getActionConfig()->getActionName() === $actionConfig->getActionName())->count() > 0;
    }
}
