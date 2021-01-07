<?php

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class ItemPileNormalizer implements ContextAwareNormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private TokenStorageInterface $tokenStorage;
    private GameEquipmentService $gameEquipmentService;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        GameEquipmentService $gameEquipmentService
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof Collection && $data->first() instanceof GameEquipment; //@TODO corriger ca
    }

    /**
     * @param mixed $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $piles = [];

        $items = $object->filter(fn (GameEquipment $equipment) => $equipment instanceof GameItem);

        foreach ($items as $item) {
            $hiddenStatus = $item->GetStatusByName(EquipmentStatusEnum::HIDDEN);
            if (!$hiddenStatus || ($hiddenStatus->getPlayer() === $this->getPlayer())) {
                //If item is stackable and there is already piles for this item
                if ($item->getEquipment()->isStackable() &&
                    ($pileItemKey = $this->getPileItemKey($item, $piles, $items))
                ) {
                    //@TODO if ration is contaminated put it on top of the pile
                    if (isset($piles[$pileItemKey]['number'])) {
                        $piles[$pileItemKey]['number'] = $piles[$pileItemKey]['number'] + 1;
                    } else {
                        $piles[$pileItemKey]['number'] = 2;
                    }
                } else {
                    $piles[] = $this->normalizer->normalize($item);
                }
            }
        }

        return $piles;
    }

    private function getPlayer(): Player
    {
        if (!$token = $this->tokenStorage->getToken()) {
            throw new AccessDeniedException('User should be logged to access that');
        }

        /** @var User $user */
        $user = $token->getUser();

        if (!$player = $user->getCurrentGame()) {
            throw new AccessDeniedException('User should be in game to access that');
        }

        return $player;
    }

    private function getPileItemKey(GameEquipment $gameEquipment, array &$pile, Collection $gameEquipments): ?int
    {
        if (empty($pile)) {
            return null;
        }

        $pileItemKey = key(array_filter(
            $pile,
            fn ($pileItem) => (
                $pileItem['key'] === $gameEquipment->getName() && $this->isSamePile($gameEquipment, $pileItem, $gameEquipments)
            )
        ));

        return $pileItemKey;
    }

    private function isSamePile(GameEquipment $gameEquipment, array $pile, Collection $items): bool
    {
        $itemStatuses = $gameEquipment->getStatuses();

        /** @var GameEquipment $pileTopEquipment */
        $pileTopEquipment = $items->filter(fn (GameEquipment $gameEquipment) => $gameEquipment->getId() === $pile['id'])->first();

        $pileStatuses = $pileTopEquipment->getStatuses();

        //if the item is a doc stack the one with the same content (ie same document_content status)
        $statusName = EquipmentStatusEnum::DOCUMENT_CONTENT;
        if (($itemStatuses->filter(fn (Status $status) => ($status->getName() === $statusName))->isEmpty() !==
                $pileStatuses->filter(fn (Status $status) => ($status->getName() === $statusName))->isEmpty()) ||
            (!$itemStatuses->filter(fn (Status $status) => ($status->getName() === $statusName))->isEmpty() &&
                $itemStatuses->filter(fn (Status $status) => ($status->getName() === $statusName))->first()->getContent() !==
                $pileStatuses->filter(fn (Status $status) => ($status->getName() === $statusName))->first()->getContent())) {
            return false;
        }

        // in other cases check that the status on the item are the same (ie same Name)
        foreach (EquipmentStatusEnum::splitItemPileStatus() as $statusName) {
            if ($itemStatuses->filter(fn (Status $status) => ($status->getName() === $statusName))->isEmpty() !==
                $pileStatuses->filter(fn (Status $status) => ($status->getName() === $statusName))->isEmpty()) {
                return false;
            }
        }

        //mush player see contaminated rations in a different pile
        $statusName = EquipmentStatusEnum::CONTAMINATED;
        if ($itemStatuses->filter(fn (Status $status) => ($status->getName() === $statusName))->isEmpty() ===
            $pileStatuses->filter(fn (Status $status) => ($status->getName() === $statusName))->isEmpty() &&
            $this->getPlayer()->isMush()) {
            return false;
        }

        return true;
    }
}
