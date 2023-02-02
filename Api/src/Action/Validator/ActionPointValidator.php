<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ActionPointValidator extends AbstractActionValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            $errorMessage = "ActionPointValidator::validate: value must be an instance of AbstractAction";
            $this->logger->error($errorMessage);
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof ActionPoint) {
            $errorMessage = "ActionPointValidator::validate: constraint must be an instance of ActionPoint";
            $this->logger->error($errorMessage,
                [   
                    'daedalus' => $value->getPlayer()->getDaedalus()->getId(),
                    'player' => $value->getPlayer()->getId(),
                ]
            );
            throw new UnexpectedTypeException($constraint, ActionPoint::class);
        }

        if ($value->getPlayer()->getActionPoint() < $value->getActionPointCost() ||
            $value->getPlayer()->getMoralPoint() < $value->getMoralPointCost()
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
