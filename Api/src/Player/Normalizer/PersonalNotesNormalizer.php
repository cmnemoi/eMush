<?php

declare(strict_types=1);

namespace Mush\Player\Normalizer;

use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\PersonalNotes;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PersonalNotesNormalizer implements NormalizerInterface
{
    public function __construct(
        private TranslationServiceInterface $translationService,
        private PersonalNotesTabNormalizer $tabNormalizer
    ) {}

    public function getSupportedTypes(?string $format): array
    {
        return [
            PersonalNotes::class => true,
        ];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PersonalNotes;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $notes = $this->getPersonalNotes($object);
        $player = $notes->getPlayer();

        $tabs = [];
        foreach ($notes->getTabs() as $tab) {
            $tabs[] = $this->tabNormalizer->normalize($tab, $format, $context);
        }

        return [
            'tabs' => $tabs,
            'hasAccess' => $player->canReachATalkie(),
        ];
    }

    private function getPersonalNotes(mixed $object): PersonalNotes
    {
        return $object instanceof PersonalNotes ? $object : throw new \InvalidArgumentException('This normalizer only supports PersonalNotes objects');
    }
}
