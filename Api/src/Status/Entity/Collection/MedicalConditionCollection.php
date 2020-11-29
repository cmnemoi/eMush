<?php

namespace Mush\Status\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\ActionCost;
use Mush\Status\Entity\MedicalCondition;

class MedicalConditionCollection extends ArrayCollection
{
    public function applyActionCostModificator(ActionCost $actionCost): ActionCost
    {
        /**
         * @var MedicalCondition $medicalCondition
         */
        foreach ($this->getIterator() as $medicalCondition) {
            $modifier = $medicalCondition->getMedicalConditionConfig()->getActionModifier();
            if ($actionCost->getMoralPointCost() > 0) {
                $actionCost->addMoralPointPointCost($modifier->getMoralPointModifier());
            } elseif ($actionCost->getMovementPointCost() > 0) {
                $actionCost->addMovementPointCost($modifier->getMovementPointModifier());
            } elseif ($modifier->getMoralPointModifier() > 0) {
                $actionCost->addActionPointCost($modifier->getActionPointModifier());
            }
        }

        return $actionCost;
    }
}
