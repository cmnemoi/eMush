<?php

declare(strict_types=1);

namespace Mush\Exploration\PlanetSectorEventHandler;

use Doctrine\ORM\EntityManagerInterface;
use Mush\Equipment\Enum\ItemEnum;
use Mush\Exploration\Entity\ExplorationLog;
use Mush\Exploration\Event\PlanetSectorEvent;
use Mush\Game\Entity\Collection\ProbaCollection;
use Mush\Game\Service\EventServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Modifier\Entity\Config\AbstractModifierConfig;
use Mush\Modifier\Enum\ModifierNameEnum;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;

abstract class AbstractPlanetSectorEventHandler
{
    protected EntityManagerInterface $entityManager;
    protected EventServiceInterface $eventService;
    protected RandomServiceInterface $randomService;
    protected TranslationServiceInterface $translationService;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventServiceInterface $eventService,
        RandomServiceInterface $randomService,
        TranslationServiceInterface $translationService
    ) {
        $this->entityManager = $entityManager;
        $this->eventService = $eventService;
        $this->randomService = $randomService;
        $this->translationService = $translationService;
    }

    abstract public function getName(): string;

    abstract public function handle(PlanetSectorEvent $event): ExplorationLog;

    protected function createExplorationLog(PlanetSectorEvent $event, array $parameters = []): ExplorationLog
    {
        $closedExploration = $event->getExploration()->getClosedExploration();

        $explorationLog = new ExplorationLog($closedExploration);
        $explorationLog->setPlanetSectorName($event->getPlanetSector()->getName());
        $explorationLog->setEventName($event->getName());
        $explorationLog->setParameters(array_merge($event->getLogParameters(), $parameters));

        $closedExploration->addLog($explorationLog);

        $this->entityManager->persist($explorationLog);

        return $explorationLog;
    }

    protected function drawEventOutputQuantity(?ProbaCollection $outputTable): int
    {
        if ($outputTable === null) {
            throw new \RuntimeException('You need an output quantity table to draw an event output quantity');
        }

        $quantity = $this->randomService->getSingleRandomElementFromProbaCollection($outputTable);
        if (!\is_int($quantity)) {
            throw new \RuntimeException('Quantity should be an int');
        }

        return $quantity;
    }

    protected function getLogParameters(PlanetSectorEvent $event): array
    {
        $logParameters = [];
        $logParameters['fight_prevented_by_item'] = null;

        if ($event->hasTag(ItemEnum::WHITE_FLAG)) {
            $logParameters['fight_prevented_by_item'] = '////' . $this->translationService->translate(
                key: 'fight_prevented_by_item',
                parameters: ['item' => ItemEnum::WHITE_FLAG],
                domain: 'planet_sector_event',
                language: $event->getExploration()->getDaedalus()->getLanguage()
            );
        }

        return $logParameters;
    }

    protected function getSkillReducedDamageForPlayer(Player $player, SkillEnum $skill): string
    {
        return $this->translationService->translate(
            key: 'skill_reduced_damage_for_player',
            parameters: [
                $player->getLogKey() => $player->getLogName(),
                'skill' => $this->translationService->translate(
                    key: sprintf('%s.name', $skill->toString()),
                    parameters: [],
                    domain: 'skill',
                    language: $player->getLanguage()
                ),
                'quantity' => $this->getSkillReduction($player, $skill),
            ],
            domain: 'planet_sector_event',
        );
    }

    private function getSkillReduction(Player $player, SkillEnum $skill): int
    {
        return match ($skill) {
            SkillEnum::SURVIVALIST => $this->getSurvivalistReduction($player),
            default => 0,
        };
    }

    /**
     * @psalm-suppress PossiblyFalseReference
     * @psalm-suppress UndefinedMethod
     */
    private function getSurvivalistReduction(Player $player): int
    {
        $survivalistSkill = $player->getSkillByNameOrThrow(SkillEnum::SURVIVALIST);
        $survivalistModifierConfig = $survivalistSkill->getModifierConfigs()->filter(static fn (AbstractModifierConfig $modifierConfig) => $modifierConfig->getModifierName() === ModifierNameEnum::PLAYER_PLUS_1_HEALTH_POINT_ON_CHANGE_VARIABLE_IF_FROM_PLANET_SECTOR_EVENT)->first();

        return (int) $survivalistModifierConfig->getDelta();
    }
}
