<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Mush\Exploration\Entity\ClosedExploration;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\ClosedPlayer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ClosedExplorationNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    private const ALREADY_CALLED = 'CLOSED_EXPLORATION_NORMALIZER_ALREADY_CALLED';

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof ClosedExploration;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): ?array
    {
        /** @var ClosedExploration $closedExploration */
        $closedExploration = $object;

        $context[self::ALREADY_CALLED] = true;

        /** @var array $normalizedClosedExploration */
        $normalizedClosedExploration = $this->normalizer->normalize($closedExploration, $format, $context);

        /** @var ArrayCollection<int, array> $closedExplorators */
        $closedExplorators = new ArrayCollection();

        /** @var ClosedPlayer $closedExplorator */
        foreach ($closedExploration->getClosedExplorators() as $closedExplorator) {
            $closedExplorators->add([
                'id' => $closedExplorator->getId(),
                'logName' => $closedExplorator->getLogName(),
                'isAlive' => $closedExplorator->isAlive(),
            ]);
        }

        $normalizedClosedExploration['closedExplorators'] = $closedExplorators->toArray();

        $normalizedClosedExploration['planetName'] = $this->translationService->translate(
            key: 'planet_name',
            parameters: $closedExploration->getPlanetName(),
            domain: 'planet',
            language: $closedExploration->getDaedalusInfo()->getLanguage(),
        );
        $normalizedClosedExploration['tips'] = $this->translationService->translate(
            key: 'closed_exploration.tips',
            parameters: [],
            domain: 'terminal',
            language: $closedExploration->getDaedalusInfo()->getLanguage(),
        );

        return $normalizedClosedExploration;
    }
}
