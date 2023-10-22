<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Exploration\Entity\Exploration;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Collection\PlayerCollection;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

// @TODO: probably normalize ClosedExploration instead of Exploration
final class ExplorationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Exploration;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): ?array
    {
        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];
        /** @var Exploration $exploration */
        $exploration = $object;

        if (!$currentPlayer->isExploring()) {
            return null;
        }

        return [
            'id' => $exploration->getId(),
            'createdAt' => $exploration->getCreatedAt(),
            'updatedAt' => $exploration->getUpdatedAt(),
            'planet' => $this->normalizer->normalize($exploration->getPlanet(), $format, $context),
            'explorators' => $this->normalizeExplorators($exploration->getExplorators()),
            'logs' => $this->normalizer->normalize($exploration->getLogs(), $format, $context),
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
                'isAlive' => $explorator->isAlive(),
            ];
        }

        return $normalizedExplorators;
    }
}
