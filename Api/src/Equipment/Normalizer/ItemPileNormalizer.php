<?php

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Normalizer\EquipmentNormalizer;
use Mush\Equipment\Entity\GameItem;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ItemPileNormalizer implements ContextAwareNormalizerInterface
{
    private EquipmentNormalizer $equipmentNormalizer;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EquipmentNormalizer $equipmentNormalizer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->equipmentNormalizer = $equipmentNormalizer;
        $this->tokenStorage = $tokenStorage;
    }

    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof Collection;
    }

    /**
     * @param Collection $equipments
     *
     * @return array
     */
    public function normalize($equipments, string $format = null, array $context = [])
    {
        $piles = [];

        $items=$equipments->filter(fn (GameEquipment $equipment) => $equipment instanceof GameItem);

        

        foreach($items as $item){
            $itemName=$item->getEquipment()->getName();
            $itemStatuses=$item->getStatuses();
            
            if ($item->getEquipment()->isStackable() &&
                count(array_filter($piles, function ($pile) use ($itemName, $itemStatuses)
                         {return $pile['key'] === $itemName && $this->compareStatusesForPiles($itemStatuses, $pile['statuses']);}))>0){

                //@TODO mush player see contaminated rations in a different pile
                //@TODO if ration is contaminated put it on top of the pile

                $pileKey=array_search(current(array_filter($piles, function ($pile) use ($itemName, $itemStatuses)
                        {return $pile['key'] === $itemName && $this->compareStatusesForPiles($itemStatuses, $pile['statuses']);})), $piles);
                
                if (array_key_exists('number', $piles[$pileKey])){
                    $piles[$pileKey]['number'] = $piles[$pileKey]['number']+1;
                }else{
                    $piles[$pileKey]['number'] = 2;
                }

            } else{
                if(!(!$item->GetStatusByName(EquipmentStatusEnum::HIDDEN) &&
                    $item->GetStatusByName(EquipmentStatusEnum::HIDDEN)->getPlayer()!==$this->getUser()->getCurrentGame())){
                    $piles[]=$this->equipmentNormalizer->normalize($item);
                }
            }
        };

        return $piles;
    }

    private function getUser(): User
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    private function filterStatusesForPiles(Collection $statuses): Collection
    {
        return $statuses->filter(fn (Status $status) => 
            ($status->getName===EquipmentStatusEnum::HIDDEN ||
             $status->getName===EquipmentStatusEnum::BROKEN ||
             $status->getName===EquipmentStatusEnum::UNSTABLE ||
             $status->getName===EquipmentStatusEnum::HAZARDOUS ||
             $status->getName===EquipmentStatusEnum::DECOMPOSING ||
             $status->getName===EquipmentStatusEnum::FROZEN));
    }

    private function compareStatusesForPiles(Collection $itemStatuses, Collection $pileStatuses): bool
    {
        if ($itemStatuses->filter(fn (Status $status) => $status->getName===EquipmentStatusEnum::DOCUMENT_CONTENT)->isEmpty()){
            return $this->filterStatusesForPiles($itemStatuses)->toArray()===$this->filterStatusesForPiles($pileStatuses)->toArray();
        } else{
            $itemContent=$itemStatuses->filter(fn (Status $status) => $status->getName===EquipmentStatusEnum::DOCUMENT_CONTENT)->first()->getContent();
            $pileContent=$itemStatuses->filter(fn (Status $status) => $status->getName===EquipmentStatusEnum::DOCUMENT_CONTENT)->first()->getContent();
            return $this->filterStatusesForPiles($itemStatuses)->toArray()===$this->filterStatusesForPiles($pileStatuses)->toArray() && $itemContent===$pileContent;
        }
            
    }
}
