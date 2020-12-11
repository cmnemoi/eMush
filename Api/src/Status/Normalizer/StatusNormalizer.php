<?php

namespace Mush\Status\Normalizer;

use Mush\Status\Entity\Status;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\MedicalCondition;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatusNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Status;
    }

    /**
     * @param Status $status
     *
     * @return array
     */
    public function normalize($status, string $format = null, array $context = [])
    {
        $statusName=$status->getName();
        $normedStatus=[
            'key' => $statusName,
            'name' => $this->translator->trans($statusName . '.name', [], 'statuses'),
            'description' => $this->translator->trans("{$statusName}.description", [], 'statusess'),
        ];

        if ($status instanceof ChargeStatus){
            $normedStatus['charge'] = $status->getCharge();
        }

        if ($status instanceof MedicalCondition){
            $normedStatus['effect'] = $this->translator->trans("{$statusName}.effect", [], 'statuses');
        }

        return $normedStatus;
    }
}
