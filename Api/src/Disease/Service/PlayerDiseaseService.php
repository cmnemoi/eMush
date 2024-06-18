<?php

namespace Mush\Disease\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\DisorderEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Game\Enum\SkillEnum;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

class PlayerDiseaseService implements PlayerDiseaseServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;
    private EventServiceInterface $eventService;

    public function __construct(
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService,
        EventServiceInterface $eventService
    ) {
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
        $this->eventService = $eventService;
    }

    public function persist(PlayerDisease $playerDisease): PlayerDisease
    {
        $this->entityManager->persist($playerDisease);
        $this->entityManager->flush();

        return $playerDisease;
    }

    public function delete(PlayerDisease $playerDisease): void
    {
        $this->entityManager->remove($playerDisease);
        $this->entityManager->flush();
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
        array $reasons,
        ?int $delayMin = null,
        ?int $delayLength = null
    ): PlayerDisease {
        $diseaseConfig = $this->findDiseaseConfigByNameAndDaedalus($diseaseName, $player->getDaedalus());

        if ($player->isMush() && $diseaseConfig->getType() !== MedicalConditionTypeEnum::INJURY) {
            $dummyDisease = new PlayerDisease();
            $dummyDisease->setPlayer($player);
            $dummyDisease->setDiseaseConfig($diseaseConfig);

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
        $player = $playerDisease->getPlayer();
        if ($player->isMush() && $playerDisease->isAPhysicalDisease()) {
            $this->removePlayerDisease($playerDisease, [DiseaseStatusEnum::MUSH_CURE], $time, VisibilityEnum::HIDDEN);

            return;
        }

        if ($this->diseaseHealsAtCycleChange($playerDisease)) {
            $playerDisease->decrementDiseasePoints();
        }

        if ($playerDisease->isTreatedByAShrink()) {
            $playerRoom = $player->getPlace();
            $this->treatDisorder(
                $playerDisease,
                shrink: $this->randomService->getRandomPlayer($playerRoom->getAliveShrinksExceptPlayer($player)),
                time: $time
            );

            return;
        }

        $diseasePoint = $playerDisease->getDiseasePoint();
        if ($diseasePoint <= 0) {
            if ($playerDisease->getStatus() === DiseaseStatusEnum::INCUBATING) {
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

    private function diseaseHealsAtCycleChange(PlayerDisease $playerDisease): bool
    {
        $spontaneousHealingDisorders = [DisorderEnum::VERTIGO, DisorderEnum::SPLEEN];

        return $playerDisease->getDiseaseConfig()->getType() === MedicalConditionTypeEnum::DISEASE
            || \in_array($playerDisease->getDiseaseConfig()->getDiseaseName(), $spontaneousHealingDisorders, true);
    }

    private function treatDisorder(PlayerDisease $playerDisease, Player $shrink, \DateTime $time): void
    {
        $playerDisease->decrementDiseasePoints();

        // if disorder is cured, remove it
        if ($playerDisease->getDiseasePoint() <= 0) {
            $this->removePlayerDisease(
                $playerDisease,
                [SkillEnum::SHRINK],
                $time,
                VisibilityEnum::PUBLIC,
                $shrink
            );

            return;
        }

        // else, send an event to other modules saying that the disorder is still being treated
        $event = new DiseaseEvent(
            $playerDisease,
            tags: [SkillEnum::SHRINK],
            time: $time,
        );
        $event->setAuthor($shrink);
        $event->setVisibility(VisibilityEnum::PUBLIC);
        $this->eventService->callEvent($event, DiseaseEvent::TREAT_DISEASE);

        $this->persist($playerDisease);
    }
}
