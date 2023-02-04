<?php

namespace Mush\Game\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Entity\TriumphConfig;
use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Repository\GameConfigRepository;
use Mush\Game\Repository\TriumphConfigRepository;

class TriumphConfigDataLoader extends ConfigDataLoader
{
    private EntityManagerInterface $entityManager;
    private GameConfigRepository $gameConfigRepository;
    private TriumphConfigRepository $triumphConfigRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameConfigRepository $gameConfigRepository,
        TriumphConfigRepository $triumphConfigRepository)
    {
        $this->entityManager = $entityManager;
        $this->gameConfigRepository = $gameConfigRepository;
        $this->triumphConfigRepository = $triumphConfigRepository;
    }

    public function loadConfigData(): void
    {
        $triumphDataArray = $this->getTriumphData();

        /** @var GameConfig $defaultGameConfig */
        $defaultGameConfig = $this->gameConfigRepository->findOneBy(['name' => 'default']);
        if ($defaultGameConfig == null) {
            throw new \Exception('Default game config not found');
        }

        foreach ($triumphDataArray as $triumphData) {
            $triumphConfig = $this->triumphConfigRepository->findOneBy(['name' => $triumphData['name']]);

            if ($triumphConfig == null) {
                $triumphConfig = new TriumphConfig();
                $triumphConfig
                    ->setName($triumphData['name'])
                    ->setTriumph($triumphData['triumph'])
                    ->setIsAllCrew($triumphData['is_all_crew'])
                    ->setTeam($triumphData['team'])
                ;

                $this->entityManager->persist($triumphConfig);
                if (!$defaultGameConfig->getTriumphConfig()->contains($triumphConfig)) {
                    $defaultGameConfig->addTriumphConfig($triumphConfig);
                }
            }
        }
        $this->entityManager->flush();
    }

    private function getTriumphData(): array
    {
        return [
            ['name' => 'alien_science', 'triumph' => 16, 'is_all_crew' => false, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'expedition', 'triumph' => 3, 'is_all_crew' => false, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'super_nova', 'triumph' => 20, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'first_starmap', 'triumph' => 6, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'next_starmap', 'triumph' => 1, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'cycle_mush', 'triumph' => -2, 'is_all_crew' => true, 'team' => VisibilityEnum::MUSH],
            ['name' => 'starting_mush', 'triumph' => 120, 'is_all_crew' => false, 'team' => VisibilityEnum::MUSH],
            ['name' => 'cycle_mush_late', 'triumph' => -3, 'is_all_crew' => false, 'team' => VisibilityEnum::MUSH],
            ['name' => 'conversion', 'triumph' => 8, 'is_all_crew' => false, 'team' => VisibilityEnum::MUSH],
            ['name' => 'infection', 'triumph' => 1, 'is_all_crew' => false, 'team' => VisibilityEnum::MUSH],
            ['name' => 'humanocide', 'triumph' => 3, 'is_all_crew' => false, 'team' => VisibilityEnum::MUSH],
            ['name' => 'chun_dead', 'triumph' => 7, 'is_all_crew' => true, 'team' => VisibilityEnum::MUSH],
            ['name' => 'sol_return_mush', 'triumph' => 8, 'is_all_crew' => true, 'team' => VisibilityEnum::MUSH],
            ['name' => 'eden_mush', 'triumph' => 32, 'is_all_crew' => true, 'team' => VisibilityEnum::MUSH],
            ['name' => 'cycle_human', 'triumph' => 1, 'is_all_crew' => false, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'cycle_inactive', 'triumph' => 0, 'is_all_crew' => false, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'new_planet_orbit', 'triumph' => 5, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'sol_contact', 'triumph' => 8, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'small_research', 'triumph' => 3, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'standard_research', 'triumph' => 6, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'brilliant_research', 'triumph' => 16, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'sol_return', 'triumph' => 20, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'sol_mush_intruder', 'triumph' => -10, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'hunter_killed', 'triumph' => 1, 'is_all_crew' => false, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'mushicide', 'triumph' => 3, 'is_all_crew' => false, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'rebel_wolf', 'triumph' => 8, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'nice_surgery', 'triumph' => 5, 'is_all_crew' => false, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_crew_alive', 'triumph' => 1, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_alien_plant', 'triumph' => 1, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_gender', 'triumph' => 4, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden', 'triumph' => 6, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_cat', 'triumph' => 4, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_cat_dead', 'triumph' => -4, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_cat_mush', 'triumph' => -8, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_disease', 'triumph' => -4, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_engineers', 'triumph' => 6, 'is_all_crew' => false, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_biologist', 'triumph' => 3, 'is_all_crew' => false, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_mush_intruder', 'triumph' => -16, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_by_pregnant', 'triumph' => 8, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'eden_computed', 'triumph' => 4, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'anathem', 'triumph' => 8, 'is_all_crew' => false, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'pregnancy', 'triumph' => 8, 'is_all_crew' => false, 'team' => VisibilityEnum::PUBLIC],
            ['name' => 'all_pregnant', 'triumph' => 2, 'is_all_crew' => true, 'team' => VisibilityEnum::PUBLIC],
        ];
    }
}
