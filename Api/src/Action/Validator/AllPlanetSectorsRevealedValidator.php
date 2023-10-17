<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Exploration\Entity\Planet;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class AllPlanetSectorsRevealedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AllPlanetSectorsRevealed) {
            throw new UnexpectedTypeException($constraint, AllPlanetSectorsRevealed::class);
        }

        $planet = $value->getTarget();
        if (!$planet instanceof Planet) {
            throw new UnexpectedTypeException($planet, Planet::class);
        }

        if ($planet->getSectors()->count() === $planet->getRevealedSectors()->count()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
