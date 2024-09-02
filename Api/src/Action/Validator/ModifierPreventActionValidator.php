<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Enum\ActionImpossibleCauseEnum;
use Mush\Action\Event\ActionEvent;
use Mush\Game\Service\EventServiceInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ModifierPreventActionValidator extends ConstraintValidator
{
    private EventServiceInterface $eventService;

    public function __construct(EventServiceInterface $eventService)
    {
        $this->eventService = $eventService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof ModifierPreventAction) {
            throw new UnexpectedTypeException($constraint, ModifierPreventAction::class);
        }

        $actionTarget = $value->getTarget();

        $preActionEvent = new ActionEvent(
            actionConfig: $value->getActionConfig(),
            actionProvider: $value->getActionProvider(),
            player: $value->getPlayer(),
            tags: $value->getTags(),
            actionTarget: $actionTarget
        );
        $eventCancelReason = $this->eventService->eventCancelReason($preActionEvent, ActionEvent::PRE_ACTION);

        if ($eventCancelReason !== null) {
            $message = $this->getViolationMessage(
                $eventCancelReason,
                $constraint->message
            );

            $this->context->buildViolation($message)->addViolation();
        }
    }

    private function getViolationMessage(
        string $preventedReason,
        string $defaultMessage
    ): string {
        return match ($preventedReason) {
            ModifierNameEnum::CEASEFIRE => ActionImpossibleCauseEnum::CEASEFIRE,
            ModifierNameEnum::MUTE_PREVENT_ACTIONS,
            ModifierNameEnum::PREVENT_SHOOT,
            ModifierNameEnum::PREVENT_PILOTING,
            ModifierNameEnum::PREVENT_PICK_HEAVY,
            ModifierNameEnum::PREVENT_MOVE,
            ModifierNameEnum::PREVENT_ATTACKING => ActionImpossibleCauseEnum::SYMPTOMS_ARE_PREVENTING_ACTION,
            default => $defaultMessage,
        };
    }
}
