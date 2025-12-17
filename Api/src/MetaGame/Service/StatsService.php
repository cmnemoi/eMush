<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Repository\PlayerRepositoryInterface;
use Mush\RoomLog\Entity\RoomLog;
use Mush\Skill\Entity\Skill;
use Mush\Skill\Enum\SkillEnum;
use Mush\Skill\Repository\SkillRepositoryInterface;

final class StatsService implements StatsServiceInterface
{
    public function __construct(
        private PlayerRepositoryInterface $playerRepository,
        private SkillRepositoryInterface $skillRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    public function getPlayerSkillCount(SkillEnum $skill): string
    {
        $number = $this->skillRepository->countSkill($skill);

        return 'The result is ' . $number;
    }

    public function getAllSkillCount(): string
    {
        $number = $this->skillRepository->countAllSkill();

        return 'The result is ' . $number;
    }

    public function getSkillList(): array
    {
        return SkillEnum::cases();
    }

    public function getCharacterList(): array
    {
        return CharacterEnum::getAll();
    }

    public function getSkillByCharacter(string $character): string
    {
        $result = $this->skillRepository->countSkillByCharacter($character);

        $resultArray = [];

        $resultText = '';

        /** @var Skill $skill */
        foreach ($result as $skill) {
            $skillName = $skill->getConfig()->getNameAsString();
            if (\array_key_exists($skillName, $resultArray)) {
                ++$resultArray[$skillName];
            } else {
                $resultArray[$skillName] = 1;
            }
        }

        foreach ($resultArray as $key => $value) {
            $resultText .= \sprintf('%s was learned %d times.//', $key, $value);
        }

        return $resultText;
    }

    public function getExploFightData(int $daedalusId): string
    {
        $queryBuilder = $this->entityManager->getRepository(ExplorationLog::class)->createQueryBuilder('exploration_log')
            ->select('exploration_log')
            ->innerJoin('exploration_log.closedExploration', 'closedExploration')
            ->innerJoin('closedExploration.daedalusInfo', 'daedalusInfo')
            ->where("exploration_log.eventName = 'fight'")
            ->andWhere('daedalusInfo.id >= :id')
            ->setParameter('id', $daedalusId);

        $resultRaw = $queryBuilder->getQuery()->getResult();

        $resultProcessed = [];
        $resultProcessed['creaturePower'] = [];
        $resultProcessed['crewPower'] = [];
        $resultProcessed['dmgTaken'] = [];

        /** @var ExplorationLog $log */
        foreach ($resultRaw as $log) {
            $resultProcessed['creaturePower'][] = $log->getParameters()['creature_strength'];
            $resultProcessed['crewPower'][] = $log->getParameters()['expedition_strength'];
            $resultProcessed['dmgTaken'][] = $log->getParameters()['damage'];
        }

        $creaturePower = array_sum($resultProcessed['creaturePower']) / \count($resultProcessed['creaturePower']);
        $crewPower = array_sum($resultProcessed['crewPower']) / \count($resultProcessed['crewPower']);
        $dmgTaken = array_sum($resultProcessed['dmgTaken']) / \count($resultProcessed['dmgTaken']);

        $resultText = '';
        $resultText .= \sprintf('The average creature strenght is %f.//', $creaturePower);
        $resultText .= \sprintf('The average crew strenght is %f.//', $crewPower);
        $resultText .= \sprintf('The average damage taken is %f.//', $dmgTaken);

        return $resultText;
    }

    public function getMushData(): string
    {
        $alphaMushAmount = 0;
        $betaMushAmount = 0;

        // we the differents fates here
        $fates = ['vaccinated' => [], 'alive' => [], 'transfered' => []];

        // we get every mush from this logs as it happen when you are first converted
        $queryBuilder = $this->entityManager->getRepository(RoomLog::class)->createQueryBuilder('room_log')
            ->select('room_log')
            ->where("room_log.log = 'mush_initial_bonus.log'");

        $mushConversionLog = $queryBuilder->getQuery()->getResult();

        // we get every vaccinated log
        $queryBuilder = $this->entityManager->getRepository(RoomLog::class)->createQueryBuilder('room_log')
            ->select('room_log')
            ->where("room_log.log = 'player_vaccinated'");

        $mushVaccinationLog = $queryBuilder->getQuery()->getResult();

        // we store the id of every player vaccinated
        /** @var RoomLog $log */
        foreach ($mushVaccinationLog as $log) {
            $fates['vaccinated'][] = $log->getPlayerOrThrow()->getId();
        }

        // we check every log from $mushConversionLog
        /** @var RoomLog $log */
        foreach ($mushConversionLog as $log) {
            $player = $log->getPlayerOrThrow();
            $closedPlayer = $player->getPlayerInfo()->getClosedPlayer();

            // here we store if a player was an alpha or beta
            if ($closedPlayer->isAlphaMush()) {
                ++$alphaMushAmount;
            } else {
                ++$betaMushAmount;
            }

            // here we get the death cause for a given player
            $deathCause = $closedPlayer->getEndCause();

            // if the player id is not stored in vaccinated then :
            if (\in_array($player->getId(), $fates['vaccinated'], true) === false) {
                // if player is no longer mush when dead but did not get the serum then they must have transfered to another player.
                if ($closedPlayer->isMush() === false) {
                    $fates['transfered'][] = $player->getId();
                }
                // THe player is alive
                elseif ($player->isAlive()) {
                    $fates['alive'][] = $player->getId();
                }
                // here we handle causes of death
                elseif (\array_key_exists($deathCause, $fates)) {
                    $fates[$deathCause][] = $player->getId();
                } else {
                    $fates[$deathCause] = [$player->getId()];
                }
            }
        }

        $textResult = '';
        $textResult .= \sprintf('Number of Mush : %d.//', $alphaMushAmount + $betaMushAmount);
        $textResult .= \sprintf('Number of Alpha Mush : %d.//', $alphaMushAmount);
        $textResult .= \sprintf('Number of Beta Mush : %d.//', $betaMushAmount);
        $textResult .= \sprintf('Number of Spore Extracted : %d.//', $this->getSporeExtracted());
        $textResult .= \sprintf('Number of Spore Used for Phagocyte : %d.//', $this->getPhagocyteSpores());
        $textResult .= \sprintf('Number of Spore Used To Infect Directly : %d.//', $this->getInfectActionSpores());
        $textResult .= \sprintf('Number of Spore Used to Trap a Room: %d.//', $this->getTrapSpores());

        foreach ($fates as $key => $value) {
            $textResult .= \sprintf("Number of Mush with the fate '%s' : %d.//", $key, \count($value));
        }

        return $textResult;
    }

    private function getSporeExtracted(): int
    {
        $queryBuilder = $this->entityManager->getRepository(RoomLog::class)->createQueryBuilder('room_log')
            ->select('room_log')
            ->where("room_log.log = 'extract_spore_success'");

        $extractSporeLog = $queryBuilder->getQuery()->getResult();

        return \count($extractSporeLog);
    }

    private function getInfectActionSpores(): int
    {
        $queryBuilder = $this->entityManager->getRepository(RoomLog::class)->createQueryBuilder('room_log')
            ->select('room_log')
            ->where("room_log.log = 'infect_success'");

        $infectLog = $queryBuilder->getQuery()->getResult();

        return \count($infectLog);
    }

    private function getPhagocyteSpores(): int
    {
        $queryBuilder = $this->entityManager->getRepository(RoomLog::class)->createQueryBuilder('room_log')
            ->select('room_log')
            ->where("room_log.log = 'phagocyte_success'");

        $phagocyteLog = $queryBuilder->getQuery()->getResult();

        return \count($phagocyteLog);
    }

    private function getTrapSpores(): int
    {
        $queryBuilder = $this->entityManager->getRepository(RoomLog::class)->createQueryBuilder('room_log')
            ->select('room_log')
            ->where("room_log.log = 'trap_closet_success'");

        $trapLog = $queryBuilder->getQuery()->getResult();

        return \count($trapLog);
    }
}
