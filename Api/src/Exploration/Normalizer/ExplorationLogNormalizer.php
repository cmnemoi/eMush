<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Exploration\Entity\ExplorationLog;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ExplorationLogNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof ExplorationLog;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): ?array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        /** @var ExplorationLog $explorationLog */
        $explorationLog = $object;

        if (!$currentPlayer->isExploring()) {
            return null;
        }

        $planetSectorKey = $explorationLog->getPlanetSectorName();

        $logParameters = $explorationLog->getParameters();
        $logParameters['planet'] = $this->translationService->translate(
            key: 'planet_name',
            parameters: $explorationLog->getClosedExploration()->getPlanetName(),
            domain: 'planet',
            language: $currentPlayer->getDaedalus()->getLanguage(),
        );

        return [
            'id' => $explorationLog->getId(),
            'planetSectorKey' => $planetSectorKey,
            'planetSectorName' => $this->translationService->translate(
                key: $explorationLog->getPlanetSectorName() . '.name',
                parameters: [],
                domain: 'planet',
                language: $currentPlayer->getDaedalus()->getLanguage(),
            ),
            'eventName' => $this->translationService->translate(
                key: $explorationLog->getEventName() . '.name',
                parameters: [],
                domain: 'planet_sector_event',
                language: $currentPlayer->getDaedalus()->getLanguage(),
            ),
            'eventDescription' => $this->translationService->translate(
                key: $explorationLog->getEventName() . '.description',
                parameters: $logParameters,
                domain: 'planet_sector_event',
                language: $currentPlayer->getDaedalus()->getLanguage(),
            ),
            'eventOutcome' => $this->translationService->translate(
                key: $explorationLog->getEventName() . '.' . $planetSectorKey . '_description',
                parameters: $logParameters,
                domain: 'planet_sector_event',
                language: $currentPlayer->getDaedalus()->getLanguage(),
            ),
        ];
    }
}
