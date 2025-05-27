<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class DoesNotHaveMageBookSkillValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof DoesNotHaveMageBookSkill) {
            throw new UnexpectedTypeException($constraint, DoesNotHaveMageBookSkill::class);
        }

        $action = $value;
        $mageBookSkill = $action->gameEquipmentTarget()->getBookMechanicOrThrow()->getSkill();
        $player = $action->getPlayer();

        if ($player->cannotLearnSkill($mageBookSkill)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
