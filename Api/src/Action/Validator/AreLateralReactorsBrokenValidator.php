<?php

declare(strict_types=1);

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentEnum;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Symfony\Component\HttpFoundation\File\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class AreLateralReactorsBrokenValidator extends ConstraintValidator
{
    private GameEquipmentServiceInterface $gameEquipmentService;

    public function __construct(GameEquipmentServiceInterface $gameEquipmentService)
    {
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AreLateralReactorsBroken) {
            throw new UnexpectedTypeException($constraint, AreLateralReactorsBroken::class);
        }

        /** @var false|GameEquipment $alphaLateralReactor */
        $alphaLateralReactor = $this->gameEquipmentService->findByNameAndDaedalus(
            EquipmentEnum::REACTOR_LATERAL_ALPHA,
            $value->getPlayer()->getDaedalus(),
        )->first();
        /** @var false|GameEquipment $bravoLateralReactor */
        $bravoLateralReactor = $this->gameEquipmentService->findByNameAndDaedalus(
            EquipmentEnum::REACTOR_LATERAL_BRAVO,
            $value->getPlayer()->getDaedalus(),
        )->first();

        if ($alphaLateralReactor instanceof GameEquipment && $alphaLateralReactor->isBroken()
            && $bravoLateralReactor instanceof GameEquipment && $bravoLateralReactor->isBroken()
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
