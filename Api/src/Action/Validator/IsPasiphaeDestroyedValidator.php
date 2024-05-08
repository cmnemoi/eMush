<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Place\Enum\RoomEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsPasiphaeDestroyedValidator extends ConstraintValidator
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(GameEquipmentServiceInterface $gameEquipmentService)
    {
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof IsPasiphaeDestroyed) {
            throw new UnexpectedTypeException($constraint, IsPasiphaeDestroyed::class);
        }

        $player = $value->getPlayer();
        $pasiphae = $this->gameEquipmentService->findEquipmentByNameAndDaedalus(EquipmentEnum::PASIPHAE, $player->getDaedalus())->first();

        if ($pasiphae instanceof GameEquipment && $player->getPlace()->getName() !== RoomEnum::PASIPHAE) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
