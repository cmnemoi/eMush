<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Service\GearToolServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UsedToolValidator extends ConstraintValidator
{
    private GearToolServiceInterface $gearToolService;

    public function __construct(GearToolServiceInterface $gearToolService)
    {
        $this->gearToolService = $gearToolService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof UsedTool) {
            throw new UnexpectedTypeException($constraint, UsedTool::class);
        }

        if ($this->gearToolService->getUsedTool($value->getPlayer(), $value->getActionName()) === null) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
