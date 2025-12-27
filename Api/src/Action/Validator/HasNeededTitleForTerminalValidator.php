<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Enum\ActionEnum;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Game\Enum\TitleEnum;
use Mush\Skill\Enum\SkillEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasNeededTitleForTerminalValidator extends ConstraintValidator
{
    private static array $terminalTitleMap = [
        EquipmentEnum::COMMAND_TERMINAL => TitleEnum::COMMANDER,
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
        if ($this->usingConspirator($value)) {
            return;
        }
        if ($this->usingMultipass($value)) {
            return;
        }

        $titleNeededToAccess = $constraint->allowAccess;
        $playerHasTitle = $player->hasTitle($titleNeededForTerminal);

        if ($this->shouldBuildViolation($titleNeededToAccess, $playerHasTitle)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function usingConspirator(AbstractAction $value): bool
    {
        $terminal = $value->gameEquipmentTarget();
        $player = $value->getPlayer();

        return $terminal->getName() === EquipmentEnum::BIOS_TERMINAL && $value->getActionName() === ActionEnum::BYPASS_TERMINAL->value;
    }

    private function usingMultipass(AbstractAction $value): bool
    {
        $terminal = $value->gameEquipmentTarget();
        $player = $value->getPlayer();

        return $terminal->getName() === EquipmentEnum::COMMAND_TERMINAL
        && $player->hasAnyOperationalEquipment([ItemEnum::MULTIPASS])
        && $player->hasSkill(SkillEnum::CONCEPTOR);
    }

    private function shouldBuildViolation(bool $titleNeededToAccess, bool $playerHasTitle): bool
    {
        return $titleNeededToAccess && !$playerHasTitle || !$titleNeededToAccess && $playerHasTitle;
    }
}
