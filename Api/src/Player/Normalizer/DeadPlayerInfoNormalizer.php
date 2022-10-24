<?php

namespace Mush\Player\Normalizer;

use Mush\Equipment\Service\GearToolServiceInterface;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\DeadPlayerInfo;
use Mush\Player\Entity\Player;
use Mush\Player\Service\PlayerServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class DeadPlayerInfoNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private PlayerServiceInterface $playerService;
    private TranslationServiceInterface $translationService;
    private GearToolServiceInterface $gearToolService;

    public function __construct(
        PlayerServiceInterface $playerService,
        TranslationServiceInterface $translationService,
        GearToolServiceInterface $gearToolService
    ) {
        $this->playerService = $playerService;
        $this->translationService = $translationService;
        $this->gearToolService = $gearToolService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof DeadPlayerInfo;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var DeadPlayerInfo $deadPlayerInfo */
        $deadPlayerInfo = $object;

        $endCause = $deadPlayerInfo->getEndStatus();

        $player = $deadPlayerInfo->getPlayer();

        return [
            'players' => $this->getOtherPlayers($player),
            'endCause' => $this->normalizeEndReason($endCause, $player->getDaedalus()->getGameConfig()->getLanguage()),
        ];
    }

    private function getOtherPlayers(Player $player): array
    {
        $language = $player->getDaedalus()->getGameConfig()->getLanguage();

        $otherPlayers = [];
        foreach ($player->getDaedalus()->getPlayers() as $otherPlayer) {
            if ($otherPlayer !== $player) {
                $character = $otherPlayer->getCharacterConfig()->getName();

                // TODO add likes
                $normalizedOtherPlayer = [
                    'id' => $player->getId(),
                    'character' => [
                        'key' => $character,
                        'value' => $this->translationService->translate(
                            $character . '.name',
                            [],
                            'characters',
                            $language
                        ),
                        'description' => $this->translationService->translate(
                            $character . '.abstract',
                            [],
                            'characters',
                            $language
                        ),
                    ],
                ];

                if ($otherPlayer->getGameStatus() !== GameStatusEnum::CURRENT) {
                    $deadPlayerInfo = $this->playerService->findDeadPlayerInfo($otherPlayer);
                    if ($deadPlayerInfo === null) {
                        throw new \LogicException('unable to find deadPlayerInfo');
                    }

                    $endCause = $deadPlayerInfo->getEndStatus();
                    $normalizedOtherPlayer['isDead'] = [
                        'day' => $deadPlayerInfo->getDayDeath(),
                        'cycle' => $deadPlayerInfo->getCycleDeath(),
                        'cause' => $this->normalizeEndReason($endCause, $language),
                    ];
                } else {
                    $normalizedOtherPlayer['isDead'] = false;
                }
                $otherPlayers[] = $normalizedOtherPlayer;
            }
        }

        return $otherPlayers;
    }

    private function normalizeEndReason(string $endCause, string $language): array
    {
        return [
            'key' => $endCause,
            'name' => $this->translationService->translate(
                $endCause . '.name',
                [],
                'end_cause',
                $language
            ),
            'description' => $this->translationService->translate(
                $endCause . '.description',
                [],
                'end_cause',
                $language
            ),
        ];
    }
}
