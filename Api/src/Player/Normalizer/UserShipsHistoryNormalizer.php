<?php

declare(strict_types=1);

namespace Mush\Player\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\ViewModel\UserShipsHistoryViewModel;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class UserShipsHistoryNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof UserShipsHistoryViewModel;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [UserShipsHistoryViewModel::class => true];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var UserShipsHistoryViewModel $object */
        $object = $object;

        $characterName = $this->translationService->translate(
            key: \sprintf('%s.name', $object->characterName),
            parameters: [],
            domain: 'characters',
            language: $this->getLanguageFromContext($context)
        );

        return [
            'characterName' => \sprintf(':%s: %s', $object->characterName, $characterName),
            'daysSurvived' => $object->daysSurvived,
            'nbExplorations' => $object->nbExplorations,
            'nbNeronProjects' => $object->nbNeronProjects,
            'nbResearchProjects' => $object->nbResearchProjects,
            'nbScannedPlanets' => $object->nbScannedPlanets,
            'titles' => implode('', array_map(static fn (string $title) => ":{$title}:", $object->titles)),
            'triumph' => $object->playerWasMush ? \sprintf('%s :triumph_mush:', $object->triumph) : \sprintf('%s :triumph:', $object->triumph),
            'endCause' => $this->translationService->translate(
                key: \sprintf('%s.name', $object->endCause),
                parameters: [],
                domain: 'end_cause',
                language: $this->getLanguageFromContext($context)
            ),
            'daedalusId' => $object->daedalusId,
        ];
    }

    private function getLanguageFromContext(array $context): string
    {
        if (isset($context['language'])) {
            return $context['language'];
        }

        throw new \RuntimeException('The language is not set in the context');
    }
}
