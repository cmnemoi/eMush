<?php

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Service\GameEquipmentService;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class ItemPileNormalizer implements ContextAwareNormalizerInterface
{
    private EquipmentNormalizer $equipmentNormalizer;
    private TokenStorageInterface $tokenStorage;
    private GameEquipmentService $gameEquipmentService;

    public function __construct(
        EquipmentNormalizer $equipmentNormalizer,
        TokenStorageInterface $tokenStorage,
        GameEquipmentService $gameEquipmentService
    ) {
        $this->equipmentNormalizer = $equipmentNormalizer;
        $this->tokenStorage = $tokenStorage;
        $this->gameEquipmentService = $gameEquipmentService;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Collection && $data->first() instanceof GameEquipment; //@TODO corriger ca
    }

    /**
     * @param Collection $object
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $piles = [];

        $items = $object->filter(fn (GameEquipment $equipment) => $equipment instanceof GameItem);

        foreach ($items as $item) {
            $itemName = $item->getEquipment()->getName();
            $itemStatuses = $item->getStatuses();

            if ((!$item->GetStatusByName(EquipmentStatusEnum::HIDDEN) ||
                    ($item->GetStatusByName(EquipmentStatusEnum::HIDDEN) &&
                    $item->GetStatusByName(EquipmentStatusEnum::HIDDEN)->getPlayer() === $this->getUser()->getCurrentGame()))) {
                if ($item->getEquipment()->isStackable() &&
                    count(array_filter($piles, function ($pile) use ($itemName, $itemStatuses) {
                        return $pile['key'] === $itemName && $this->compareStatusesForPiles($itemStatuses, $pile['id']);
                    })) > 0) {
                    //@TODO if ration is contaminated put it on top of the pile

                    $pileKey = array_search(current(array_filter($piles, function ($pile) use ($itemName, $itemStatuses) {
                        return $pile['key'] === $itemName && $this->compareStatusesForPiles($itemStatuses, $pile['id']);
                    })), $piles);

                    if (array_key_exists('number', $piles[$pileKey])) {
                        $piles[$pileKey]['number'] = $piles[$pileKey]['number'] + 1;
                    } else {
                        $piles[$pileKey]['number'] = 2;
                    }
                } else {
                    $piles[] = $this->equipmentNormalizer->normalize($item);
                }
            }
        }

        return $piles;
    }

    /**
     * @return \Stringable|\Symfony\Component\Security\Core\User\UserInterface|string
     */
    private function getUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    private function compareStatusesForPiles(Collection $itemStatuses, int $pileId): bool
    {
        $pileStatuses = $this->gameEquipmentService->findById($pileId)->getStatuses();

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
             $this->getUser()->getCurrentGame()->isMush()) {
            return false;
        }

        return true;
    }
}
