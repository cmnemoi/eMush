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
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;

final class PlayerDiseaseService implements PlayerDiseaseServiceInterface
{
    public function __construct(
        private D100RollServiceInterface $d100Roll,
        private EventServiceInterface $eventService,
        private RandomServiceInterface $randomService,
        private PlayerDiseaseRepositoryInterface $playerDiseaseRepository,
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
        ?int $delayMin = null,
        ?int $delayLength = null
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
            ->setDiseaseConfig($diseaseConfig);
        $player->addMedicalCondition($disease);

        $delayMin = $delayMin ?? $diseaseConfig->getDelayMin();
        $delayLength = $delayLength ?? $diseaseConfig->getDelayLength();

        if ($delayMin !== 0) {
            $disease->setDiseasePoint($this->randomService->random($delayMin, $delayMin + $delayLength));
            $disease->setStatus(DiseaseStatusEnum::INCUBATING);
        } else {
            $diseaseDurationMin = $diseaseConfig->getDiseasePointMin();
            $disease->setDiseasePoint($this->randomService->random($diseaseDurationMin, $diseaseDurationMin + $diseaseConfig->getDiseasePointLength()));
        }

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

    public function handleNewCycle(PlayerDisease $playerDisease, \DateTime $time): void
    {
        if ($playerDisease->shouldHealSilently()) {
            $this->removePlayerDisease($playerDisease, [DiseaseStatusEnum::MUSH_CURE], $time, VisibilityEnum::HIDDEN);

            return;
        }

        if ($playerDisease->isIncubating()) {
            $playerDisease->decrementDiseasePoints();
        }

        $diseasePoint = $playerDisease->getDiseasePoint();
        if ($diseasePoint <= 0) {
            if ($playerDisease->isIncubating()) {
                $diseaseConfig = $playerDisease->getDiseaseConfig();
                $diseaseDurationMin = $diseaseConfig->getDiseasePointMin();
                $playerDisease
                    ->setStatus(DiseaseStatusEnum::ACTIVE)
                    ->setResistancePoint($diseaseConfig->getResistance())
                    ->setDiseasePoint(
                        $this->randomService->random(
                            $diseaseDurationMin,
                            $diseaseDurationMin + $diseaseConfig->getDiseasePointLength()
                        )
                    );

                $this->persist($playerDisease);
                $this->activateDisease($playerDisease, [DiseaseCauseEnum::INCUBATING_END], $time);
            } else {
                $this->removePlayerDisease($playerDisease, [DiseaseStatusEnum::SPONTANEOUS_CURE], $time, VisibilityEnum::PRIVATE);
            }
        } else {
            $this->persist($playerDisease);
        }
    }

    public function healDisease(Player $author, PlayerDisease $playerDisease, array $reasons, \DateTime $time, string $visibility): void
    {
        if ($playerDisease->getResistancePoint() === 0) {
            $this->removePlayerDisease($playerDisease, $reasons, $time, $visibility, $author);
        } else {
            $event = new DiseaseEvent(
                $playerDisease,
                $reasons,
                $time
            );
            $event->setAuthor($author);
            $event->setVisibility($visibility);
            $this->eventService->callEvent($event, DiseaseEvent::TREAT_DISEASE);

            $playerDisease->setResistancePoint($playerDisease->getResistancePoint() - 1);
            $this->persist($playerDisease);
        }
    }

    public function treatDisorder(PlayerDisease $playerDisease, \DateTime $time): void
    {
        $playerDisease->decrementDiseasePoints();

        $player = $playerDisease->getPlayer();
        $playerRoom = $player->getPlace();
        $shrink = $this->randomService->getRandomPlayer($playerRoom->getAliveShrinksExceptPlayer($player));

        // if disorder is cured, remove it
        if ($playerDisease->getDiseasePoint() <= 0) {
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

        foreach ($diseaseConfig->getOverride() as $diseaseName) {
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
