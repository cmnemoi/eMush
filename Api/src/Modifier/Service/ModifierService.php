<?php

namespace Mush\Modifier\Service;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Action\Entity\Action;
use Mush\Action\Entity\ActionParameter;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\DaedalusModifier;
use Mush\Modifier\Entity\EquipmentModifier;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\PlaceModifier;
use Mush\Modifier\Entity\PlayerModifier;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Modifier\Enum\ModifierTargetEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Service\StatusService;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;

class ModifierService implements ModifierServiceInterface
{
    private const ATTEMPT_INCREASE = 1.25;
    private EntityManagerInterface $entityManager;
    private StatusService $statusService;

    public function __construct(
        EntityManagerInterface $entityManager,
        StatusService $statusService
    ) {
        $this->entityManager = $entityManager;
        $this->statusService = $statusService;
    }

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

    public function createModifier(
        ModifierConfig $modifierConfig,
        Daedalus $daedalus,
        ?Place $place,
        ?Player $player,
        ?GameEquipment $gameEquipment,
        ?ChargeStatus $chargeStatus
    ): void {
        switch ($modifierConfig->getReach()) {
            case ModifierReachEnum::DAEDALUS:
                $modifier = new DaedalusModifier();
                $modifier
                    ->setDaedalus($daedalus)
                    ->setModifierConfig($modifierConfig)
                ;
                break;

            case ModifierReachEnum::PLACE:
                if ($place === null) {
                    return;
                }

                $modifier = new PlaceModifier();
                $modifier
                    ->setPlace($place)
                    ->setModifierConfig($modifierConfig)
                ;
                break;

            case ModifierReachEnum::PLAYER:
            case ModifierReachEnum::TARGET_PLAYER:
                if ($player === null) {
                    return;
                }
                $modifier = new PlayerModifier();
                $modifier
                    ->setPlayer($player)
                    ->setModifierConfig($modifierConfig)
                ;

                // no break
            case ModifierReachEnum::EQUIPMENT:
                if ($gameEquipment === null) {
                    return;
                }
                $modifier = new EquipmentModifier();
                $modifier
                    ->setEquipment($gameEquipment)
                    ->setModifierConfig($modifierConfig)
                ;

                // no break
            default:
                throw new \LogicException('this reach is not handled');
        }

        if ($chargeStatus) {
            $modifier->setCharge($chargeStatus);
        }

        $this->persist($modifier);
    }

    private function getModifiedValue(ModifierCollection $modifierCollection, ?float $initValue): int
    {
        if ($initValue === null) {
            return 0;
        }

        $multiplicativeDelta = 1;
        $additiveDelta = 0;

        /** @var Modifier $modifier */
        foreach ($modifierCollection as $modifier) {
            $chargeStatus = $modifier->getCharge();
            if (
                $chargeStatus === null ||
                $chargeStatus->getCharge() !== 0
            ) {
                if ($modifier->getModifierConfig()->isAdditive()) {
                    $additiveDelta += $modifier->getModifierConfig()->getDelta();
                } else {
                    $multiplicativeDelta *= $modifier->getModifierConfig()->getDelta();
                }
            }
        }

        return intval($initValue * $multiplicativeDelta + $additiveDelta);
    }

    private function getActionModifiers(Action $action, Player $player, ?ActionParameter $parameter): ModifierCollection
    {
        $modifiers = new ModifierCollection();

        $scopes = array_merge([$action->getName()], $action->getTypes());

        $modifiers = $modifiers
            ->addModifiers($player->getModifiers()->getScopedModifiers($scopes))
            ->addModifiers($player->getPlace()->getModifiers()->getScopedModifiers($scopes))
            ->addModifiers($player->getDaedalus()->getModifiers()->getScopedModifiers($scopes))
        ;

        if ($parameter instanceof Player) {
            $modifiers = $modifiers->addModifiers($parameter->getModifiers()->getScopedModifiers($scopes));
        } elseif ($parameter instanceof GameEquipment) {
            $modifiers = $modifiers->addModifiers($parameter->getModifiers()->getScopedModifiers($scopes));
        }

        return $modifiers;
    }

    public function getActionModifiedValue(Action $action, Player $player, string $target, ?ActionParameter $parameter, ?int $attemptNumber = null): int
    {
        $modifiers = $this->getActionModifiers($action, $player, $parameter);

        switch ($target) {
            case ModifierTargetEnum::ACTION_POINT:
                return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $action->getActionCost()->getActionPointCost());
            case ModifierTargetEnum::MOVEMENT_POINT:
                return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $action->getActionCost()->getMovementPointCost());
            case ModifierTargetEnum::MORAL_POINT:
                return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $action->getActionCost()->getMoralPointCost());
            case ModifierTargetEnum::PERCENTAGE:
                if ($attemptNumber === null) {
                    throw new InvalidTypeException('number of attempt should be provided');
                }

                $initialValue = $action->getSuccessRate() * (self::ATTEMPT_INCREASE) ** $attemptNumber;

                return $this->getModifiedValue($modifiers->getTargetedModifiers($target), $initialValue);
        }

        throw new \LogicException('This target is not handled');
    }

    public function consumeActionCharges(Action $action, Player $player, ?ActionParameter $parameter): void
    {
        $modifiers = $this->getActionModifiers($action, $player, $parameter);

        foreach ($modifiers as $modifier) {
            if (($charge = $modifier->getCharge()) !== null) {
                $this->statusService->updateCharge($charge, -1);
            }
        }
    }

    public function getEventModifiedValue(Player $player, array $scopes, string $target, int $initValue): int
    {
        $modifiers = new ModifierCollection();
        $modifiers = $modifiers
            ->addModifiers($player->getModifiers()->getScopedModifiers($scopes)->getTargetedModifiers($target))
            ->addModifiers($player->getPlace()->getModifiers()->getScopedModifiers($scopes)->getTargetedModifiers($target))
            ->addModifiers($player->getDaedalus()->getModifiers()->getScopedModifiers($scopes)->getTargetedModifiers($target))
        ;

        $this->consumeEventCharges($modifiers);

        return $this->getModifiedValue($modifiers, $initValue);
    }

    private function consumeEventCharges(Collection $modifiers): void
    {
        foreach ($modifiers as $modifier) {
            if (($charge = $modifier->getCharge()) !== null) {
                $this->statusService->updateCharge($charge, -1);
            }
        }
    }
}
