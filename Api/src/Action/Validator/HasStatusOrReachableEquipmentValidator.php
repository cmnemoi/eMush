<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Service\GearToolServiceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class HasStatusOrReachableEquipmentValidator extends ConstraintValidator
{
    private GearToolServiceInterface $gearToolService;

    public function __construct(GearToolServiceInterface $gearToolService)
    {
        $this->gearToolService = $gearToolService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasStatusOrReachableEquipment) {
            throw new UnexpectedTypeException($constraint, HasStatusOrReachableEquipment::class);
        }

        $player = $value->getPlayer();

        if ($player->hasStatus($constraint->status) === false
            && $this->gearToolService->getEquipmentsOnReachByName($player, $constraint->equipmentName)->isEmpty()) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
