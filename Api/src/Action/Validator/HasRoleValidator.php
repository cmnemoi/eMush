<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasRoleValidator extends AbstractActionValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasRole) {
            throw new UnexpectedTypeException($constraint, HasRole::class);
        }

        $player = $value->getPlayer();
        $userRoles = $player->getUser()->getRoles();

        if (array_intersect($constraint->roles, $userRoles) === []) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
