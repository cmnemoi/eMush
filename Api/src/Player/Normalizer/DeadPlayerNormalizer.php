<?php

namespace Mush\Player\Normalizer;

use Mush\Game\Enum\GameStatusEnum;
use Mush\Game\Enum\LanguageEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Player\Enum\EndCauseEnum;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DeadPlayerNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService,
    ) {
        $this->translationService = $translationService;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Player::class => false,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        $currentPlayer = $context['currentPlayer'] ?? null;

        return $data instanceof Player
            && $data === $currentPlayer
            && $data->getPlayerInfo()->getGameStatus() === GameStatusEnum::FINISHED;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        /** @var Player $player */
        $player = $object;

        $daedalus = $player->getDaedalus();
        $language = $daedalus->getLanguage();
        $character = $player->getName();
        $playerInfo = $player->getPlayerInfo();
        $deadPlayerInfo = $playerInfo->getClosedPlayer();

        $endCause = $deadPlayerInfo->getEndCause();

        $context['language'] = $language;
        $context['isMush'] = $player->isMush();

        $playerData = [
            'id' => $player->getId(),
            'character' => [
                'key' => $character,
                'value' => $this->translationService->translate($character . '.name', [], 'characters', $language),
            ],
            'triumph' => [
                'name' => $this->translationService->translate('triumph.name', [], 'player', $language),
                'description' => $this->translationService->translate('triumph.description', [], 'player', $language),
                'quantity' => $player->getTriumph(),
            ],
            'daedalus' => [
                'key' => $daedalus->getId(),
                'calendar' => [
                    'name' => $this->translationService->translate('calendar.name', [], 'daedalus', $language),
                    'description' => $this->translationService->translate('calendar.description', [], 'daedalus', $language),
                    'cycle' => $daedalus->getCycle(),
                    'cycleName' => $this->translationService->translate(
                        key: 'cycle.name',
                        parameters: [],
                        domain: 'daedalus',
                        language: $language
                    ),
                    'day' => $daedalus->getDay(),
                    'dayName' => $this->translationService->translate(
                        key: 'day.name',
                        parameters: [],
                        domain: 'daedalus',
                        language: $language
                    ),
                ],
            ],
            'gameStatus' => $playerInfo->getGameStatus(),
            'endCause' => $this->normalizeEndReason($endCause, $language),
            'isMush' => $player->isMush(),
            'triumphGains' => $this->normalizer->normalize($deadPlayerInfo->getTriumphGains(), $format, $context),
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
                $character = $otherPlayer->getName();

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

                $otherPlayerInfo = $otherPlayer->getPlayerInfo();
                $otherClosedPlayer = $otherPlayerInfo->getClosedPlayer();

                $normalizedOtherPlayer['deathDay'] = $otherClosedPlayer->getDayDeath();
                $normalizedOtherPlayer['deathCycle'] = $otherClosedPlayer->getCycleDeath();
                $normalizedOtherPlayer['likes'] = $otherClosedPlayer->getLikes();

                $endCause = $otherPlayerInfo->isAlive() ? EndCauseEnum::STILL_LIVING : $otherClosedPlayer->getEndCause();
                $normalizedOtherPlayer['endCause'] = $this->normalizeEndReason($endCause, $language);
                $otherPlayers[] = $normalizedOtherPlayer;
            }
        }

        return $otherPlayers;
    }

    private function normalizeEndReason(string $endCause, string $language): array
    {
        return [
            'key' => $endCause,
            'shortName' => $this->translationService->translate(
                $endCause . '.name',
                [],
                LanguageEnum::END_CAUSE,
                $language
            ),
            'name' => $this->translationService->translate(
                $endCause . '.short_name',
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
