<?php

namespace Mush\Disease\Normalizer;

use Mush\Disease\Entity\PlayerDisease;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DiseaseNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof PlayerDisease;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $diseaseConfig = $object->getDiseaseConfig();

        return [
           'key' => $diseaseConfig->getName(),
           'name' => $this->translator->trans($diseaseConfig->getName() . '.name', [], 'disease'),
           'description' => $this->translator->trans("{$diseaseConfig->getName()}.description", [], 'disease'),
       ];
    }
}
