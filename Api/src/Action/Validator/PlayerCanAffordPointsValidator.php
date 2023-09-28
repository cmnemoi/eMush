<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Service\ActionServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PlayerCanAffordPointsValidator extends ConstraintValidator
{
    private ActionServiceInterface $actionService;

    public function __construct(ActionServiceInterface $actionService)
    {
        $this->actionService = $actionService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof PlayerCanAffordPoints) {
            throw new UnexpectedTypeException($constraint, PlayerCanAffordPoints::class);
        }

        if (!$this->actionService->playerCanAffordPoints($value->getPlayer(), $value->getAction(), $value->getSupport())) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
