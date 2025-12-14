<?php

declare(strict_types=1);

namespace Mush\MetaGame\Service;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Game\Enum\CharacterEnum;
use Mush\Player\Repository\PlayerRepositoryInterface;
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
}
