<?php

namespace Mush\Status\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\ActionCost;
use Mush\Status\Entity\MedicalCondition;

class MedicalConditionCollection extends ArrayCollection
{
    public function applyActionCostModificator(ActionCost $actionCost): ActionCost
    {
        /** @var MedicalCondition $medicalCondition */
        foreach ($this->getIterator() as $medicalCondition) {
            $medicalConditionConfig = $medicalCondition->getMedicalConditionConfig();
            if ($actionCost->getMoralPointCost() > 0) {
                $actionCost->addMoralPointPointCost($medicalConditionConfig->getMoralPointModifier());
            } elseif ($actionCost->getMovementPointCost() > 0) {
                $actionCost->addMovementPointCost($medicalConditionConfig->getMovementPointModifier());
            } else { //If the action do not require moral or movement point then it will require action point
                $actionCost->addActionPointCost($medicalConditionConfig->getActionPointModifier());
            }
        }

        return $actionCost;
    }
}
