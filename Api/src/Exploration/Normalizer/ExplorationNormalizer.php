<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Exploration\Entity\Exploration;
use Mush\Exploration\Entity\ExplorationLogCollection;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Mush\Status\Enum\PlayerStatusEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ExplorationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private CycleServiceInterface $cycleService;
    private TranslationServiceInterface $translationService;

    public function __construct(
        CycleServiceInterface $cycleService,
        TranslationServiceInterface $translationService,
    ) {
        $this->cycleService = $cycleService;
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Exploration;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Exploration::class => false,
        ];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): ?array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        /** @var Exploration $exploration */
        $exploration = $object;

        if (!$currentPlayer->isExploringOrIsLostOnPlanet()) {
            return null;
        }

        return [
            'createdAt' => $exploration->getCreatedAt(),
            'updatedAt' => $exploration->getUpdatedAt(),
            'cycleLength' => $exploration->getCycleLength(),
            'planet' => $this->normalizer->normalize($exploration->getPlanet(), $format, $context),
            'explorators' => $this->normalizeExplorators($exploration->getExplorators()),
            'logs' => $this->normalizeExplorationLogs($exploration->getClosedExploration()->getLogs()),
            'estimated_duration' => $this->translationService->translate(
                'estimated_duration',
                [
                    '%duration%' => $exploration->getEstimatedDuration(),
                    'isExplorationFinished' => $exploration->isFinished() ? 'true' : 'false',
                ],
                'misc',
                $exploration->getDaedalus()->getLanguage(),
            ),
            'timer' => $this->getNormalizedTimer($exploration),
            'uiElements' => $this->getNormalizedUiElements($exploration, $currentPlayer),
        ];
    }

    private function normalizeExplorators(PlayerCollection $explorators): array
    {
        $normalizedExplorators = [];

        /** @var Player $explorator */
        foreach ($explorators as $explorator) {
            $normalizedExplorators[] = [
                'key' => $explorator->getName(),
                'name' => $this->translationService->translate(
                    key: $explorator->getName() . '.name',
                    parameters: [],
                    domain: 'characters',
                    language: $explorator->getDaedalus()->getLanguage(),
                ),
                'healthPoints' => $explorator->getHealthPoint(),
                'isDead' => !$explorator->isAlive(),
                'isLost' => $explorator->hasStatus(PlayerStatusEnum::LOST),
                'isStuck' => $explorator->hasStatus(PlayerStatusEnum::STUCK_IN_THE_SHIP),
            ];
        }

        return $normalizedExplorators;
    }

    private function normalizeExplorationLogs(ExplorationLogCollection $explorationLogs): array
    {
        $normalizedLogs = [];

        foreach ($explorationLogs->getLogsSortedBy('createdAt', descending: true) as $log) {
            $normalizedLogs[] = $this->normalizer->normalize($log);
        }

        return $normalizedLogs;
    }

    private function getNormalizedUiElements(Exploration $exploration, Player $player): array
    {
        $normalizedUiElements = [];
        $normalizedUiElements['tips'] = $this->translationService->translate(
            'exploration.tips',
            [
                'quantity' => $exploration->getCycleLength(),
                'isExplorationFinished' => $exploration->isFinished() ? 'true' : 'false',
            ],
            'terminal',
            $exploration->getDaedalus()->getLanguage(),
        );
        $normalizedUiElements['recoltedInfos'] = $this->translationService->translate(
            'exploration.recolted_infos',
            [],
            'terminal',
            $exploration->getDaedalus()->getLanguage()
        );
        $normalizedUiElements['newStep'] = $this->translationService->translate(
            'exploration.new_step',
            [],
            'terminal',
            $exploration->getDaedalus()->getLanguage()
        );
        $normalizedUiElements['lost'] = $this->translationService->translate(
            'exploration.lost',
            [$player->getLogKey() => $player->getLogName()],
            'terminal',
            $exploration->getDaedalus()->getLanguage()
        );
        $normalizedUiElements['finished'] = $this->translationService->translate(
            'exploration.finished',
            [],
            'terminal',
            $exploration->getDaedalus()->getLanguage()
        );

        return $normalizedUiElements;
    }

    private function getNormalizedTimer(Exploration $exploration): array
    {
        $timerCycle = $this->cycleService->getExplorationDateStartNextCycle($exploration)->format(\DateTimeInterface::ATOM);
        if ($exploration->isFinished()) {
            $timerCycle = null;
        }

        return [
            'name' => $this->translationService->translate('currentCycle.name', [], 'daedalus', $exploration->getDaedalus()->getLanguage()),
            'description' => $this->translationService->translate(
                'currentCycle.description',
                [],
                'daedalus',
                $exploration->getDaedalus()->getLanguage(),
            ),
            'timerCycle' => $timerCycle,
        ];
    }
}
