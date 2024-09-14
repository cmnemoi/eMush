<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Game\Entity\GameVariable;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GameVariableLevelValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof GameVariableLevel) {
            throw new UnexpectedTypeException($constraint, Reach::class);
        }

        $gameVariable = $this->getGameVariable($constraint->target, $constraint->variableName, $value);

        if ($this->checkVariableLevel($gameVariable, $constraint)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }

    private function getGameVariable(string $target, string $variableName, AbstractAction $value): GameVariable
    {
        switch ($target) {
            case GameVariableLevel::DAEDALUS:
                $targetVariables = $value->getPlayer()->getDaedalus()->getGameVariables();

                break;

            case GameVariableLevel::PLAYER:
                $targetVariables = $value->getPlayer()->getGameVariables();

                break;

            case GameVariableLevel::TARGET_PLAYER:
                /** @var Player $targetPlayer */
                $targetPlayer = $value->getTarget();
                $targetVariables = $targetPlayer->getGameVariables();

                break;

            default:
                throw new LogicException('unsupported target');
        }

        return $targetVariables->getVariableByName($variableName);
    }

    private function checkVariableLevel(GameVariable $gameVariable, Constraint $constraint): bool
    {
        switch ($constraint->checkMode) {
            case GameVariableLevel::IS_MAX:
                return $this->checkMaxVariableLevel($gameVariable);

            case GameVariableLevel::IS_MIN:
                return $this->checkMinVariableLevel($gameVariable);

            case GameVariableLevel::IS_IN_RANGE:
                return $this->checkInRangeVariableLevel($gameVariable);

            case GameVariableLevel::EQUALS:
                return $this->checkEqualsVariableLevel($gameVariable, $constraint->value);

            case GameVariableLevel::NOT_EQUALS:
                return $this->checkEqualsVariableLevel($gameVariable, $constraint->value) === false;

            default:
                throw new LogicException('unsupported checkMode');
        }
    }

    private function checkMaxVariableLevel(GameVariable $gameVariable): bool
    {
        if ($gameVariable->getMaxValue() !== null && $gameVariable->getValue() >= $gameVariable->getMaxValue()) {
            return true;
        }

        return false;
    }

    private function checkMinVariableLevel(GameVariable $gameVariable): bool
    {
        if ($gameVariable->getMinValue() !== null && $gameVariable->getValue() <= $gameVariable->getMinValue()) {
            return true;
        }

        return false;
    }

    private function checkInRangeVariableLevel(GameVariable $gameVariable): bool
    {
        if ($gameVariable->getMaxValue() !== null && $gameVariable->getValue() < $gameVariable->getMaxValue()
            && $gameVariable->getMinValue() !== null && $gameVariable->getValue() > $gameVariable->getMinValue()
        ) {
            return true;
        }

        return false;
    }

    private function checkEqualsVariableLevel(GameVariable $gameVariable, int $value): bool
    {
        if ($gameVariable->getValue() === $value) {
            return true;
        }

        return false;
    }
}
