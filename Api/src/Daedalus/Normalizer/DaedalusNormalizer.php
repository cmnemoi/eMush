<?php

namespace Mush\Daedalus\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Service\PlanetServiceInterface;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\DaedalusStatusEnum;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DaedalusNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private CycleServiceInterface $cycleService;
    private TranslationServiceInterface $translationService;
    private PlanetServiceInterface $planetService;

    public function __construct(
        CycleServiceInterface $cycleService,
        TranslationServiceInterface $translationService,
        PlanetServiceInterface $planetService,
    ) {
        $this->cycleService = $cycleService;
        $this->translationService = $translationService;
        $this->planetService = $planetService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        // This group is used to differentiate the normalizer for in-game Daedalus and Daedalus in the admin panel
        // Do not remove it
        $group = current($context['groups'] ?? []);

        return $data instanceof Daedalus && $group === false;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context['normalizing_daedalus'] = true;

        /** @var Daedalus $daedalus */
        $daedalus = $object;

        $gameConfig = $daedalus->getGameConfig();
        $players = $daedalus->getPlayers();

        /** @var ArrayCollection<array-key, ClosedPlayer> $closedPlayers */
        $closedPlayers = $players->map(static fn (Player $player) => $player->getPlayerInfo()->getClosedPlayer());

        $cryoPlayers = $gameConfig->getCharactersConfig()->count() - $daedalus->getPlayers()->count();
        $humanAlive = $daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->count();
        $mushAlive = $daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count();
        $humanDead = $closedPlayers->filter(static fn (ClosedPlayer $player) => !$player->getPlayerInfo()->isAlive() && !$player->isMush())->count();
        $mushDead = $closedPlayers->filter(static fn (ClosedPlayer $player) => !$player->getPlayerInfo()->isAlive() && $player->isMush())->count();

        $language = $daedalus->getLanguage();

        $planet = $this->planetService->findPlanetInDaedalusOrbit($daedalus);
        if ($planet !== null) {
            $planet = $this->normalizer->normalize($planet, $format, $context);
        }

        $attackingHunters = $daedalus->getAttackingHunters()->count();

        return [
            'id' => $object->getId(),
            'game_config' => $object->getGameConfig()->getId(),
            'oxygen' => $this->normalizeDaedalusVariable($object, DaedalusVariableEnum::OXYGEN, $language),
            'fuel' => $this->normalizeDaedalusVariable($object, DaedalusVariableEnum::FUEL, $language),
            'hull' => $this->normalizeDaedalusVariable($object, DaedalusVariableEnum::HULL, $language),
            'shield' => $this->normalizeDaedalusVariable($object, DaedalusVariableEnum::SHIELD, $language),
            'timer' => [
                'name' => $this->translationService->translate('currentCycle.name', [], 'daedalus', $language),
                'description' => $this->translationService->translate(
                    'currentCycle.description',
                    [],
                    'daedalus',
                    $language
                ),
                'timerCycle' => $this->cycleService->getDateStartNextCycle($object)->format(\DateTimeInterface::ATOM),
            ],
            'calendar' => [
                'name' => $this->translationService->translate('calendar.name', [], 'daedalus', $language),
                'description' => $this->translationService->translate('calendar.description', [], 'daedalus', $language),
                'cycle' => $object->getCycle(),
                'cycleName' => $this->translationService->translate('cycle.name', [], 'daedalus', $language),
                'day' => $object->getDay(),
                'dayName' => $this->translationService->translate('day.name', [], 'daedalus', $language),
            ],
            'cryogenizedPlayers' => $cryoPlayers,
            'humanPlayerAlive' => $humanAlive,
            'humanPlayerDead' => $humanDead,
            'mushPlayerAlive' => $mushAlive,
            'mushPlayerDead' => $mushDead,
            'crewPlayer' => [
                'name' => $this->translationService->translate('crewPlayer.name', [], 'daedalus', $language),
                'description' => $this->translationService->translate(
                    'crewPlayer.description',
                    ['cryogenizedPlayers' => $cryoPlayers,
                        'playerAlive' => $daedalus->getPlayers()->getPlayerAlive()->count(),
                        'playerDead' => $daedalus->getPlayers()->getPlayerDead()->count(),
                        'mushAlive' => $mushAlive,
                        'mushDead' => $mushDead,
                    ],
                    'daedalus',
                    $language
                ), ],
            'inOrbitPlanet' => $planet,
            'isDaedalusTravelling' => $daedalus->hasStatus(DaedalusStatusEnum::TRAVELING),
            'attackingHunters' => $attackingHunters,
            'onGoingExploration' => $this->normalizeOnGoingExploration($daedalus),
            'projects' => $this->getNormalizedProjects($daedalus, $format, $context),
        ];
    }

    private function normalizeDaedalusVariable(Daedalus $daedalus, string $variable, string $language): array
    {
        $gameVariable = $daedalus->getVariableByName($variable);
        $maxValue = $gameVariable->getMaxValue();
        $quantity = $gameVariable->getValue();

        return [
            'quantity' => $quantity,
            'name' => $this->translationService->translate(
                $variable . '.name',
                ['maximum' => $maxValue, 'quantity' => $quantity],
                'daedalus',
                $language
            ),
            'description' => $this->translationService->translate(
                $variable . '.description',
                [],
                'daedalus',
                $language
            ),
        ];
    }

    private function getNormalizedProjects(Daedalus $daedalus, ?string $format, array $context): ?array
    {
        $normalizedProjects = [];
        if ($daedalus->isPilgredFinished()) {
            $normalizedProjects['pilgred'] = $this->normalizer->normalize($daedalus->getPilgred(), format: $format, context: $context);
        }

        return $normalizedProjects;
    }

    private function normalizeOnGoingExploration(Daedalus $daedalus): ?array
    {
        $exploration = $daedalus->getExploration();
        if ($exploration === null) {
            return null;
        }

        $normalizedPlanet = $this->translationService->translate(
            'exploration_pop_up.planet',
            [
                'planetName' => $this->translationService->translate(
                    key: 'planet_name',
                    parameters: $exploration->getPlanet()->getName()->toArray(),
                    domain: 'planet',
                    language: $daedalus->getLanguage()
                ),
            ],
            'misc',
            $daedalus->getLanguage()
        );
        $normalizedExplorators = $this->translationService->translate(
            'exploration_pop_up.explorators',
            [
                'explorators' => $this->getTranslatedExploratorNames($exploration),
            ],
            'misc',
            $daedalus->getLanguage()
        );
        $normalizedEstimatedDuration = $this->translationService->translate(
            'exploration_pop_up.estimated_duration',
            [
                'estimatedDuration' => $exploration->getEstimatedDuration(),
            ],
            'misc',
            $daedalus->getLanguage()
        );

        return [
            'title' => $this->translationService->translate(
                'exploration_pop_up.title',
                [],
                'misc',
                $daedalus->getLanguage()
            ),
            'planet' => $normalizedPlanet,
            'explorators' => $normalizedExplorators,
            'estimatedDuration' => $normalizedEstimatedDuration,
        ];
    }

    private function getTranslatedExploratorNames(Exploration $exploration): string
    {
        /** @var array<int, string> $exploratorNames */
        $exploratorNames = $exploration->getAliveExplorators()
            ->filter(static fn (Player $player) => !$player->hasStatus(PlayerStatusEnum::LOST))
            ->map(fn (Player $player) => $this->translateExploratorName($player))
            ->toArray();

        return implode(', ', $exploratorNames);
    }

    private function translateExploratorName(Player $player): string
    {
        return $this->translationService->translate(
            key: $player->getLogName() . '.name',
            parameters: [],
            domain: 'characters',
            language: $player->getDaedalus()->getLanguage(),
        );
    }
}
