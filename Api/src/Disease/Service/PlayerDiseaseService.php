<?php

namespace Mush\Disease\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Disease\Entity\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Repository\DiseaseConfigRepository;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Player\Entity\Player;

class PlayerDiseaseService implements PlayerDiseaseServiceInterface
{
    private EntityManagerInterface $entityManager;
    private DiseaseConfigRepository $diseaseConfigRepository;
    private RandomServiceInterface $randomService;

    public function __construct(
        EntityManagerInterface $entityManager,
        DiseaseConfigRepository $diseaseConfigRepository,
        RandomServiceInterface $randomService
    ) {
        $this->entityManager = $entityManager;
        $this->diseaseConfigRepository = $diseaseConfigRepository;
        $this->randomService = $randomService;
    }

    public function persist(PlayerDisease $playerDisease): PlayerDisease
    {
        $this->entityManager->persist($playerDisease);
        $this->entityManager->flush();

        return $playerDisease;
    }

    public function delete(PlayerDisease $playerDisease): bool
    {
        $this->entityManager->remove($playerDisease);
        $this->entityManager->flush();

        return true;
    }

    public function createDiseaseFromName(string $diseaseName, Player $player): PlayerDisease
    {
        /** @var DiseaseConfig $diseaseConfig */
        $diseaseConfig = $this->diseaseConfigRepository->findOneBy(['name' => $diseaseName, 'gameConfig' => $player->getDaedalus()->getGameConfig()]);

        if ($diseaseConfig === null) {
            throw new \LogicException("{$diseaseName} do not have any disease config for the daedalus {$player->getDaedalus()->getId()}");
        }

        $disease = new PlayerDisease();
        $disease
            ->setPlayer($player)
            ->setDiseaseConfig($diseaseConfig)
            ->setDiseasePoint(10) //@TODO
        ;
        $player->addDisease($disease);

        $this->persist($disease);

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
            $disease = new PlayerDisease();
            $disease
                ->setPlayer($player)
                ->setDiseaseConfig($diseaseConfig)
                ->setDiseasePoint(10) //@TODO
            ;
            $player->addDisease($disease);

            $this->persist($disease);
        }
    }
}
