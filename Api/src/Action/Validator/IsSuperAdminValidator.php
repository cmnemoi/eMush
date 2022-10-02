<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\User\Enum\RoleEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsSuperAdminValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof IsSuperAdmin) {
            throw new UnexpectedTypeException($constraint, IsSuperAdmin::class);
        }

        $player = $value->getPlayer();
        $userRoles = $player->getUser()->getRoles();

        if (!in_array(RoleEnum::SUPER_ADMIN, $userRoles, true)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
