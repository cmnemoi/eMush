<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\Mechanics\Document;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ShredableValidator extends AbstractActionValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Shredable) {
            throw new UnexpectedTypeException($constraint, Shredable::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof GameEquipment) {
            throw new UnexpectedTypeException($parameter, GameEquipment::class);
        }

        /** @var Document $document */
        $document = $parameter->getEquipment()->getMechanicByName(EquipmentMechanicEnum::DOCUMENT);

        if (!$document || !$document->canShred()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
