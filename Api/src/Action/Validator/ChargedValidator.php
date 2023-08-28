<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\EquipmentMechanic;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Hunter\Entity\Hunter;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ChargedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof Charged) {
            throw new UnexpectedTypeException($constraint, Charged::class);
        }

        $parameter = $value->getParameter();
        if (!$parameter instanceof GameEquipment) {
            // hack for Shoot hunter action : the parameter is the hunter but we want to check the turret / patrol ship charges
            if ($parameter instanceof Hunter) {
                $parameter = $this->getShootingEquipment($value->getPlayer());
            } else {
                throw new UnexpectedTypeException($parameter, GameEquipment::class);
            }
        }

        /** @var ChargeStatus $chargeStatus */
        $chargeStatus = $this->getChargeStatus($value->getActionName(), $parameter);

        if ($chargeStatus !== null && $chargeStatus->getCharge() <= 0) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    private function getChargeStatus(string $actionName, StatusHolderInterface $holder): ?ChargeStatus
    {
        $charges = $holder->getStatuses()->filter(function (Status $status) use ($actionName) {
            return $status instanceof ChargeStatus
                && $status->hasDischargeStrategy($actionName);
        });

        if ($charges->count() > 0) {
            return $charges->first();
        } elseif ($charges->count() === 0) {
            return null;
        } else {
            throw new LogicException('there should be maximum 1 chargeStatus with this dischargeStrategy on this statusHolder');
        }
    }

    private function getShootingEquipment(Player $player): GameEquipment
    {
        /** @var GameEquipment $shootingEquipment */
        $shootingEquipment = $player->getPlace()->getEquipments()
            ->filter(fn (GameEquipment $shootingEquipment) => !$shootingEquipment instanceof GameItem) // filter items to avoid recover PvP weapons
            ->filter(fn (GameEquipment $shootingEquipment) => $shootingEquipment->getEquipment()->getMechanics()->filter(fn (EquipmentMechanic $mechanic) => $mechanic instanceof Weapon)->count() > 0)
            ->first();

        if (!$shootingEquipment instanceof GameEquipment) {
            throw new \Exception("Shoot hunter action : {$player->getPlace()->getName()} should have a shooting equipment (turret or patrol ship)");
        }

        return $shootingEquipment;
    }
}
