<?php

namespace Mush\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Status\Enum\SkillPointsEnum;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class PlayerCanAffordSpecialistPointsValidator extends ConstraintValidator
{
    private ActionServiceInterface $actionService;

    public function __construct(ActionServiceInterface $actionService)
    {
        $this->actionService = $actionService;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof PlayerCanAffordSpecialistPoints) {
            throw new UnexpectedTypeException($constraint, PlayerCanAffordSpecialistPoints::class);
        }

        $action = $value;
        $actionTags = $action->getTags();
        $player = $value->getPlayer();
        $skillPointTags = [];

        foreach ($player->getChargedSkillPoints() as $skillPoint) {
            foreach (SkillPointsEnum::getPointsActionTypesFromStatusName($skillPoint->getName())->toArray() as $skillPointTag) {
                $skillPointTags[] = $skillPointTag;
            }
        }
        $skillPointTags = array_unique($skillPointTags);

        $message = $constraint->message;
        if (\count(array_intersect($skillPointTags, $actionTags)) <= 0) {
            $this->context->buildViolation($message)->addViolation();
        }
    }
}
