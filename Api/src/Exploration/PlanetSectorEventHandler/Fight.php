<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Service\DiseaseCauseServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Entity\Mechanics\Weapon;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Equipment\Service\DeleteEquipmentServiceInterface;
use Mush\Equipment\Service\GameEquipmentServiceInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Enum\PlanetSectorEventTagEnum;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Event\VariableEventInterface;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Mush\Player\Enum\PlayerVariableEnum;
use Mush\Player\Event\PlayerVariableEvent;
use Mush\RoomLog\Enum\LogEnum;
use Mush\RoomLog\Service\RoomLogServiceInterface;
use Mush\Skill\Enum\SkillEnum;

final class Fight extends AbstractLootItemsEventHandler
{
    public const int MANKAROG_STRENGTH = 32;

    private const int DISEASE_CHANCE = 5;

    private DiseaseCauseServiceInterface $diseaseCauseService;
    private RoomLogServiceInterface $roomLogService;
    private DeleteEquipmentServiceInterface $deleteEquipmentService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService,
        DeleteEquipmentServiceInterface $deleteEquipmentService,
        DiseaseCauseServiceInterface $diseaseCauseService,
        RoomLogServiceInterface $roomLogService,
        GameEquipmentServiceInterface $gameEquipmentService,
    ) {
        parent::__construct($entityManager, $eventService, $randomService, $translationService, $gameEquipmentService);
        $this->deleteEquipmentService = $deleteEquipmentService;
        $this->diseaseCauseService = $diseaseCauseService;
        $this->roomLogService = $roomLogService;
    }

    public function getName(): string
    {
        return PlanetSectorEvent::FIGHT;
    }

    public function handle(PlanetSectorEvent $event): ExplorationLog
    {
        // we get the values that we will use
        $creatureStrength = (int) $event->getConfig()->hasTag(PlanetSectorEventTagEnum::RANDOM_FIGHT)
            ? $this->randomService->getRandomElement(PlanetSectorEventTagEnum::getRandomFightPower())
            : $event->getConfig()->getFightStrength();

        $expeditionStrength = $this->getExpeditionStrength($event);
        $damage = max(0, $creatureStrength - $expeditionStrength);

        // we handle grenade here
        $damageWithoutGrenades = $creatureStrength - $this->getExpeditionStrength($event, includeGrenades: false);
        if ($damageWithoutGrenades > 0) {
            $this->removeGrenadesFromFighters($event, $damageWithoutGrenades);
        }

        // we get the % to win
        $winChance = $this->getWinChance($creatureStrength, $expeditionStrength);

        // if we are fighting a Mankarog, add an event tag to shame the dead players with a special death cause
        if ($creatureStrength >= self::MANKAROG_STRENGTH) {
            $event->addTag(EndCauseEnum::MANKAROG);
        }

        // we handle damage and disease
        $this->inflictDamageToExplorators($event, $damage);
        $this->giveDiseaseToExplorators($event);

        // handle win and loss
        if ($this->randomService->isSuccessful($winChance)) {
            // make the items in the case the crew win
            $rewards = $this->createRandomItemsFromEvent($event);
            $logParameters = $this->getLogParameters($event);

            $event->addTag(PlanetSectorEvent::FIGHT_WON);

            $logParameters['result'] = 'victory';
            $logParameters['reward'] = $this->getRewardName($event->getReward(), $event->getDaedalus()->getLanguage(), $rewards->count() > 1);
            $logParameters['reward_amount'] = $rewards->count();
        } else {
            $logParameters = $this->getLogParameters($event);
            $logParameters['result'] = 'loss';
        }

        // get the last few parameters
        $logParameters['fight'] = $this->getFightDescription($event->getPlanetSector()->getName(), $winChance, $logParameters['result'], $event->getDaedalus()->getLanguage());
        $logParameters['creature_strength'] = $creatureStrength;
        $logParameters['expedition_strength'] = $expeditionStrength;
        $logParameters['damage'] = $damage;

        return $this->createExplorationLog($event, $logParameters);
    }

    private function getExpeditionStrength(PlanetSectorEvent $event, bool $includeGrenades = true): int
    {
        // base strength is the number of explorators present during the fight
        $fighters = $event->getExploration()->getNotLostActiveExplorators();
        $expeditionStrength = $fighters->count();

        // then, add bonus from their weapons
        /** @var Player $fighter */
        foreach ($fighters as $fighter) {
            /** @var ArrayCollection<int, GameItem> $fighterWeapons */
            $fighterWeapons = $fighter
                ->getOperationalEquipmentsByNames(ItemEnum::getWeapons()->toArray())
                ->filter(static fn (GameEquipment $item) => $item->getName() !== ItemEnum::GRENADE || $includeGrenades);

            foreach ($fighterWeapons as $weapon) {
                /** @var ?Weapon $weaponMechanic */
                $weaponMechanic = $weapon->getEquipment()->getMechanicByName(EquipmentMechanicEnum::WEAPON);
                $expeditionStrength += $weaponMechanic?->getExpeditionBonus() ?? 0;
                if (\in_array($weapon->getName(), ItemEnum::getBlasters()->toArray(), true)
                && $weapon->getDaedalus()->hasModifierByModifierName(ModifierNameEnum::CENTAURI_REBEL_BASE_MODIFIER)) {
                    $expeditionStrength += (int) $weapon->getDaedalus()->getModifiers()->getByModifierNameOrThrow(ModifierNameEnum::CENTAURI_REBEL_BASE_MODIFIER)->getVariableModifierConfigOrThrow()->getDelta();
                }
            }

            // If fighter is also a Shooter, add 1 point to the expedition strength if they have a loaded gun
            if (
                $fighter->hasSkill(SkillEnum::SHOOTER)
                && $fighterWeapons->filter(static fn (GameItem $weapon) => ItemEnum::getGuns()->contains($weapon->getName()))->count() > 0
            ) {
                ++$expeditionStrength;
            }
        }

        return $expeditionStrength;
    }

    private function removeGrenadesFromFighters(PlanetSectorEvent $event, int $damageWithoutGrenades): void
    {
        $fighters = $event->getExploration()->getNotLostActiveExplorators();
        foreach ($fighters as $fighter) {
            $fighterGrenades = $fighter->getEquipmentsByNames([ItemEnum::GRENADE]);

            // We are removing grenades from the fighter until we have enough damage to kill the creature
            // or until we run out of grenades
            while ($damageWithoutGrenades > 0 && $fighterGrenades->count() > 0) {
                /** @var GameEquipment $grenade */
                $grenade = $fighterGrenades->first();

                /** @var Weapon $mechanic */
                $mechanic = $grenade->getEquipment()->getMechanicByNameOrThrow(EquipmentMechanicEnum::WEAPON);

                $damageWithoutGrenades -= $mechanic->getExpeditionBonus();
                $fighterGrenades->removeElement($grenade);
                $this->deleteEquipmentService->execute(
                    gameEquipment: $grenade,
                    tags: $event->getTags()
                );
            }
        }
    }

    private function inflictDamageToExplorators(PlanetSectorEvent $event, int $damage): void
    {
        $fighters = $event->getExploration()->getNotLostActiveExplorators();
        $damages = [];

        // Randomly select a fighter to take the hit for each point of damage
        for ($i = 0; $i < $damage; ++$i) {
            $explorator = $this->randomService->getRandomPlayer($fighters);
            $fighterName = $explorator->getName();

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

    private function giveDiseaseToExplorators(PlanetSectorEvent $event): void
    {
        $fighters = $event->getExploration()->getNotLostActiveExplorators();

        /** @var Player $explorator */
        foreach ($fighters as $explorator) {
            if ($this->randomService->isSuccessful(self::DISEASE_CHANCE)) {
                $disease = $this->diseaseCauseService->handleDiseaseForCause(DiseaseCauseEnum::ALIEN_FIGHT, $explorator, 0, 0, $event->getTime());
                $this->roomLogService->createLog(
                    LogEnum::DISEASE_BY_ALIEN_FIGHT,
                    $explorator->getPlace(),
                    VisibilityEnum::PRIVATE,
                    'event_log',
                    $explorator,
                    [
                        'disease' => $disease->getName(),
                        'is_player_mush' => $explorator->isMush() ? 'true' : 'false',
                    ],
                    $event->getTime()
                );
            }
        }
    }

    private function getWinChance(int $creatureStrength, int $expeditionStrength): int
    {
        $threshold = $creatureStrength / 2;

        if ($expeditionStrength < $threshold) {
            return 0;
        }

        // % is zero if less than half the creature strength and increase linearly until it reach 100 when reaching the creature strength
        return (int) floor(min(max($expeditionStrength - $threshold + 1, 0) / ($creatureStrength - $threshold + 1), 1) * 100);
    }

    private function getRewardName(string $name, string $language, bool $plural): string
    {
        return $this->translationService->translate(
            $plural ? $name . '.plural_name' : $name . '.name',
            [],
            'items',
            $language
        );
    }

    private function getFightDescription(string $sectorName, int $winChance, string $result, string $language): string
    {
        $suffix = match (true) {
            $winChance === 0 => 'low',
            $winChance === 100 => 'high',
            default => 'mid'
        };

        return $this->translationService->translate(
            'fight.' . $sectorName . '_' . $suffix,
            ['result' => $result],
            'planet_sector_event',
            $language
        );
    }
}
