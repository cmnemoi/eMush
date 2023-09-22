<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DaedalusNormalizer implements NormalizerInterface
{
    private CycleServiceInterface $cycleService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        CycleServiceInterface $cycleService,
        TranslationServiceInterface $translationService,
    ) {
        $this->cycleService = $cycleService;
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        $group = current($context['groups'] ?? []);

        return $data instanceof Daedalus && $group === false;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Daedalus $daedalus */
        $daedalus = $object;
        $gameConfig = $daedalus->getGameConfig();
        $players = $daedalus->getPlayers();
        $closedPlayers = $players->map(fn (Player $player) => $player->getPlayerInfo()->getClosedPlayer());

        $cryoPlayers = $gameConfig->getCharactersConfig()->count() - $daedalus->getPlayers()->count();
        $humanAlive = $daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->count();
        $mushAlive = $daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count();
        $humanDead = $closedPlayers->filter(fn (ClosedPlayer $player) => !$player->getPlayerInfo()->isAlive() && !$player->isMush())->count();
        $mushDead = $closedPlayers->filter(fn (ClosedPlayer $player) => !$player->getPlayerInfo()->isAlive() && $player->isMush())->count();

        $language = $daedalus->getLanguage();

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
                    'description' => $this->translationService->translate('crewPlayer.description',
                        ['cryogenizedPlayers' => $cryoPlayers,
                            'playerAlive' => $daedalus->getPlayers()->getPlayerAlive()->count(),
                            'playerDead' => $daedalus->getPlayers()->getPlayerDead()->count(),
                            'mushAlive' => $mushAlive,
                            'mushDead' => $mushDead,
                        ], 'daedalus',
                        $language
                    ), ],
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
                $variable . '.name', ['maximum' => $maxValue, 'quantity' => $quantity],
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
}
