<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Skill\Enum\SkillEnum;
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

        $player = $value->getPlayer();
        $terminal = $value->gameEquipmentTarget();

        $titleNeededForTerminal = self::$terminalTitleMap[$terminal->getName()] ?? null;
        if ($titleNeededForTerminal === null) {
            return;
        }
        if ($this->shouldBypassTitle($value)) {
            return;
        }

        $titleNeededToAccess = $constraint->allowAccess;
        $playerHasTitle = $player->hasTitle($titleNeededForTerminal);

        if ($this->shouldBuildViolation($titleNeededToAccess, $playerHasTitle)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function shouldBypassTitle(AbstractAction $value): bool
    {
        $terminal = $value->gameEquipmentTarget();
        $player = $value->getPlayer();

        return $terminal->getName() === EquipmentEnum::BIOS_TERMINAL && $player->hasSkill(SkillEnum::BYPASS);
    }

    private function shouldBuildViolation(bool $titleNeededToAccess, bool $playerHasTitle): bool
    {
        return $titleNeededToAccess && !$playerHasTitle || !$titleNeededToAccess && $playerHasTitle;
    }
}
