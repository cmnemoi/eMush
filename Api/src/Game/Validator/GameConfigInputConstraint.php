<?php

namespace Mush\Game\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

class GameConfigInputConstraint extends Constraint implements InputConstraintInterface
{
    public string $message = 'The input "{{ value }}" contains invalid values.';

    final public function validatedBy(): string
    {
        return GroupValidator::class;
    }

    public function getConstraints(): Constraint
    {
        return new Assert\Collection([
            'name' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Length(['max' => 48]),
                new Assert\Type(['string']),
            ]),
            'nbMush' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\PositiveOrZero(),
            ]),
            'cyclePerGameDay' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\Positive(),
            ]),
            'cycleLength' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\Positive(),
            ]),
            'timeZone' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['string']),
            ]),
            'language' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['string']),
            ]),
            'maxNumberPrivateChannel' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\PositiveOrZero(),
            ]),
            'initHealthPoint' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\Positive(),
            ]),
            'maxHealthPoint' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\Positive(),
            ]),
            'initMoralPoint' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\Positive(),
            ]),
            'maxMoralPoint' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\Positive(),
            ]),
            'initSatiety' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
            ]),
            'initActionPoint' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\PositiveOrZero(),
            ]),
            'maxActionPoint' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\Positive(),
            ]),
            'initMovementPoint' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\PositiveOrZero(),
            ]),
            'maxMovementPoint' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\Positive(),
            ]),
            'maxItemInInventory' => new Assert\Sequentially([
                new Assert\NotBlank(),
                new Assert\Type(['integer']),
                new Assert\PositiveOrZero(),
            ]),
        ], null, null, true);
    }
}
