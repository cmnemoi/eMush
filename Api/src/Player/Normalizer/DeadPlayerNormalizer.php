<?php

namespace Mush\Player\Normalizer;

use Mush\Game\Enum\GameStatusEnum;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class DeadPlayerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator,
    ) {
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player && $data === $currentPlayer && $data->getGameStatus() === GameStatusEnum::FINISHED;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;

        $character = $player->getCharacterConfig()->getName();

        $endCause = $player->getDeadPlayerInfo()->getEndStatus();

        return [
            'id' => $player->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translator->trans($character . '.name', [], 'characters'),
            ],
            'triumph' => $player->getTriumph(),
            'mush' => $player->isMush(),
            'user' => $player->getUser()->getUsername(),
            'players' => $this->getOtherPlayers($player),
            'endCause' => [
                'name' => $this->translator->trans($endCause . '.name', [], 'end_cause'),
                'description' => $this->translator->trans($endCause . '.category', [], 'end_cause'),
            ],
        ];
    }

    private function getOtherPlayers(Player $player): array
    {
        $otherPlayers = [];
        foreach ($player->getDaedalus()->getPlayers() as $otherPlayer) {
            if ($otherPlayer !== $player) {
                $character = $otherPlayer->getCharacterConfig()->getName();

                $normalizedOtherPlayer = [
                    'id' => $player->getId(),
                    'character' => [
                        'key' => $character,
                        'value' => $this->translator->trans($character . '.name', [], 'characters'),
                        'description' => $this->translator->trans($character . '.abstract', [], 'characters'),
                        ],
                    'likes' => $player->getDeadPlayerInfo()->getLikes(),
                    ];

                if ($otherPlayer->getGameStatus() !== GameStatusEnum::CURRENT) {
                    $endCause = $otherPlayer->getDeathPlayerInfo()->getEndStatus();
                    $normalizedOtherPlayer['isDead'] = [
                        'day' => $otherPlayer->getDeathPlayerInfo()->getDayDeath(),
                        'cycle' => $otherPlayer->getDeathPlayerInfo()->getCycleDeath(),
                        'cause' => [$this->normalizeEndReason($endCause)],
                    ];
                } else {
                    $normalizedOtherPlayer['isDead'] = false;
                }
                $otherPlayers[] = $normalizedOtherPlayer;
            }
        }

        return $otherPlayers;
    }

    private function normalizeEndReason(string $endCause): array
    {
        return [
            'name' => $this->translator->trans($endCause . '.name', [], 'end_cause'),
            'description' => $this->translator->trans($endCause . '.description', [], 'end_cause'),
        ];
    }
}
