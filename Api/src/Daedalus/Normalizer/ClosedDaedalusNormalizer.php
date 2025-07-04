<?php

namespace Mush\Daedalus\Normalizer;

use Mush\Daedalus\Entity\ClosedDaedalus;
use Mush\Game\Service\CycleServiceInterface;
use Mush\Game\Service\RandomServiceInterface;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Project\Enum\ProjectType;
use Mush\Triumph\Service\ChangeTriumphFromEventService;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ClosedDaedalusNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const string ALREADY_CALLED = 'CLOSED_DAEDALUS_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly ChangeTriumphFromEventService $triumphFromEventService,
        private readonly CycleServiceInterface $cycleService,
        private readonly RandomServiceInterface $randomService,
        private readonly TranslationServiceInterface $translationService
    ) {}

    public function getSupportedTypes(?string $format): array
    {
        return [
            ClosedDaedalus::class => false,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        // Make sure we're not called twice
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof ClosedDaedalus;
    }

    public function normalize($object, $format = null, array $context = []): array
    {
        /** @var ClosedDaedalus $daedalus */
        $daedalus = $object;

        $context[self::ALREADY_CALLED] = true;

        $normalizedDaedalus = $this->normalizer->normalize($object, $format, $context);

        if (!\is_array($normalizedDaedalus)) {
            throw new \Exception('normalized closedDaedalus should be an array');
        }

        if (!$daedalus->isDaedalusFinished()) {
            return $normalizedDaedalus;
        }

        $normalizedDaedalus['cyclesSurvived'] = $this->cycleService->getNumberOfCycleElapsed(
            start: $daedalus->getCreatedAtOrThrow(),
            end: $daedalus->getFinishedAtOrThrow(),
            daedalusInfo: $daedalus->getDaedalusInfo()
        );
        $normalizedDaedalus['daysSurvived'] = (int) ($normalizedDaedalus['cyclesSurvived'] / $daedalus->getDaedalusInfo()->getGameConfig()->getDaedalusConfig()->getCyclePerGameDay());
        $normalizedDaedalus['statistics'] = $this->getNormalizedStatistics($daedalus);
        $normalizedDaedalus['projects'] = $this->getNormalizedProjects($daedalus);
        $normalizedDaedalus['titleHolders'] = $this->getNormalizedTitleHolders($daedalus);
        $normalizedDaedalus['funFacts'] = $this->getNormalizedRandomFunFacts($daedalus);

        return $normalizedDaedalus;
    }

    private function getNormalizedStatistics(ClosedDaedalus $daedalus): array
    {
        $normalizedStatistics = [];
        $normalizedStatistics['title'] = $this->translationService->translate(
            key: 'statistics',
            parameters: [],
            domain: 'the_end',
            language: $daedalus->getLanguage()
        );
        foreach ($daedalus->getDaedalusInfo()->getDaedalusStatistics()->toArray() as $statistic) {
            $normalizedStatistics['lines'][] = [
                'name' => $this->translationService->translate(
                    key: $statistic->name,
                    parameters: [],
                    domain: 'the_end',
                    language: $daedalus->getLanguage()
                ),
                'value' => $statistic->value,
            ];
        }

        return $normalizedStatistics;
    }

    private function getNormalizedProjects(ClosedDaedalus $daedalus): array
    {
        $normalizedProjects = [];

        foreach ($daedalus->getDaedalusInfo()->getDaedalusProjectsStatistics()->toArray() as $categoryBaseName => $category) {
            $categoryName = $this->translationService->translate(
                key: $categoryBaseName,
                parameters: [],
                domain: 'the_end',
                language: $daedalus->getLanguage()
            );

            $normalizedProjects[$categoryBaseName] = [
                'title' => $categoryName,
                'lines' => [],
            ];

            foreach ($category as $projectBaseName) {
                $normalizedProjects[$categoryBaseName]['lines'][] = [
                    'type' => ProjectType::fromCategory($categoryBaseName)->toString(),
                    'key' => $projectBaseName,
                    'name' => $this->translationService->translate(
                        key: "{$projectBaseName}.name",
                        parameters: [],
                        domain: 'project',
                        language: $daedalus->getLanguage()
                    ),
                    'description' => $this->getDescriptionForProjectName(
                        projectName: $projectBaseName,
                        daedalus: $daedalus
                    ),
                    'lore' => $this->translationService->translate(
                        key: "{$projectBaseName}.lore",
                        parameters: [],
                        domain: 'project',
                        language: $daedalus->getLanguage()
                    ),
                ];
            }
        }

        return $normalizedProjects;
    }

    private function getNormalizedTitleHolders(ClosedDaedalus $daedalus): array
    {
        $normalizedTitleHolders = [];

        foreach ($daedalus->getDaedalusInfo()->getGameConfig()->getTitleConfigs() as $titleConfig) {
            $translatedTitle = $this->translationService->translate(
                key: $titleConfig->getName(),
                parameters: [],
                domain: 'the_end',
                language: $daedalus->getLanguage()
            );
            $normalizedTitleHolders[$titleConfig->getName()] = [
                'title' => $translatedTitle,
                'characterKeys' => [],
            ];
        }

        foreach ($daedalus->getDaedalusInfo()->getTitleHolders() as $title => $playerNames) {
            $normalizedTitleHolders[$title]['characterKeys'] = $playerNames;
        }

        return $normalizedTitleHolders;
    }

    private function getNormalizedRandomFunFacts(ClosedDaedalus $daedalus): array
    {
        $normalizedFunFacts = [];

        $funFacts = $daedalus->getDaedalusInfo()->getFunFacts();

        for ($i = 0; $i < 5; ++$i) {
            if (\count($funFacts) === 0) {
                break;
            }
            $name = array_rand($funFacts);
            $translatedName = $this->translationService->translate(
                key: "{$name}.name",
                parameters: [],
                domain: 'fun_facts',
                language: $daedalus->getLanguage()
            );
            $translatedDescription = $this->translationService->translate(
                key: "{$name}.description",
                parameters: [],
                domain: 'fun_facts',
                language: $daedalus->getLanguage()
            );

            $displayedCharacter = (string) $this->randomService->getRandomElement($funFacts[$name]);
            $normalizedFunFacts[] = [
                'title' => $translatedName,
                'description' => $translatedDescription,
                'characterKey' => $displayedCharacter,
            ];
            unset($funFacts[$name]);
        }

        return $normalizedFunFacts;
    }

    private function getDescriptionForProjectName(string $projectName, ClosedDaedalus $daedalus): string
    {
        $translatedProjectDescription = $this->translationService->translate(
            key: "{$projectName}.description",
            parameters: [],
            domain: 'project',
            language: $daedalus->getLanguage()
        );
        $triumphQuantity = $this->triumphFromEventService->getProjectFinishedTriumph($projectName, $daedalus);
        $translatedTriumphGain = $triumphQuantity !== 0 ? $this->translationService->translate(
            key: 'project_grants_triumph',
            parameters: ['quantity' => $triumphQuantity],
            domain: 'misc',
            language: $daedalus->getLanguage()
        ) : '';

        return implode('//', array_filter([$translatedProjectDescription, $translatedTriumphGain]));
    }
}
