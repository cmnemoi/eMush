<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class DailySporesLimitValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof DailySporesLimit) {
            throw new UnexpectedTypeException($constraint, DailySporesLimit::class);
        }

        $player = $value->getPlayer();

        if ($constraint->target === DailySporesLimit::DAEDALUS && $player->getDaedalus()->getSpores() <= 0) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
        if ($constraint->target === DailySporesLimit::PLAYER) {
            /** @var ChargeStatus $mushStatus */
            $mushStatus = $player->getStatusByName(PlayerStatusEnum::MUSH);

            if (!$mushStatus || !$mushStatus->isCharged()) {
                $this->context->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
