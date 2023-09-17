<?php

namespace Mush\Alert\Normalizer;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Place\Entity\Place;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AlertNormalizer implements NormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService,
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Alert;
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Alert $alert */
        $alert = $object;

        $language = $alert->getDaedalus()->getLanguage();

        $key = $alert->getName();

        $normalizedAlert = [
            'key' => $key,
        ];

        if (($quantity = $this->getAlertQuantity($alert)) !== null) {
            $normalizedAlert['name'] = $this->translationService->translate(
                $alert->getName() . '.name',
                ['quantity' => $quantity],
                'alerts',
                $language
            );
            $normalizedAlert['description'] = $this->translationService->translate(
                "{$key}.description",
                ['quantity' => $quantity],
                'alerts',
                $language
            );
        } else {
            $normalizedAlert['name'] = $this->translationService->translate(
                $alert->getName() .
                '.name', [],
                'alerts',
                $language
            );
            $normalizedAlert['description'] = $this->translationService->translate(
                "{$key}.description",
                [],
                'alerts',
                $language
            );
        }

        if (!$alert->getAlertElements()->isEmpty()) {
            $normalizedAlert['reports'] = $this->handleAlertReport($alert, $language);
        }

        return $normalizedAlert;
    }

    private function getAlertQuantity(Alert $alert): ?int
    {
        if (!$alert->getAlertElements()->isEmpty()) {
            return $alert->getAlertElements()->count();
        }

        if ($alert->getName() === AlertEnum::LOW_HULL) {
            return $alert->getDaedalus()->getHull();
        }

        if ($alert->getName() === AlertEnum::HUNTER) {
            return $alert->getDaedalus()->getAttackingHunters()->count();
        }

        return null;
    }

    private function handleAlertReport(Alert $alert, string $language): array
    {
        $reports = [];

        /** @var AlertElement $element */
        foreach ($alert->getAlertElements() as $element) {
            $playerInfo = $element->getPlayerInfo();

            if ($playerInfo !== null) {
                /** @var Place $place */
                $place = $element->getPlace();

                $placeName = $this->translationService->translate(
                    $place->getName(),
                    [],
                    'rooms',
                    $language
                );
                $loc_prep = $this->translationService->translate(
                    $place->getName() . '.loc_prep',
                    [],
                    'rooms',
                    $language
                );
                $parameters = [
                    'character' => $playerInfo->getName(),
                    'place' => $placeName,
                    'loc_prep' => $loc_prep,
                ];

                $reports[] = $this->translationService->translate(
                    "{$alert->getName()}.report",
                    $parameters,
                    'alerts',
                    $language
                );
            }
        }

        return $reports;
    }
}
