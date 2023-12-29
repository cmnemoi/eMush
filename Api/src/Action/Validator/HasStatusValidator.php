<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Status\Entity\StatusHolderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasStatusValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasStatus) {
            throw new UnexpectedTypeException($constraint, HasStatus::class);
        }

        // @TODO remove this when rejuvenate is removed for testers
        if ($constraint->bypassIfUserIsAdmin && $value->getPlayer()->getUser()->isAdmin()) {
            return;
        }

        $target = $this->getTarget($value, $constraint);

        $this->checkValidator($constraint, $target);
    }

    private function getTarget(AbstractAction $value, HasStatus $constraint): StatusHolderInterface
    {
        $target = match ($constraint->target) {
            HasStatus::PARAMETER => $value->getTarget(),
            HasStatus::PLAYER => $value->getPlayer(),
            HasStatus::PLAYER_ROOM => $value->getPlayer()->getPlace(),
            HasStatus::DAEDALUS => $value->getPlayer()->getDaedalus(),
            default => throw new LogicException('unsupported target'),
        };

        if (!$target instanceof StatusHolderInterface) {
            throw new UnexpectedTypeException($target, StatusHolderInterface::class);
        }

        return $target;
    }

    private function checkValidator(HasStatus $constraint, StatusHolderInterface $target): void
    {
        if ($constraint->ownerSide && $target->hasStatus($constraint->status) !== $constraint->contain) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
        if (!$constraint->ownerSide && $target->hasTargetingStatus($constraint->status) !== $constraint->contain) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
        if ($constraint->statusTargetName !== null && $target->getStatusByName($constraint->status)?->getTarget()?->getName() !== $constraint->statusTargetName) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
