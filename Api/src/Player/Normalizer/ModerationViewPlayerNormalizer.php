<?php

declare(strict_types=1);

namespace Mush\Player\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ModerationViewPlayerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(TranslationServiceInterface $translationService)
    {
        $this->translationService = $translationService;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof Player
               && isset($context['groups']) // only moderators can recover this data
               && in_array('moderation_view', $context['groups'], true);
    }

    public function normalize(mixed $object, string $format = null, array $context = [])
    {
        /** @var Player $player */
        $player = $object;
        $daedalus = $player->getDaedalus();
        $language = $daedalus->getLanguage();

        $context['currentPlayer'] = $player;

        return [
            'id' => $player->getId(),
            'daedalusId' => $daedalus->getId(),
            'user' => $this->normalizePlayerUser($player),
            'character' => $this->normalizePlayerCharacter($player, $language),
            'playerVariables' => $this->normalizePlayerVariables($player),
            'isMush' => $player->isMush(),
            'statuses' => $this->normalizePlayerStatuses($player, $format, $context),
            'diseases' => $this->normalizePlayerDiseases($player, $format, $context),
            'skills' => $this->normalizePlayerSkills($player, $format, $context),
            'isAlive' => $player->isAlive(),
            'cycleStartedAt' => $player->getDaedalus()->getCycleStartedAt()?->format('Y-m-d H:i:s'),
        ];
    }

    private function normalizePlayerUser(Player $player): array
    {
        return [
            'id' => $player->getUser()->getId(),
            'userId' => $player->getUser()->getUserId(),
            'username' => $player->getUser()->getUsername(),
            'isBanned' => $player->getUser()->isBanned(),
        ];
    }

    private function normalizePlayerCharacter(Player $player, string $language): array
    {
        $character = $player->getName();

        return [
            'key' => $character,
            'value' => $this->translationService->translate($character . '.name', [], 'characters', $language),
            'description' => $this->translationService->translate($character . '.description', [], 'characters', $language),
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

    private function normalizePlayerStatuses(Player $player, string $format = null, array $context = []): array
    {
        $statuses = [];
        foreach ($player->getStatuses() as $status) {
            $normedStatus = $this->normalizer->normalize($status, $format, $context);
            if (is_array($normedStatus) && count($normedStatus) > 0) {
                $statuses[] = $normedStatus;
            }
        }

        return $statuses;
    }

    private function normalizePlayerDiseases(Player $player, string $format = null, array $context = []): array
    {
        $diseases = [];

        foreach ($player->getMedicalConditions()->getActiveDiseases() as $disease) {
            $normedDisease = $this->normalizer->normalize($disease, $format, $context);
            if (is_array($normedDisease) && count($normedDisease) > 0) {
                $diseases[] = $normedDisease;
            }
        }

        return $diseases;
    }

    private function normalizePlayerSkills(Player $player, string $format = null, array $context = []): array
    {
        $skills = [];

        foreach ($player->getSkills() as $skill) {
            $normedSkill = $this->normalizer->normalize($skill, $format, $context);
            if (is_array($normedSkill) && count($normedSkill) > 0) {
                $skills[] = $normedSkill;
            }
        }

        return $skills;
    }
}
