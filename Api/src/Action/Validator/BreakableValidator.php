<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class BreakableValidator extends AbstractActionValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            $errorMessage = "BreakableValidator::validate: value must be an instance of AbstractAction";
            $this->logger->error($errorMessage);
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Breakable) {
            $errorMessage = "BreakableValidator::validate: constraint must be an instance of Breakable";
            $this->logger->error($errorMessage,
                [   
                    'daedalus' => $value->getPlayer()->getDaedalus()->getId(),
                    'player' => $value->getPlayer()->getId(),
                ]
            );
            throw new UnexpectedTypeException($constraint, Breakable::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof GameEquipment) {
            $errorMessage = "BreakableValidator::validate: parameter must be an instance of GameEquipment";
            $this->logger->error($errorMessage,
                [   
                    'daedalus' => $value->getPlayer()->getDaedalus()->getId(),
                    'player' => $value->getPlayer()->getId(),
                    'parameter' => $parameter,
                ]
            );
            throw new UnexpectedTypeException($parameter, GameEquipment::class);
        }

        if (!$parameter->isBreakable()) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
