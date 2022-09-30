<?php

namespace Mush\Modifier\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Modifier\Entity\Modifier;
use Mush\Modifier\Entity\ModifierConfig;
use Mush\Modifier\Entity\ModifierHolder;
use Mush\Modifier\Enum\ModifierReachEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;

class ModifierService implements ModifierServiceInterface
{

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
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

    public function createModifier(ModifierConfig $config, ModifierHolder $holder) : Modifier
    {
        $modifier = new Modifier($holder, $config);
        $this->persist($modifier);
        return $modifier;
    }

    public function deleteModifier(Modifier $modifier): void {
        $this->delete($modifier);
    }

    public function getHolderFromConfig(ModifierConfig $config, ModifierHolder $holder, ModifierHolder $target = null) : ModifierHolder {
        $reach = $config->getReach();

        if ($holder instanceof Daedalus) {
            if ($reach === ModifierReachEnum::DAEDALUS) {
                return $holder;
            }
        }

        if ($holder instanceof Place) {
            if ($reach === ModifierReachEnum::DAEDALUS) {
                return $holder->getDaedalus();
            }

            if ($reach === ModifierReachEnum::PLACE) {
                return $holder;
            }
        }

        if ($holder instanceof GameEquipment) {
            return $this->getEquipmentHolder($holder, $reach);
        }

        if ($holder instanceof Player) {
            if ($target !== null) {
                if ($target instanceof Player) {
                    return $this->getPlayerHolder($holder, $target, $reach);
                } else {
                    throw new \LogicException('Target is not a player.');
                }
            } else {
                return $this->getPlayerHolder($holder, null, $reach);
            }
        }

        throw new \LogicException($holder->getClassName() .' can\'t have a ' . $reach . ' reach.');
    }

    private function getEquipmentHolder(GameEquipment $holder, string $reach) : ModifierHolder {
        switch ($reach) {
            case ModifierReachEnum::DAEDALUS:
                return $holder->getPlace()->getDaedalus();

            case ModifierReachEnum::PLACE:
                return $holder->getPlace();

            case ModifierReachEnum::EQUIPMENT:
                return $holder;

            case ModifierReachEnum::PLAYER:
                $player = $holder->getHolder();
                if ($player instanceof Player) {
                    return $player;
                } else {
                    throw new \LogicException('Equipment without a holder have a ' . $reach . ' reach.');
                }

            default:
                throw new \LogicException('Equipment don\'t have a ' . $reach . ' reach.');
        }
    }

    private function getPlayerHolder(Player $holder, Player | null $target, string $reach) : ModifierHolder {
        switch ($reach) {
            case ModifierReachEnum::DAEDALUS:
                return $holder->getPlace()->getDaedalus();

            case ModifierReachEnum::PLACE:
                return $holder->getPlace();

            case ModifierReachEnum::EQUIPMENT:
                throw new \LogicException('Player can\'t have a ' . $reach . ' reach.');

            case ModifierReachEnum::PLAYER:
                return $holder;

            case ModifierReachEnum::TARGET_PLAYER:
                if ($target === null) {
                    throw new \LogicException('Target is null.');
                } else {
                    return $target;
                }

            default:
                throw new \LogicException('Player don\'t have a ' . $reach . ' reach.');
        }
    }

}
