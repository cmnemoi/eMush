<?php

namespace Mush\Status\Entity\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Action\Entity\ActionCost;
use Mush\Status\Entity\Affliction;

class AfflictionCollection extends ArrayCollection
{
    public function applyActionCostModificator(ActionCost $actionCost): ActionCost
    {
        /** @var Affliction $affliction */
        foreach ($this->getIterator() as $affliction) {
            $afflictionConfig = $affliction->getAfflictionConfig();
            if ($actionCost->getMoralPointCost() > 0) {
                $actionCost->addMoralPointPointCost($afflictionConfig->getMoralPointModifier());
            } elseif ($actionCost->getMovementPointCost() > 0) {
                $actionCost->addMovementPointCost($afflictionConfig->getMovementPointModifier());
            } else { //If the action do not require moral or movement point then it will require action point
                $actionCost->addActionPointCost($afflictionConfig->getActionPointModifier());
            }
        }

        return $actionCost;
    }
}