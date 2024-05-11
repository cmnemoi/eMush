<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class HasSkillValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasSkill) {
            throw new UnexpectedTypeException($constraint, HasSkill::class);
        }

        $player = $value->getPlayer();

        if ($player->hasSkill($constraint->skill) === false) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
