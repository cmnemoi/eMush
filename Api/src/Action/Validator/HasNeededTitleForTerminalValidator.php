<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\TitleEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasNeededTitleForTerminalValidator extends ConstraintValidator
{
    private static array $terminalTitleMap = [
        EquipmentEnum::COMMAND_TERMINAL => TitleEnum::COMMANDER,
        EquipmentEnum::COMMUNICATION_CENTER => TitleEnum::COM_MANAGER,
        EquipmentEnum::BIOS_TERMINAL => TitleEnum::NERON_MANAGER,
    ];

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasNeededTitleForTerminal) {
            throw new UnexpectedTypeException($constraint, HasNeededTitleForTerminal::class);
        }

        $actionTarget = $value->getTarget();

        if (!$actionTarget instanceof GameEquipment) {
            throw new UnexpectedTypeException($actionTarget, GameEquipment::class);
        }

        $titleNeededForTerminal = self::$terminalTitleMap[$actionTarget->getName()] ?? null;
        if ($titleNeededForTerminal === null) {
            return;
        }

        $titleNeededToAccess = $constraint->allowAccess;
        $playerHasTitle = $value->getPlayer()->hasTitle($titleNeededForTerminal);

        if ($titleNeededToAccess && !$playerHasTitle) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }

        if (!$titleNeededToAccess && $playerHasTitle) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
