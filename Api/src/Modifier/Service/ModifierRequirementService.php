<?php

namespace Mush\Modifier\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Collection\ModifierCollection;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Entity\Config\ModifierActivationRequirement;
use Mush\Modifier\Entity\ModifierHolderInterface;
use Mush\Modifier\Enum\ModifierRequirementEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\StatusHolderInterface;

class ModifierRequirementService implements ModifierRequirementServiceInterface
{
    private RandomServiceInterface $randomService;

    public function __construct(
        RandomServiceInterface $randomService,
    ) {
        $this->randomService = $randomService;
    }

    public function getActiveModifiers(ModifierCollection $modifiers): ModifierCollection
    {
        $validatedModifiers = new ModifierCollection();

        foreach ($modifiers as $modifier) {
            $holder = $modifier->getModifierHolder();
            $chargeStatus = $modifier->getCharge();
            if (
                $chargeStatus === null
                || $chargeStatus->getCharge() > 0
            ) {
                if ($this->checkModifier($modifier->getModifierConfig(), $holder)) {
                    $validatedModifiers->add($modifier);
                }
            }
        }

        return $validatedModifiers;
    }

    public function checkModifier(
        AbstractModifierConfig $modifierConfig,
        ModifierHolderInterface $holder
    ): bool {
        foreach ($modifierConfig->getModifierActivationRequirements() as $activationRequirement) {
            if (!$this->checkActivationRequirement($activationRequirement, $holder)) {
                return false;
            }
        }

        return true;
    }

    private function checkActivationRequirement(ModifierActivationRequirement $activationRequirement, ModifierHolderInterface $holder): bool
    {
        switch ($activationRequirement->getActivationRequirementName()) {
            case ModifierRequirementEnum::RANDOM:
                return $this->randomService->isSuccessful(intval($activationRequirement->getValue()));

            case ModifierRequirementEnum::PLAYER_IN_ROOM:
                return $this->handlePlayerInRoomActivationRequirement($activationRequirement, $holder);

            case ModifierRequirementEnum::CYCLE:
                return $this->handleCycleActivationRequirement($activationRequirement, $holder);

            case ModifierRequirementEnum::PLAYER_EQUIPMENT:
                return $this->handlePlayerEquipmentActivationRequirement($activationRequirement, $holder);

            case ModifierRequirementEnum::ITEM_IN_ROOM:
                return $this->handleItemInRoomActivationRequirement($activationRequirement, $holder);

            case ModifierRequirementEnum::STATUS:
                return $this->handleStatusActivationRequirement($activationRequirement, $holder);

            default:
                throw new \LogicException('this activationRequirement is not implemented');
        }
    }

    private function handlePlayerInRoomActivationRequirement(ModifierActivationRequirement $activationRequirement, ModifierHolderInterface $holder): bool
    {
        if ($holder instanceof Place) {
            $room = $holder;
        } elseif ($holder instanceof GameEquipment || $holder instanceof Player) {
            $room = $holder->getPlace();
        } else {
            throw new \LogicException('daedalus cannot be used as holder for a player_in_room activationRequirement');
        }

        $players = $room->getPlayers()->getPlayerAlive();
        switch ($activationRequirement->getActivationRequirement()) {
            case ModifierRequirementEnum::NOT_ALONE:
                return $players->count() >= 2;
            case ModifierRequirementEnum::ALONE:
                return $players->count() === 1;
            case ModifierRequirementEnum::FOUR_PEOPLE:
                return $players->count() >= 4;
            case ModifierRequirementEnum::MUSH_IN_ROOM:
                return $players->filter(fn (Player $player) => $player->isMush())->count() >= 1;

            default:
                throw new \LogicException('This activationRequirement is invalid for player_in_room');
        }
    }

    private function handleCycleActivationRequirement(ModifierActivationRequirement $activationRequirement, ModifierHolderInterface $holder): bool
    {
        if ($holder instanceof Place || $holder instanceof Player) {
            $daedalus = $holder->getDaedalus();
        } elseif ($holder instanceof GameEquipment) {
            $daedalus = $holder->getDaedalus();
        } elseif ($holder instanceof Daedalus) {
            $daedalus = $holder;
        } else {
            throw new \LogicException('This modifierHolder type is not handled');
        }

        switch ($activationRequirement->getActivationRequirement()) {
            case ModifierRequirementEnum::EVEN:
                return $daedalus->getCycle() / 2 === intval($daedalus->getCycle() / 2);

            default:
                throw new \LogicException('This activationRequirement is invalid for cycle');
        }
    }

    private function handlePlayerEquipmentActivationRequirement(ModifierActivationRequirement $activationRequirement, ModifierHolderInterface $holder): bool
    {
        if (!$holder instanceof Player) {
            throw new \LogicException('PLAYER_EQUIPMENT activationRequirement can only be applied on a player');
        }

        /** @var Player $player */
        $player = $holder;

        $expectedItem = $activationRequirement->getActivationRequirement();

        if ($expectedItem === null) {
            throw new \LogicException('provide an item for player_equipment activationRequirement');
        }

        return $player->hasEquipmentByName($expectedItem);
    }

    private function handleItemInRoomActivationRequirement(ModifierActivationRequirement $activationRequirement, ModifierHolderInterface $holder): bool
    {
        if ($holder instanceof Place) {
            $room = $holder;
        } elseif ($holder instanceof Player) {
            $room = $holder->getPlace();
        } else {
            throw new \LogicException('invalid ModifierHolderInterface for item_in_room activationRequirement');
        }

        return $room->getEquipments()->filter(function (GameEquipment $equipment) use ($activationRequirement) {
            return $equipment->getName() === $activationRequirement->getActivationRequirement();
        })->count() > 0;
    }

    private function handleStatusActivationRequirement(ModifierActivationRequirement $activationRequirement, ModifierHolderInterface $holder): bool
    {
        if (!$holder instanceof StatusHolderInterface) {
            throw new \LogicException('STATUS activationRequirement can only be applied on a statusHolder');
        }
        /** @var Player $player */
        $player = $holder;
        $expectedStatus = $activationRequirement->getActivationRequirement();
        if ($expectedStatus === null) {
            throw new \LogicException('provide a status for player_status activationRequirement');
        }

        return $player->hasStatus($expectedStatus);
    }
}
