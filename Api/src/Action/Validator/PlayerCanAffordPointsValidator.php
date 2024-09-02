<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
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

        $player = $value->getPlayer();
        $message = $constraint->message;
        if (!$this->actionService->playerCanAffordPoints(
            $player,
            $value->getActionConfig(),
            $value->getActionProvider(),
            $value->getTarget(),
            $value->getTags()
        )
        ) {
            if ($player->isMush()) {
                $message = ActionImpossibleCauseEnum::INSUFFICIENT_ACTION_POINT_MUSH;
            }

            $this->context->buildViolation($message)
                ->addViolation();
        }
    }
}
