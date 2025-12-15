<?php

namespace Mush\Status\Normalizer;

use Mush\Game\Enum\VisibilityEnum;
use Mush\Game\Service\TranslationServiceInterface;
use Mush\Player\Entity\Player;
use Mush\Skill\Enum\SkillEnum;
use Mush\Status\Entity\ChargeStatus;
use Mush\Status\Entity\Status;
use Mush\Status\Entity\StatusHolderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class StatusNormalizer implements NormalizerInterface
{
    public function __construct(private TranslationServiceInterface $translationService) {}

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Status;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Status::class => true];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $status = $this->status($object);
        $statusName = $status->getName();

        $currentPlayer = $this->currentPlayer($context);
        $language = $currentPlayer->getDaedalus()->getLanguage();

        $statusNotVisible = $this->isVisible($status->getVisibility(), $currentPlayer, $status->getOwner(), $status->getTarget()) === false;
        $parameters = [];

        if ($statusNotVisible) {
            return [];
        }

        $parameters[$status->getVisibleOwner()->getLogKey()] = $status->getVisibleOwner()->getLogName();

        $normalizedStatus = [
            'id' => $status->getId(),
            'key' => $statusName,
            'isPrivate' => $status->getVisibility() === VisibilityEnum::PRIVATE,
        ];

        if (
            $status instanceof ChargeStatus
            && $this->isVisible($status->getChargeVisibility(), $currentPlayer, $status->getOwner(), $status->getTarget())
        ) {
            $parameters['charge'] = $status->getCharge();
            $normalizedStatus['charge'] = $status->getCharge();
        }

        if (($target = $status->getTarget()) !== null) {
            $normalizedStatus['target'] = ['key' => $target->getName(), 'id' => $target->getId()];
            $parameters['target'] = $target->getName();
        }

        $normalizedStatus['name'] = $this->translationService->translate(
            key: "{$statusName}.name",
            parameters: $parameters,
            domain: 'status',
            language: $language
        );

        $normalizedStatus['description'] = $this->translationService->translate(
            key: "{$statusName}.description",
            parameters: $parameters,
            domain: 'status',
            language: $language
        );

        return $normalizedStatus;
    }

    private function isVisible(
        string $visibility,
        Player $currentPlayer,
        ?StatusHolderInterface $statusOwner,
        ?StatusHolderInterface $statusTarget,
    ): bool {
        if ($this->isPublicOrSpecialVisibility($visibility, $currentPlayer)) {
            return true;
        }

        $player = $this->getRelevantPlayer($statusOwner, $statusTarget);
        if ($player === null) {
            return false;
        }

        return $visibility === VisibilityEnum::PRIVATE && $player === $currentPlayer;
    }

    private function isPublicOrSpecialVisibility(string $visibility, Player $currentPlayer): bool
    {
        return $visibility === VisibilityEnum::PUBLIC
            || ($visibility === VisibilityEnum::MUSH && $currentPlayer->isMush())
            || ($visibility === VisibilityEnum::CHEF && ($currentPlayer->hasSkill(SkillEnum::CHEF) || $currentPlayer->isMush()));
    }

    private function getRelevantPlayer(?StatusHolderInterface $statusOwner, ?StatusHolderInterface $statusTarget): ?Player
    {
        if ($statusOwner instanceof Player) {
            return $statusOwner;
        }
        if ($statusTarget instanceof Player) {
            return $statusTarget;
        }

        return null;
    }

    private function status(mixed $object): Status
    {
        return $object instanceof Status ? $object : throw new \RuntimeException('This normalizer only supports Status');
    }

    private function currentPlayer(array $context): Player
    {
        return $context['currentPlayer'] ?? throw new \RuntimeException('This normalizer requires a currentPlayer in the context');
    }
}
