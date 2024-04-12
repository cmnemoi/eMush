<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\PlayerVariableEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * This class implements a validator for the `CanHeal` constraint.
 */
class CanHealValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof CanHeal) {
            throw new UnexpectedTypeException($constraint, CanHeal::class);
        }

        $target = $this->getTarget($value, $constraint);

        $roomName = $value->getPlayer()->getPlace()->getName();
        $isMedlabRoom = $roomName === RoomEnum::MEDLAB;

        $this->checkCanTargetBeHealed($constraint, $target, $value->getPlayer(), $isMedlabRoom);
    }

    private function getTarget(AbstractAction $value, CanHeal $constraint): Player
    {
        $target = match ($constraint->target) {
            CanHeal::PARAMETER => $value->getTarget(),
            CanHeal::PLAYER => $value->getPlayer(),
            default => throw new LogicException('unsupported target'),
        };

        if (!$target instanceof Player) {
            throw new UnexpectedTypeException($target, Player::class);
        }

        return $target;
    }

    private function isPlayerMaxHealth(Player $player): bool
    {
        return $player->getVariableByName(PlayerVariableEnum::HEALTH_POINT)->isMax();
    }

    private function isPlayerHealthy(Player $player): bool
    {
        $playerDiseases = $player->getMedicalConditions();

        $playerDiseases = $playerDiseases->getByDiseaseType(MedicalConditionTypeEnum::DISEASE)->getActiveDiseases();

        return $playerDiseases->count() === 0;
    }

    private function checkCanTargetBeHealed(
        CanHeal $constraint,
        Player $targetPlayer,
        Player $player,
        bool $isMedlabRoom
    ): void {
        // if there is no medical supplies on reach
        if (!$isMedlabRoom && !$player->hasEquipmentByName(ToolItemEnum::MEDIKIT)) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }
        // if medlab is used build violation if the target is healthy AND max health
        if (
            $isMedlabRoom
            && $this->isPlayerMaxHealth($targetPlayer)
            && $this->isPlayerHealthy($targetPlayer)
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();

            return;
        }
        // if medikit is used build violation if the target is max health
        if (
            !$isMedlabRoom
            && $this->isPlayerMaxHealth($targetPlayer)
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
