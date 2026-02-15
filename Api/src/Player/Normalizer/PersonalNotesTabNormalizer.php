<?php

declare(strict_types=1);

namespace Mush\Player\Normalizer;

use Mush\Player\Entity\PersonalNotesTab;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class PersonalNotesTabNormalizer implements NormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return [
            PersonalNotesTab::class => true,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PersonalNotesTab;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $tab = $this->getTab($object);

        return [
            'id' => $tab->getId(),
            'index' => $tab->getIndex(),
            'icon' => $tab->getIcon(),
            'content' => $tab->getContent(),
        ];
    }

    private function getTab(mixed $object): PersonalNotesTab
    {
        return $object instanceof PersonalNotesTab ? $object : throw new \InvalidArgumentException('This normalizer only supports CommanderMission objects');
    }
}
