<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MushSporeValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        $player = $value->getPlayer();

        /** @var ?ChargeStatus $sporeStatus */
        $sporeStatus = $player->getStatusByName(PlayerStatusEnum::SPORES);

        if ($constraint->threshold > 0) {
            if ($sporeStatus->getCharge() >= $constraint->threshold) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        } else {
            if ($sporeStatus->getCharge() <= $constraint->threshold) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
