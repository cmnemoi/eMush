<?php

namespace Mush\Action\Validator;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Actions\AbstractAction;
use Mush\Disease\Entity\Config\SymptomCondition;
use Mush\Disease\Enum\SymptomConditionEnum;
use Mush\Equipment\Entity\GameItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AreSymptomsPreventingActionValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AreSymptomsPreventingAction) {
            throw new UnexpectedTypeException($constraint, AreSymptomsPreventingAction::class);
        }

        $parameter = $value->getParameter();

        $playerActiveSymptoms = $value
            ->getPlayer()
            ->getMedicalConditions()
            ->getActiveDiseases()
            ->getAllSymptoms()
        ;

        foreach ($playerActiveSymptoms as $symptom) {
            if ($symptom->getTrigger() !== $value->getActionName() && !in_array($symptom->getTrigger(), $value->getAction()->getTypes())) {
                continue;
            }

            /** @var Collection $symptomConditions */
            $symptomConditions = $symptom->getSymptomConditions();

            if ($symptomConditions->isEmpty()) {
                $this->context->buildViolation($constraint->message)
                        ->addViolation();
                break;
            }

            foreach ($symptomConditions as $symptomCondition) {
                if ($this->isSymptomConditionMet($symptomCondition, $parameter)) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                    break;
                }
            }
        }
    }

    private function isSymptomConditionMet(SymptomCondition $symptomCondition, $parameter): bool
    {
        switch ($symptomCondition->getName()) {
            case SymptomConditionEnum::ITEM_STATUS:
                if (!$parameter instanceof GameItem) {
                    throw new UnexpectedTypeException($parameter, GameItem::class);
                }
                $item = $parameter;

                $condition = $symptomCondition->getCondition();
                if ($condition === null) {
                    return false;
                }

                return $item->hasStatus($condition);
            default:
                return false;
        }
    }
}
