<?php

declare(strict_types=1);

namespace Mush\MetaGame\Normalizer;

use Mush\MetaGame\ViewModel\FillingDaedalusViewModel;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class FillingDaedalusViewModelNormalizer implements NormalizerInterface
{
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FillingDaedalusViewModel;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            FillingDaedalusViewModel::class => true,
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        // @var FillingDaedalusViewModel $object
        return $object->toArray();
    }
}
