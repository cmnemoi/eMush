<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GearToolServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ParameterHasActionValidator extends ConstraintValidator
{
    private GearToolServiceInterface $gearToolService;

    public function __construct(GearToolServiceInterface $gearToolService)
    {
        $this->gearToolService = $gearToolService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        if (!$constraint instanceof ParameterHasAction) {
            throw new UnexpectedTypeException($constraint, ParameterHasAction::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof GameEquipment) {
            throw new UnexpectedTypeException($parameter, GameEquipment::class);
        }

        if (!$parameter->getActions()->contains($value->getAction()) &&
            $this->gearToolService->getUsedTool($value->getPlayer(), $value->getActionName()) === null
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
