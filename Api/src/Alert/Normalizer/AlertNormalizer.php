<?php

namespace Mush\Alert\Normalizer;

use Mush\Alert\Entity\Alert;
use Mush\Alert\Enum\AlertEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class AlertNormalizer implements ContextAwareNormalizerInterface
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

        $language = $alert->getDaedalus()->getGameConfig()->getLanguage();

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

        return null;
    }

    private function handleAlertReport(Alert $alert, string $language): array
    {
        $reports = [];

        foreach ($alert->getAlertElements() as $element) {
            if ($element->getPlayer() !== null) {
                $place = $this->translationService->translate(
                    $element->getPlace()->getName() . '.name',
                    [],
                    'rooms',
                    $language
                );
                $loc_prep = $this->translationService->translate(
                    $element->getPlace()->getName() . '.loc_prep',
                    [],
                    'rooms',
                    $language
                );
                $parameters = [
                    'character' => $element->getPlayer()->getCharacterConfig()->getName(),
                    'place' => $place,
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
