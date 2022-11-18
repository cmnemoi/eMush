<?php

namespace Mush\Player\Normalizer;

use Error;
use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class DeadPlayerNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService,
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player &&
            $data === $currentPlayer &&
            $data->getGameStatus() === GameStatusEnum::FINISHED
        ;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;

        $daedalus = $player->getDaedalus();
        $language = $daedalus->getGameConfig()->getLanguage();
        $character = $player->getCharacterConfig()->getName();
        $deadPlayerInfo = $player->getDeadPlayerInfo();

        if ($deadPlayerInfo === null) {
            throw new Error('a dead player info should have been created');
        }

        $endCause = $deadPlayerInfo->getEndCause();

        $playerData = [
            'id' => $player->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate($character . '.name', [], 'characters', $language),
            ],
            'triumph' => $player->getTriumph(),
            'daedalus' => [
                'key' => $daedalus->getId(),
                'calendar' => [
                    'name' => $this->translationService->translate('calendar.name', [], 'daedalus', $language),
                    'description' => $this->translationService->translate('calendar.description', [], 'daedalus', $language),
                    'cycle' => $daedalus->getCycle(),
                    'day' => $daedalus->getDay(),
                ],
            ],
            'skills' => $player->getSkills(),
            'gameStatus' => $player->getGameStatus(),
            'endCause' => $this->normalizeEndReason($endCause, $player->getDaedalus()->getGameConfig()->getLanguage()),
        ];

        $playerData['players'] = $this->getOtherPlayers($player, $language);

        return $playerData;
    }

    private function getOtherPlayers(Player $player, string $language): array
    {
        $otherPlayers = [];

        /** @var Player $otherPlayer */
        foreach ($player->getDaedalus()->getPlayers() as $otherPlayer) {
            if ($otherPlayer !== $player) {
                $character = $otherPlayer->getCharacterConfig()->getName();

                // TODO add likes
                $normalizedOtherPlayer = [
                    'id' => $otherPlayer->getId(),
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

                $deadPlayerInfo = $otherPlayer->getDeadPlayerInfo();

                if ($deadPlayerInfo === null) {
                    throw new Error('player should have a deadPlayerInfo property');
                }

                $normalizedOtherPlayer['likes'] = $deadPlayerInfo->getLikes();

                if ($otherPlayer->getGameStatus() !== GameStatusEnum::CURRENT) {
                    $endCause = $deadPlayerInfo->getEndCause();
                    $normalizedOtherPlayer['isDead'] = [
                        'day' => $deadPlayerInfo->getDayDeath(),
                        'cycle' => $deadPlayerInfo->getCycleDeath(),
                        'cause' => $this->normalizeEndReason($endCause, $language),
                    ];
                } else {
                    $normalizedOtherPlayer['isDead'] = [
                        'day' => null,
                        'cycle' => null,
                        'cause' => $this->normalizeEndReason(EndCauseEnum::STILL_LIVING, $language),
                    ];
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
                LanguageEnum::END_CAUSE,
                $language
            ),
            'description' => $this->translationService->translate(
                $endCause . '.description',
                [],
                LanguageEnum::END_CAUSE,
                $language
            ),
        ];
    }
}
