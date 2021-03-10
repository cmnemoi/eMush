<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class HasActionValidator extends ConstraintValidator
{
    private GearToolServiceInterface $gearToolService;

    public function __construct(GearToolServiceInterface $gearToolService)
    {
        $this->gearToolService = $gearToolService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($constraint, AbstractAction::class);
        }

        if (!$constraint instanceof HasAction) {
            throw new UnexpectedTypeException($constraint, HasAction::class);
        }

        $parameter = $value->getParameter();
        $action = $value->getAction();
        $player = $value->getPlayer();

        if (($parameter === null &&
            $player->getSelfActions()->contains($action)) ||
            (($parameter instanceof Player || $parameter instanceof GameEquipment) &&
                !$parameter->getActions()->contains($action)) &&
            $this->gearToolService->getUsedTool($player, $value->getActionName()) === null
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }
}
