<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ReachEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ReachValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        if (!$constraint instanceof Reach) {
            throw new UnexpectedTypeException($constraint, Reach::class);
        }

        $parameter = $value->getParameter();

        switch ($constraint->reach) {
            case ReachEnum::INVENTORY:
                if (!$parameter instanceof GameItem) {
                    throw new UnexpectedTypeException($constraint, GameItem::class);
                }

                if (!$value->getPlayer()->getItems()->contains($parameter)) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                }
                break;
            case ReachEnum::SHELVE:
                if (!$parameter instanceof GameItem) {
                    throw new UnexpectedTypeException($constraint, GameItem::class);
                }

                if (!$value->getPlayer()->getPlace()->getEquipments()->contains($parameter)) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                }
                break;
            case ReachEnum::ROOM:
                if ($parameter instanceof Player) {
                    if ($parameter === $value->getPlayer() ||
                        $parameter->getPlace() !== $value->getPlayer()->getPlace()
                    ) {
                        $this->context->buildViolation($constraint->message)
                            ->addViolation();
                    }
                } elseif ($parameter instanceof GameEquipment) {
                    if (!$value->getPlayer()->canReachEquipment($parameter)) {
                        $this->context->buildViolation($constraint->message)
                            ->addViolation();
                    }
                }
                break;
        }
    }
}
