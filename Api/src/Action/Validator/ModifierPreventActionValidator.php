<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Event\ActionEvent;
use Mush\Game\Service\EventServiceInterface;
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

        if (!$constraint instanceof AreSymptomsPreventingAction) {
            throw new UnexpectedTypeException($constraint, AreSymptomsPreventingAction::class);
        }

        $parameter = $value->getParameter();

        $preActionEvent = new ActionEvent($value->getAction(), $value->getPlayer(), $parameter);
        $canTriggerAction = $this->eventService->canEventTrigger($preActionEvent, ActionEvent::PRE_ACTION, false);

        if ($canTriggerAction !== 'true') {
            $this->context->buildViolation($canTriggerAction)
                ->addViolation();
        }
    }
}
