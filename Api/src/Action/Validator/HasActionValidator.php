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

        $actionTarget = $value->getTarget();
        $action = $value->getAction();
        $player = $value->getPlayer();

        if (($this->isPlayerAction($actionTarget, $player, $action) || $this->isActionSupportAction($actionTarget, $action))
            && $this->gearToolService->getUsedTool($player, $value->getActionName()) === null
        ) {
            $this->context->buildViolation($constraint->message)
                ->addViolation();
        }
    }

    /**
     * no action support and player do not have action.
     */
    private function isPlayerAction(?LogParameterInterface $actionTarget, Player $player, Action $action): bool
    {
        return $actionTarget === null && !$player->getSelfActions()->contains($action);
    }

    /**
     * action support is player but does not have action or
     * action support is equipment and does not have action.
     */
    private function isActionSupportAction(?LogParameterInterface $actionTarget, Action $action): bool
    {
        return ($actionTarget instanceof Player && !$actionTarget->getTargetActions()->contains($action))
            || ($actionTarget instanceof GameEquipment && !$actionTarget->getActions()->contains($action));
    }
}
