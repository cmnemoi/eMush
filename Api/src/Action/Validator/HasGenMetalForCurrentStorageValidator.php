<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Actions\GenMetal;
use Mush\Place\Enum\RoomEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class HasGenMetalForCurrentStorageValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasGenMetalForCurrentStorage) {
            throw new UnexpectedTypeException($constraint, HasGenMetalForCurrentStorage::class);
        }

        $player = $value->getPlayer();
        $roomName = $player->getPlace()->getName();

        if (!\in_array($roomName, RoomEnum::getStorages(), true)) {
            return;
        }

        $statusName = GenMetal::genMetalStatusForStorage($roomName);

        if ($player->hasStatus($statusName)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
