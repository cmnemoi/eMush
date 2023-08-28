<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
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
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof HasAction) {
            throw new UnexpectedTypeException($constraint, HasAction::class);
        }

        $parameter = $value->getParameter();
        $action = $value->getAction();
        $player = $value->getPlayer();

        if (($this->isPlayerAction($parameter, $player, $action) || $this->isParameterAction($parameter, $action))
            && $this->gearToolService->getUsedTool($player, $value->getActionName()) === null
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    /**
     * no parameter and player do not have action.
     */
    private function isPlayerAction(?LogParameterInterface $parameter, Player $player, Action $action): bool
    {
        return $parameter === null && !$player->getSelfActions()->contains($action);
    }

    /**
     * parameter is player but do not have action or
     * parameter is equipment and do not have action.
     */
    private function isParameterAction(?LogParameterInterface $parameter, Action $action): bool
    {
        return ($parameter instanceof Player && !$parameter->getTargetActions()->contains($action))
            || ($parameter instanceof GameEquipment && !$parameter->getActions()->contains($action))
        ;
    }
}
