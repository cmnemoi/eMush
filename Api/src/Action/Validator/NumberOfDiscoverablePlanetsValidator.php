<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NumberOfDiscoverablePlanetsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof NumberOfDiscoverablePlanets) {
            throw new UnexpectedTypeException($constraint, NumberOfDiscoverablePlanets::class);
        }

        $player = $value->getPlayer();
        $nbDiscoverablePlanets = $player->getPlayerInfo()->getCharacterConfig()->getMaxDiscoverablePlanets();

        if ($player->getPlanets()->count() === $nbDiscoverablePlanets) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
