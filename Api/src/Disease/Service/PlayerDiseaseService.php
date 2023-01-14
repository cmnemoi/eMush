<?php

namespace Mush\Disease\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\TypeEnum;
use Mush\Disease\Event\DiseaseEvent;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PlayerDiseaseService implements PlayerDiseaseServiceInterface
{
    private EntityManagerInterface $entityManager;
    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->randomService = $randomService;
        $this->eventDispatcher = $eventDispatcher;
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
        string $cause,
        \DateTime $time,
        string $visibility,
        Player $author = null): bool
    {
        $playerDisease->setStatus($cause);

        $event = new DiseaseEvent(
            $playerDisease,
            $cause,
            $time
        );
        $event->setAuthor($author)->setVisibility($visibility);
        $this->eventDispatcher->dispatch($event, DiseaseEvent::CURE_DISEASE);

        $this->delete($playerDisease);

        return true;
    }

    public function createDiseaseFromName(
        string $diseaseName,
        Player $player,
        string $cause,
        int $delayMin = null,
        int $delayLength = null
    ): ?PlayerDisease {
        $diseaseConfig = $this->findDiseaseConfigByNameAndDaedalus($diseaseName, $player->getDaedalus());

        if ($player->isMush() && $diseaseConfig->getType() !== TypeEnum::INJURY) {
            return null;
        }

        if ($player->getMedicalConditionByName($diseaseName) !== null) {
            return null;
        }

        $time = new \DateTime();

        $disease = new PlayerDisease();
        $disease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
        ;
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
            $cause,
            $time
        );
        $this->eventDispatcher->dispatch($event, DiseaseEvent::NEW_DISEASE);

        if ($disease->getStatus() === DiseaseStatusEnum::ACTIVE) {
            $this->activateDisease($disease, $cause, $time);
        }

        return $disease;
    }

    private function findDiseaseConfigByNameAndDaedalus(string $diseaseName, Daedalus $daedalus): DiseaseConfig
    {
        $diseaseConfigs = $daedalus->getGameConfig()->getDiseaseConfigs()->filter(fn (DiseaseConfig $diseaseConfig) => $diseaseConfig->getDiseaseName() === $diseaseName);

        if ($diseaseConfigs->count() !== 1) {
            throw new \Error('there should be exactly 1 diseaseConfig with this name');
        }

        return $diseaseConfigs->first();
    }

    private function activateDisease(PlayerDisease $disease, string $cause, \DateTime $time): void
    {
        $event = new DiseaseEvent(
            $disease,
            $cause,
            $time
        );

        $event->setVisibility(VisibilityEnum::PRIVATE);
        $this->eventDispatcher->dispatch($event, DiseaseEvent::APPEAR_DISEASE);

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
                    DiseaseCauseEnum::OVERRODE,
                    $time,
                    VisibilityEnum::PRIVATE
                );
            }
        }
    }

    public function handleNewCycle(PlayerDisease $playerDisease, \DateTime $time): void
    {
        if ($playerDisease->getPlayer()->isMush() && $playerDisease->getDiseaseConfig()->getType() === TypeEnum::DISEASE) {
            $visibility = ($playerDisease->getStatus() === DiseaseStatusEnum::INCUBATING) ? VisibilityEnum::HIDDEN : VisibilityEnum::PRIVATE;

            $this->removePlayerDisease($playerDisease, DiseaseStatusEnum::MUSH_CURE, $time, $visibility);
        }

        if ($playerDisease->getDiseaseConfig()->getType() === TypeEnum::DISEASE) {
            $newDiseasePoint = $playerDisease->getDiseasePoint() - 1;
            $playerDisease->setDiseasePoint($newDiseasePoint);
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
                    )
                ;

                $this->persist($playerDisease);

                $this->activateDisease($playerDisease, DiseaseCauseEnum::INCUBATING_END, $time);
            } else {
                $this->removePlayerDisease($playerDisease, DiseaseStatusEnum::SPONTANEOUS_CURE, $time, VisibilityEnum::PRIVATE);
            }
        } else {
            $this->persist($playerDisease);
        }
    }

    public function healDisease(Player $author, PlayerDisease $playerDisease, string $reason, \DateTime $time): void
    {
        if ($playerDisease->getResistancePoint() === 0) {
            $this->removePlayerDisease($playerDisease, $reason, $time, VisibilityEnum::PRIVATE, $author);
        } else {
            $event = new DiseaseEvent(
                $playerDisease,
                $reason,
                $time
            );
            $event->setAuthor($author);
            $this->eventDispatcher->dispatch($event, DiseaseEvent::TREAT_DISEASE);

            $playerDisease->setResistancePoint($playerDisease->getResistancePoint() - 1);
            $this->persist($playerDisease);
        }
    }
}
