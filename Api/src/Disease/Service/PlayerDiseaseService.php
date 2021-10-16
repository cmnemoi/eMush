<?php

namespace Mush\Disease\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseCauseEnum;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Event\DiseaseEventInterface;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PlayerDiseaseService implements PlayerDiseaseServiceInterface
{
    private EntityManagerInterface $entityManager;
    private DiseaseConfigRepository $diseaseConfigRepository;
    private RandomServiceInterface $randomService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        DiseaseConfigRepository $diseaseConfigRepository,
        RandomServiceInterface $randomService,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->diseaseConfigRepository = $diseaseConfigRepository;
        $this->randomService = $randomService;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function persist(PlayerDisease $playerDisease): PlayerDisease
    {
        $this->entityManager->persist($playerDisease);
        $this->entityManager->flush();

        return $playerDisease;
    }

    public function removePlayerDisease(PlayerDisease $playerDisease, string $cause, \DateTime $time, Player $author = null): bool
    {
        $playerDisease->setStatus($cause);

        $event = new DiseaseEventInterface(
            $playerDisease,
            $cause,
            $time
        );
        $event->setAuthor($author);
        $this->eventDispatcher->dispatch($event, DiseaseEventInterface::CURE_DISEASE);

        $this->entityManager->remove($playerDisease);
        $this->entityManager->flush();

        return true;
    }

    public function createDiseaseFromName(
        string $diseaseName,
        Player $player,
        string $cause,
        int $delayMin = null,
        int $delayLength = null
    ): PlayerDisease {
        /** @var DiseaseConfig $diseaseConfig */
        $diseaseConfig = $this->diseaseConfigRepository->findOneBy(['name' => $diseaseName, 'gameConfig' => $player->getDaedalus()->getGameConfig()]);

        if ($diseaseConfig === null) {
            throw new \LogicException("{$diseaseName} do not have any disease config for the daedalus {$player->getDaedalus()->getId()}");
        }

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

        $event = new DiseaseEventInterface(
            $disease,
            $cause,
            new \DateTime()
        );
        $this->eventDispatcher->dispatch($event, DiseaseEventInterface::NEW_DISEASE);

        if ($disease->getStatus() === DiseaseStatusEnum::ACTIVE) {
            $this->eventDispatcher->dispatch($event, DiseaseEventInterface::APPEAR_DISEASE);
        }

        return $disease;
    }

    public function handleDiseaseForCause(string $cause, Player $player): void
    {
        $diseaseConfigs = $this->diseaseConfigRepository->findByCauses($cause, $player->getDaedalus());

        if (count($diseaseConfigs) === 0) {
            return;
        }

        $diseaseConfig = current($this->randomService->getRandomElements($diseaseConfigs));

        if ($diseaseConfig !== false) {
            $this->createDiseaseFromName($diseaseConfig->getName(), $player, $cause);
        }
    }

    public function handleNewCycle(PlayerDisease $playerDisease, \DateTime $time): void
    {
        $newDiseasePoint = $playerDisease->getDiseasePoint() - 1;
        $playerDisease->setDiseasePoint($newDiseasePoint);

        if ($newDiseasePoint === 0) {
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

                $event = new DiseaseEventInterface(
                    $playerDisease,
                    DiseaseCauseEnum::INCUBATING_END,
                    $time
                );
                $this->eventDispatcher->dispatch($event, DiseaseEventInterface::APPEAR_DISEASE);
            } else {
                $this->removePlayerDisease($playerDisease, DiseaseStatusEnum::SPONTANEOUS_CURE, $time);
            }
        } else {
            $this->persist($playerDisease);
        }
    }

    public function healDisease(Player $author, PlayerDisease $playerDisease, \DateTime $time): void
    {
        if ($playerDisease->getResistancePoint() === 0) {
            $this->removePlayerDisease($playerDisease, DiseaseStatusEnum::HEALED, $time, $author);
        } else {
            $event = new DiseaseEventInterface(
                $playerDisease,
                DiseaseStatusEnum::HEALED,
                $time
            );
            $event->setAuthor($author);
            $this->eventDispatcher->dispatch($event, DiseaseEventInterface::TREAT_DISEASE);

            $playerDisease->setResistancePoint($playerDisease->getResistancePoint() - 1);
            $this->persist($playerDisease);
        }
    }
}
