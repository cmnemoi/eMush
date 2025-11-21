<?php

declare(strict_types=1);

namespace Mush\Exploration\Normalizer;

use Mush\Exploration\Entity\PlanetSector;
use Mush\Exploration\Enum\PlanetSectorEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PlanetSectorNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PlanetSector;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [PlanetSector::class => true];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        /** @var PlanetSector $planetSector */
        $planetSector = $object;

        $player = $this->getCurrentPlayerFromContext($context);

        $key = $this->getSectorKeyForPlayer($planetSector, $player);

        $data = [
            'id' => $planetSector->getId(),
            'updatedAt' => $planetSector->getUpdatedAt()?->format(\DateTimeInterface::ATOM),
            'key' => $key,
            'name' => $this->translationService->translate(
                $key . '.name',
                parameters: [],
                domain: 'planet',
                language: $planetSector->getPlanet()->getDaedalus()->getLanguage()
            ),
            'description' => $this->getTranslatedSectorDescriptionForPlayer($planetSector, $player),
            'isVisited' => $planetSector->isVisited(),
            'isRevealed' => $planetSector->isRevealed(),
        ];

        if ($this->shouldShowNextSectorToPlayer($planetSector, $player)) {
            $data['isNextSector'] = true;
        }

        return $data;
    }

    private function getSectorKeyForPlayer(PlanetSector $planetSector, Player $player): string
    {
        if ($planetSector->isRevealed() || $this->shouldShowNextSectorToPlayer($planetSector, $player)) {
            return $planetSector->getName();
        }

        return PlanetSectorEnum::UNKNOWN;
    }

    private function getTranslatedSectorDescriptionForPlayer(PlanetSector $planetSector, Player $player): string
    {
        $key = $this->getSectorKeyForPlayer($planetSector, $player);
        $language = $planetSector->getPlanet()->getDaedalus()->getLanguage();

        $description = $this->translationService->translate(
            $key . '.description',
            parameters: [],
            domain: 'planet',
            language: $language,
        );

        if ($this->shouldShowNextSectorToPlayer($planetSector, $player)) {
            $description .= '//' . $this->translationService->translate(
                'next_sector',
                parameters: [
                    'isMush' => $player->isMush() ? 'true' : 'false',
                ],
                domain: 'planet',
                language: $language,
            );
        }

        return $description;
    }

    private function shouldShowNextSectorToPlayer(PlanetSector $planetSector, Player $player): bool
    {
        return $player->hasAnySkill([SkillEnum::U_TURN, SkillEnum::TRAITOR])
            && $player->getExploration()?->getNextSector()?->equals($planetSector);
    }

    private function getCurrentPlayerFromContext(array $context): Player
    {
        $player = $context['currentPlayer'];
        if (!$player) {
            throw new \RuntimeException('Current player is required');
        }

        return $player;
    }
}
