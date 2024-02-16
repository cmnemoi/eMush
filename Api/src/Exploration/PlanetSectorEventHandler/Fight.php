<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Event\VariableEventInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\Status\Enum\PlayerStatusEnum;

final class Fight extends AbstractPlanetSectorEventHandler
{
    public const MANKAROG_STRENGTH = 32;

    public function getName(): string
    {
        return PlanetSectorEvent::FIGHT;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        $creatureStrength = $this->drawEventOutputQuantity($event->getOutputQuantityTable());
        $expeditionStrength = $this->getExpeditionStrength($event);
        $damage = max(0, $creatureStrength - $expeditionStrength);

        $damageWithoutGrenades = $creatureStrength - $this->getExpeditionStrength($event, includeGrenades: false);
        if ($damageWithoutGrenades > 0) {
            $this->removeGrenadesFromFighters($event, $damageWithoutGrenades);
        }

        $logParameters = [
            'creature_strength' => $creatureStrength,
            'expedition_strength' => $expeditionStrength,
            'damage' => $damage,
        ];

        if ($damage === 0) {
            return $this->createExplorationLog($event, $logParameters);
        }

        // if we are fighting a Mankarog, add an event tag to shame the dead players with a special death cause
        if (
            $event->getPlanetSector()->getName() === PlanetSectorEnum::MANKAROG
            || $creatureStrength >= self::MANKAROG_STRENGTH
        ) {
            $event->addTag(EndCauseEnum::MANKAROG);
        }

        $this->inflictDamageToExplorators($event, $damage);

        return $this->createExplorationLog($event, $logParameters);
    }

    private function getExpeditionStrength(PlanetSectorEvent $event, bool $includeGrenades = true): int
    {
        // base strength is the number of explorators present during the fight
        $fighters = $event->getExploration()->getNotLostExplorators();
        $expeditionStrength = $fighters->count();

        // then, add bonus from their weapons
        /** @var Player $fighter */
        foreach ($fighters as $fighter) {
            /** @var ArrayCollection<int, GameItem> $fighterWeapons */
            $fighterWeapons = $fighter->getEquipments()
                ->filter(fn (GameItem $item) => ItemEnum::getWeapons()->contains($item->getName()))
                ->filter(fn (GameItem $item) => $item->isOperational())
                ->filter(fn (GameItem $item) => $item->getName() !== ItemEnum::GRENADE || $includeGrenades)
            ;

            // @TODO: +1 point for blasters if the rebel base Centauri has been contacted
            foreach ($fighterWeapons as $weapon) {
                /** @var ?Weapon $weaponMechanic */
                $weaponMechanic = $weapon->getEquipment()->getMechanicByName(EquipmentMechanicEnum::WEAPON);
                $expeditionStrength += $weaponMechanic?->getExpeditionBonus() ?? 0;
            }

            // If fighter is also a Shooter, add 1 point to the expedition strength if they have a loaded gun
            if (
                $fighter->hasSkill(PlayerStatusEnum::POC_SHOOTER_SKILL)
                && $fighterWeapons->filter(fn (GameItem $weapon) => ItemEnum::getGuns()->contains($weapon->getName()))->count() > 0
            ) {
                ++$expeditionStrength;
            }
        }

        return $expeditionStrength;
    }

    private function removeGrenadesFromFighters(PlanetSectorEvent $event, int $damageWithoutGrenades): void
    {
        $fighters = $event->getExploration()->getNotLostExplorators();
        foreach ($fighters as $fighter) {
            $fighterGrenades = $fighter->getEquipments()->filter(fn (GameItem $item) => $item->getName() === ItemEnum::GRENADE);

            // We are removing grenades from the fighter until we have enough damage to kill the creature
            // or until we run out of grenades
            while ($damageWithoutGrenades > 0 && $fighterGrenades->count() > 0) {
                $grenade = $fighterGrenades->first();

                $damageWithoutGrenades -= $grenade->getEquipment()->getMechanicByName(EquipmentMechanicEnum::WEAPON)->getExpeditionBonus();

                $fighterGrenades->removeElement($grenade);
                $this->entityManager->remove($grenade);
            }
        }

        $this->entityManager->flush();
    }

    private function inflictDamageToExplorators(PlanetSectorEvent $event, int $damage): void
    {
        $fighters = $event->getExploration()->getNotLostExplorators();
        $damages = [];

        // Randomly select a fighter to take the hit for each point of damage
        for ($i = 0; $i < $damage; ++$i) {
            $explorator = $this->randomService->getRandomPlayer($fighters);
            $fighterName = $explorator->getLogName();

            if (!isset($damages[$fighterName])) {
                $damages[$fighterName] = 0;
            }

            ++$damages[$fighterName];
        }

        // Apply the damages for each fighter in a single event to avoid spamming the logs
        foreach ($damages as $fighterName => $damage) {
            $fighter = $fighters->getPlayerByName($fighterName);

            if (!$fighter) {
                throw new \RuntimeException('Fighter not found');
            }

            $playerEvent = new PlayerVariableEvent(
                $fighter,
                PlayerVariableEnum::HEALTH_POINT,
                -$damage,
                $event->getTags(),
                $event->getTime()
            );

            $this->eventService->callEvent($playerEvent, VariableEventInterface::CHANGE_VARIABLE);
        }
    }
}
