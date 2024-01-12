<?php

declare(strict_types=1);

namespace Mush\Player\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class AdminViewPlayerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof Player && $data->isAlive()
               && isset($context['groups']) // only admins can recover this data
               && in_array('admin_view', $context['groups'], true);
    }

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        /** @var Player $player */
        $player = $object;
        $daedalus = $player->getDaedalus();

        return [
            'id' => $player->getId(),
            'user' => $this->normalizer->normalize($player->getUser(), $format, $context),
            'character' => $this->normalizePlayerCharacter($player, $daedalus->getLanguage()),
            'playerVariables' => $this->normalizePlayerVariables($player),
            'isMush' => $player->isMush(),
            // @TODO
            // 'statuses' => $this->normalizePlayerStatuses($player, $daedalus->getLanguage()),
            // 'diseases' => $this->normalizePlayerDiseases($player, $daedalus->getLanguage()),
            // add anything relevant...
        ];
    }

    private function normalizePlayerCharacter(Player $player, string $language): array
    {
        $character = $player->getName();

        return [
            'key' => $character,
            'value' => $this->translationService->translate($character . '.name', [], 'characters', $language),
            'description' => $this->translationService->translate($character . '.description', [], 'characters', $language),
            'skills' => $player->getPlayerInfo()->getCharacterConfig()->getSkills(),
        ];
    }

    private function normalizePlayerVariables(Player $player): array
    {
        return [
            'healthPoint' => $player->getHealthPoint(),
            'moralPoint' => $player->getMoralPoint(),
            'actionPoint' => $player->getActionPoint(),
            'movementPoint' => $player->getMovementPoint(),
            'satiety' => $player->getSatiety(),
            'spores' => $player->getSpores(),
        ];
    }
}
