<?php

namespace Mush\Disease\Normalizer;

use Mush\Disease\Entity\PlayerDisease;
use Mush\Game\Service\TranslationServiceInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class DiseaseNormalizer implements ContextAwareNormalizerInterface
{
    private TranslationServiceInterface $translationService;

    public function __construct(
        TranslationServiceInterface $translationService
    ) {
        $this->translationService = $translationService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof PlayerDisease;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $diseaseConfig = $object->getDiseaseConfig();

        return [
           'key' => $diseaseConfig->getName(),
           'name' => $this->translationService->translate($diseaseConfig->getName() . '.name', [], 'disease'),
           'type' => $diseaseConfig->getType(),
           'description' => $this->translationService->translate("{$diseaseConfig->getName()}.description", [], 'disease'),
       ];
    }
}
