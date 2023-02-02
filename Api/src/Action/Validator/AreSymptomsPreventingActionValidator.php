<?php

namespace Mush\Action\Validator;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Actions\AbstractAction;
use Mush\Disease\Entity\Config\SymptomActivationRequirement;
use Mush\Disease\Enum\SymptomActivationRequirementEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\RoomLog\Entity\LogParameterInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AreSymptomsPreventingActionValidator extends AbstractActionValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$value instanceof AbstractAction) {
            $errorMessage = "AreSymptomsPreventingActionValidator::validate: value must be an instance of AbstractAction";
            $this->logger->error($errorMessage);
            throw new UnexpectedTypeException($value, AbstractAction::class);
        }

        if (!$constraint instanceof AreSymptomsPreventingAction) {
            $errorMessage = "AreSymptomsPreventingActionValidator::validate: constraint must be an instance of AreSymptomsPreventingAction";
            $this->logger->error($errorMessage,
                [
                    'daedalus' => $value->getPlayer()->getDaedalus()->getId(),
                    'player' => $value->getPlayer()->getId(),
                ]
            );
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

            /** @var Collection $symptomActivationRequirements */
            $symptomActivationRequirements = $symptom->getSymptomActivationRequirements();

            if ($symptomActivationRequirements->isEmpty()) {
                $this->context->buildViolation($constraint->message)
                        ->addViolation();
                break;
            }

            foreach ($symptomActivationRequirements as $symptomActivationRequirement) {
                if ($this->isSymptomActivationRequirementMet($symptomActivationRequirement, $parameter)) {
                    $this->context->buildViolation($constraint->message)
                        ->addViolation();
                    break;
                }
            }
        }
    }

    private function isSymptomActivationRequirementMet(SymptomActivationRequirement $symptomActivationRequirement, LogParameterInterface|null $parameter): bool
    {
        switch ($symptomActivationRequirement->getActivationRequirementName()) {
            case SymptomActivationRequirementEnum::ITEM_STATUS:
                if (!$parameter instanceof GameItem) {
                    $errorMessage = "AreSymptomsPreventingActionValidator::isSymptomActivationRequirementMet: parameter must be an instance of GameItem";
                    $this->logger->error($errorMessage);
                    throw new UnexpectedTypeException($parameter, GameItem::class);
                }
                $item = $parameter;

                $activationRequirement = $symptomActivationRequirement->getActivationRequirement();
                if ($activationRequirement === null) {
                    return false;
                }

                return $item->hasStatus($activationRequirement);
            default:
                return false;
        }
    }
}
