<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameItem;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class FruitToGraftGivesDifferentPlantValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof FruitToGraftGivesDifferentPlant) {
            throw new UnexpectedTypeException($constraint, FruitToGraftGivesDifferentPlant::class);
        }

        $action = $value;

        /** @var GameItem $fruitToGraft */
        $fruitToGraft = $action->getActionProvider();

        /** @var GameItem $plant */
        $plant = $action->getTarget();

        if ($fruitToGraft->getPlantNameOrThrow() === $plant->getName()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
