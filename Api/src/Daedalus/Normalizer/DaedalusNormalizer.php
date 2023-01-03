<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Daedalus\Enum\DaedalusVariableEnum;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
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

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Daedalus $daedalus */
        $daedalus = $object;
        $gameConfig = $daedalus->getGameConfig();
        $cryoPlayer = $gameConfig->getCharactersConfig()->count() - $daedalus->getPlayers()->count();
        $humanDead = $daedalus->getPlayers()->getHumanPlayer()->getPlayerDead()->count();
        $mushAlive = $daedalus->getPlayers()->getMushPlayer()->getPlayerAlive()->count();
        $mushDead = $daedalus->getPlayers()->getMushPlayer()->getPlayerDead()->count();

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
                    'timerCycle' => $this->cycleService->getDateStartNextCycle($object)->format(\DateTime::ATOM),
                ],
                'calendar' => [
                    'name' => $this->translationService->translate('calendar.name', [], 'daedalus', $language),
                    'description' => $this->translationService->translate('calendar.description', [], 'daedalus', $language),
                    'cycle' => $object->getCycle(),
                    'day' => $object->getDay(),
                ],
                'cryogenizedPlayers' => $cryoPlayer,
                'humanPlayerAlive' => $daedalus->getPlayers()->getHumanPlayer()->getPlayerAlive()->count(),
                'humanPlayerDead' => $humanDead,
                'mushPlayerAlive' => $mushAlive,
                'mushPlayerDead' => $mushDead,
                'crewPlayer' => [
                    'name' => $this->translationService->translate('crewPlayer.name', [], 'daedalus', $language),
                    'description' => $this->translationService->translate('crewPlayer.description',
                        ['cryogenizedPlayers' => $cryoPlayer,
                            'playerAlive' => $daedalus->getPlayers()->getPlayerAlive()->count(),
                            'humanDead' => $humanDead,
                            'mushAlive' => $mushAlive,
                            'mushDead' => $mushDead,
                        ], 'daedalus',
                        $language
                    ), ],
            ];
    }

    private function normalizeDaedalusVariable(Daedalus $daedalus, string $variable, string $language): array
    {
        $gameVariable = $daedalus->getVariableFromName($variable);
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
