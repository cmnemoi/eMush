<?php

declare(strict_types=1);

namespace Mush\Communication\Normalizer;

use Mush\Communication\Entity\Channel;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\CommanderMission;
use Mush\Player\Entity\Player;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class TipsChannelNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(private readonly TranslationServiceInterface $translationService) {}

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Channel && $data->isTipsChannel();
    }

    public function normalize(mixed $object, ?string $format = null, array $context = [])
    {
        /** @var Channel $channel */
        $channel = $object;

        /** @var Player $currentPlayer */
        $currentPlayer = $context['currentPlayer'];

        return [
            'name' => $this->translationService->translate(
                key: \sprintf('%s.name', $channel->getScope()),
                parameters: [],
                domain: 'chat',
                language: $currentPlayer->getLanguage(),
            ),
            'description' => $this->translationService->translate(
                key: \sprintf('%s.description', $channel->getScope()),
                parameters: [],
                domain: 'chat',
                language: $currentPlayer->getLanguage(),
            ),
            'tips' => [
                'teamObjectives' => $this->getTeamObjectives($currentPlayer),
                'characterObjectives' => $this->getCharacterObjectives($currentPlayer),
                'externalResources' => [
                    'title' => $this->translationService->translate(
                        key: 'tips.externalResources.title',
                        parameters: [],
                        domain: 'chat',
                        language: $currentPlayer->getLanguage(),
                    ),
                    'elements' => $this->getExternalResources($currentPlayer),
                ],
                'missions' => [
                    'title' => $this->translationService->translate(
                        key: 'tips.missions.title',
                        parameters: [],
                        domain: 'chat',
                        language: $currentPlayer->getLanguage(),
                    ),
                    'elements' => $currentPlayer->getReceivedMissions()->map(
                        fn (CommanderMission $commanderMission) => $this->normalizer->normalize($commanderMission, $format, $context)
                    )->toArray(),
                    'buttons' => [
                        'accept' => $this->translationService->translate(
                            key: 'tips.missions.accept',
                            parameters: [],
                            domain: 'chat',
                            language: $currentPlayer->getLanguage(),
                        ),
                    ],
                ],
            ],
        ];
    }

    private function getCurrentPlayerTips(Player $currentPlayer): array
    {
        return explode('//', $this->translationService->translate(
            key: \sprintf('%s.tips', $currentPlayer->getName()),
            parameters: [],
            domain: 'characters',
            language: $currentPlayer->getLanguage()
        ));
    }

    private function getCharacterObjectives(Player $currentPlayer): array
    {
        return [
            'title' => $this->translationService->translate(
                key: 'tips.characterObjectives.title',
                parameters: [
                    'characterKey' => $currentPlayer->getName(),
                    'character' => $currentPlayer->getName(),
                ],
                domain: 'chat',
                language: $currentPlayer->getLanguage(),
            ),
            'elements' => $this->getCurrentPlayerTips($currentPlayer),
            'tutorial' => [
                'title' => $this->translationService->translate(
                    key: 'tips.characterObjectives.tutorial',
                    parameters: [],
                    domain: 'chat',
                    language: $currentPlayer->getLanguage()
                ),
                'link' => $this->translationService->translate(
                    key: \sprintf('%s.tutorial', $currentPlayer->getName()),
                    parameters: [],
                    domain: 'characters',
                    language: $currentPlayer->getLanguage()
                ),
            ],
        ];
    }

    private function getExternalResources(Player $currentPlayer): array
    {
        $crewmates = $this->translationService->translate(
            key: 'tips.externalResources.crewmates',
            parameters: [
                'team' => $currentPlayer->isMush() ? 'mush' : 'human',
            ],
            domain: 'chat',
            language: $currentPlayer->getLanguage()
        );
        $discord = $this->translationService->translate(
            key: 'tips.externalResources.discord',
            parameters: [],
            domain: 'chat',
            language: $currentPlayer->getLanguage()
        );
        $tutorials = $this->translationService->translate(
            key: 'tips.externalResources.tutorials',
            parameters: [],
            domain: 'chat',
            language: $currentPlayer->getLanguage()
        );
        $wiki = $this->translationService->translate(
            key: 'tips.externalResources.wiki',
            parameters: [],
            domain: 'chat',
            language: $currentPlayer->getLanguage()
        );
        $tutorialsLink = $this->translationService->translate(
            key: 'tips.externalResources.tutorialsLink',
            parameters: [],
            domain: 'chat',
            language: $currentPlayer->getLanguage()
        );
        $wikiLink = $this->translationService->translate(
            key: 'tips.externalResources.wikiLink',
            parameters: [],
            domain: 'chat',
            language: $currentPlayer->getLanguage()
        );

        return [
            ['text' => $crewmates],
            ['text' => $discord, 'link' => 'https://discord.gg/Jb8Nwjck6r'],
            ['text' => $tutorials, 'link' => $tutorialsLink],
            ['text' => $wiki, 'link' => $wikiLink],
        ];
    }

    private function getTeamObjectives(Player $currentPlayer): array
    {
        $teamObjectives = [
            'title' => $this->translationService->translate(
                key: 'tips.teamObjectives.title',
                parameters: [
                    'character' => $currentPlayer->getName(),
                    'team' => $currentPlayer->isMush() ? 'mush' : 'human',
                ],
                domain: 'chat',
                language: $currentPlayer->getLanguage(),
            ),
            'elements' => explode('//', $this->translationService->translate(
                key: 'tips.teamObjectives.elements',
                parameters: [
                    'team' => $currentPlayer->isMush() ? 'mush' : 'human',
                ],
                domain: 'chat',
                language: $currentPlayer->getLanguage(),
            )),
        ];

        if ($currentPlayer->isMush()) {
            $teamObjectives['tutorial'] = [
                'title' => $this->translationService->translate(
                    key: 'tips.teamObjectives.mushTutorialTitle',
                    parameters: [],
                    domain: 'chat',
                    language: $currentPlayer->getLanguage(),
                ),
                'link' => $this->translationService->translate(
                    key: 'tips.teamObjectives.mushTutorialLink',
                    parameters: [],
                    domain: 'chat',
                    language: $currentPlayer->getLanguage(),
                ),
            ];
        }

        return $teamObjectives;
    }
}
