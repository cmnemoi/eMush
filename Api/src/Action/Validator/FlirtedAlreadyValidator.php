<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FlirtedAlreadyValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof FlirtedAlready) {
            throw new UnexpectedTypeException($constraint, FlirtedAlready::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof Player) {
            throw new UnexpectedTypeException($parameter, Player::class);
        }

        $player = $value->getPlayer();

        if ($player->HasFlirtedWith($parameter)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
