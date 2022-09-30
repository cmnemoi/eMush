<?php

namespace Mush\Modifier\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\Action;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierCollection;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Entity\Quantity\ActionCost\ActionCostModifier;
use Mush\Modifier\Entity\Trash\ModifierConfig;
use Mush\Modifier\Enum\ModifierModeEnum;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierScopeEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Modifier\Event\ModifierEvent;
use Mush\Player\Entity\Player;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\Status\Entity\ChargeStatus;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Mush\Game\Service\EventServiceInterface;

class ModifierService implements ModifierServiceInterface
{

    public function persist(Modifier $modifier): Modifier
    {
        $this->entityManager->persist($modifier);
        $this->entityManager->flush();

        return $modifier;
    }

    public function delete(Modifier $modifier): void
    {
        $this->entityManager->remove($modifier);
        $this->entityManager->flush();
    }

    public function createActionCostModifier(
        ModifierHolder $holder,
        string $name,
        int $quantity,
        string $playerVariable,
        string $mode
    ) : ActionCostModifier {
        $modifier = new ActionCostModifier(
            $holder,
            $name,
            $quantity,
            $playerVariable,
            $mode
        );

        $this->persist($modifier);
        return $modifier;
    }

    public function deleteModifier(Modifier $modifier): void {
        $this->delete($modifier);
    }

}
