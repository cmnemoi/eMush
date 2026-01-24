<?php

declare(strict_types=1);

namespace Mush\Disease\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Disease\Repository\PlayerDiseaseRepositoryInterface;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\Random\D100RollServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Repository\ModifierConfigRepositoryInterface;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;

final class PlayerDiseaseService implements PlayerDiseaseServiceInterface
{
    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private EventServiceInterface $eventService,
        private RandomServiceInterface $randomService,
        private PlayerDiseaseRepositoryInterface $playerDiseaseRepository,
        private ModifierConfigRepositoryInterface $modifierConfigRepository,
    ) {}

    public function persist(PlayerDisease $playerDisease): PlayerDisease
    {
        $this->playerDiseaseRepository->save($playerDisease);

        return $playerDisease;
    }

    public function delete(PlayerDisease $playerDisease): void
    {
        $this->playerDiseaseRepository->delete($playerDisease);
    }

    public function removePlayerDisease(
        PlayerDisease $playerDisease,
        array $causes,
        \DateTime $time,
        string $visibility,
        ?Player $author = null
    ): bool {
        $event = new DiseaseEvent(
            $playerDisease,
            $causes,
            $time
        );
        $event->setVisibility($visibility)->setAuthor($author);
        $this->eventService->callEvent($event, DiseaseEvent::CURE_DISEASE);

        $this->delete($playerDisease);

        return true;
    }

    public function createDiseaseFromName(
        string $diseaseName,
        Player $player,
        array $reasons = [],
        int $delayMin = 0,
        int $delayLength = 0
    ): PlayerDisease {
        $diseaseConfig = $this->findDiseaseConfigByNameAndDaedalus($diseaseName, $player->getDaedalus());

        if ($player->shouldNotCatchDisease($diseaseConfig, $this->d100Roll)) {
            $dummyDisease = new PlayerDisease();
            $dummyDisease
                ->setPlayer($player)
                ->setDiseaseConfig($diseaseConfig);

            return $dummyDisease;
        }

        if (($disease = $player->getMedicalConditionByName($diseaseName)) !== null) {
            return $disease;
        }

        $time = new \DateTime();

        $disease = new PlayerDisease();
        $disease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setHealActionResistance($diseaseConfig->getHealActionResistance());
        $player->addMedicalCondition($disease);

        if ($delayMin !== 0) {
            $disease->setDuration($this->randomService->random($delayMin, $delayMin + $delayLength));
            $disease->setStatus(DiseaseStatusEnum::INCUBATING);
        } else {
            $diseaseDurationMin = $diseaseConfig->getDuration()[0];
            $diseaseDurationMax = $diseaseConfig->getDuration()[1];
            $disease->setDuration($this->randomService->random($diseaseDurationMin, $diseaseDurationMax));
        }

        $this->giveModifierFromConfig($disease);

        $this->persist($disease);

        $event = new DiseaseEvent(
            $disease,
            $reasons,
            $time
        );
        $this->eventService->callEvent($event, DiseaseEvent::NEW_DISEASE);

        if ($disease->getStatus() === DiseaseStatusEnum::ACTIVE) {
            $this->activateDisease($disease, $reasons, $time);
        }

        return $disease;
    }

    public function handleNewCycleForPlayer(Player $player, \DateTime $time)
    {
        // first, decrement a random active disease which heals at cycle change
        if ($player->hasActiveDiseaseHealingAtCycleChange()) {
            $playerDisease = $this->randomService->getRandomElement($player->getActiveDiseasesHealingAtCycleChange()->toArray());
            $playerDisease->decrementDuration();
            $this->persist($playerDisease);
        }

        // then, treat a random disorder by a shrink
        if ($player->hasActiveDisorder() && $player->isLaidDownInShrinkRoom()) {
            $disorder = $this->randomService->getRandomElement($player->getActiveDisorders()->toArray());
            $this->treatDisorder($disorder, $time);
        }

        // finally, handle all player diseases as a whole
        foreach ($player->getMedicalConditions() as $playerDisease) {
            $this->handleNewCycle($playerDisease, $time);
        }
    }

    public function handleNewCycle(PlayerDisease $playerDisease, \DateTime $time): void
    {
        if ($playerDisease->shouldHealSilently()) {
            $this->removePlayerDisease($playerDisease, [DiseaseStatusEnum::MUSH_CURE], $time, VisibilityEnum::HIDDEN);

            return;
        }

        // handle duration if disease is incubating
        if ($playerDisease->isIncubating()) {
            $playerDisease->decrementDuration();
        }

        $diseasePoint = $playerDisease->getDuration();
        if ($diseasePoint <= 0) {
            // activate an incubating disease
            if ($playerDisease->isIncubating()) {
                $diseaseConfig = $playerDisease->getDiseaseConfig();
                $diseaseDurationMin = $diseaseConfig->getDuration()[0];
                $diseaseDurationMax = $diseaseConfig->getDuration()[1];
                $playerDisease
                    ->setStatus(DiseaseStatusEnum::ACTIVE)
                    ->setHealActionResistance($diseaseConfig->getHealActionResistance())
                    ->setDuration(
                        $this->randomService->random(
                            $diseaseDurationMin,
                            $diseaseDurationMax
                        )
                    );

                $this->persist($playerDisease);
                $this->activateDisease($playerDisease, [DiseaseCauseEnum::INCUBATING_END], $time);
            }
            // or heal an active one
            else {
                $this->removePlayerDisease($playerDisease, [DiseaseStatusEnum::SPONTANEOUS_CURE], $time, VisibilityEnum::PRIVATE);
            }
        }

        $this->persist($playerDisease);
    }

    public function healDisease(Player $author, PlayerDisease $playerDisease, array $reasons, \DateTime $time, string $visibility): void
    {
        $playerDisease->decrementHealActionResistance();

        $event = new DiseaseEvent(
            $playerDisease,
            $reasons,
            $time
        );
        $event->setAuthor($author);
        $event->setVisibility($visibility);
        $this->eventService->callEvent($event, DiseaseEvent::TREAT_DISEASE);

        $this->persist($playerDisease);

        if ($playerDisease->getHealActionResistance() <= 0) {
            $this->removePlayerDisease($playerDisease, $reasons, $time, $visibility, $author);
        }
    }

    public function treatDisorder(PlayerDisease $playerDisease, \DateTime $time): void
    {
        $playerDisease->decrementHealActionResistance();

        $player = $playerDisease->getPlayer();
        $playerRoom = $player->getPlace();
        $shrink = $this->randomService->getRandomPlayer($playerRoom->getAliveShrinksExceptPlayer($player));

        // if disorder is cured, remove it
        if ($playerDisease->getHealActionResistance() <= 0) {
            $this->removePlayerDisease(
                $playerDisease,
                [SkillEnum::SHRINK->value],
                $time,
                VisibilityEnum::PUBLIC,
                $shrink
            );

            return;
        }

        // else, send an event to other modules saying that the disorder is still being treated
        $event = new DiseaseEvent(
            $playerDisease,
            tags: [SkillEnum::SHRINK->value],
            time: $time,
        );
        $event->setAuthor($shrink);
        $event->setVisibility(VisibilityEnum::PUBLIC);
        $this->eventService->callEvent($event, DiseaseEvent::TREAT_DISEASE);

        $this->persist($playerDisease);
    }

    public function giveModifierFromConfig(PlayerDisease $playerDisease): void
    {
        $modifierConfigs = [];

        foreach ($playerDisease->getDiseaseConfig()->getModifierConfigs() as $modifierConfigName) {
            /** @var AbstractModifierConfig $modifierConfig */
            $modifierConfig = $this->modifierConfigRepository->findByName($modifierConfigName);
            if ($modifierConfig === null) {
                throw new \Exception('Modifier config not found: ' . $modifierConfigName);
            }
            $modifierConfigs[] = $modifierConfig;
        }
        $playerDisease->setModifierConfigs($modifierConfigs);
    }

    private function findDiseaseConfigByNameAndDaedalus(string $diseaseName, Daedalus $daedalus): DiseaseConfig
    {
        $diseaseConfigs = $daedalus->getGameConfig()->getDiseaseConfig()->filter(static fn (DiseaseConfig $diseaseConfig) => $diseaseConfig->getDiseaseName() === $diseaseName);

        if ($diseaseConfigs->count() !== 1) {
            throw new \Exception("there should be exactly 1 diseaseConfig with this name {$diseaseName}, found {$diseaseConfigs->count()}");
        }

        return $diseaseConfigs->first();
    }

    private function activateDisease(PlayerDisease $disease, array $causes, \DateTime $time): void
    {
        $event = new DiseaseEvent(
            $disease,
            $causes,
            $time
        );

        $event->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventService->callEvent($event, DiseaseEvent::APPEAR_DISEASE);

        $this->removeOverrodeDiseases($disease, $time);
    }

    private function removeOverrodeDiseases(PlayerDisease $disease, \DateTime $time): void
    {
        $player = $disease->getPlayer();
        $diseaseConfig = $disease->getDiseaseConfig();

        foreach ($diseaseConfig->getRemoveLower() as $diseaseName) {
            $overrodeDisease = $player->getMedicalConditionByName($diseaseName);
            if ($overrodeDisease !== null) {
                $this->removePlayerDisease(
                    $overrodeDisease,
                    [DiseaseCauseEnum::OVERRODE],
                    $time,
                    VisibilityEnum::PRIVATE
                );
            }
        }
    }
}
