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
        /** @var Alert $normalizedAlert */
        $alert = $object;

        $key = $alert->getName();

        $normalizedAlert = [
            'key' => $key,
        ];

        if ($quantity = $this->getAlertQuantity($alert) !== null) {
            $normalizedAlert['name'] = $this->translationService->translate($alert->getName() . '.name', ['quantity' => $quantity], 'alerts');
            $normalizedAlert['description'] = $this->translationService->translate("{$key}.description", ['quantity' => $quantity], 'alerts');
        } else {
            $normalizedAlert['name'] = $this->translationService->translate($alert->getName() . '.name', [], 'alerts');
            $normalizedAlert['description'] = $this->translationService->translate("{$key}.description", [], 'alerts');
        }

        if (!$alert->getAlertElements()->isEmpty()) {
            $normalizedAlert['reports'] = $this->handleAlertReport($alert);
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

    private function handleAlertReport(Alert $alert): array
    {
        $reports = [];

        foreach ($alert->getAlertElements() as $element) {
            if ($element->getPlayer() !== null) {
                $parameters = ['character' => $element->getPlayer()->getCharacterConfig()->getName(), 'place' => $element->getPlace()->getName()];
                $reports[] = $this->translationService->translate("{$alert->getName()}.report", $parameters, 'alerts');
            }
        }

        return $reports;
    }
}
