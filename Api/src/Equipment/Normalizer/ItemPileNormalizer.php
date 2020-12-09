<?php

namespace Mush\Equipment\Normalizer;

use Doctrine\Common\Collections\Collection;
use Mush\Action\Actions\Action;
use Mush\Action\Entity\ActionParameters;
use Mush\Action\Service\ActionServiceInterface;
use Mush\Equipment\Entity\Door;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Equipment\Enum\EquipmentMechanicEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\User\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ItemPileNormalizer implements ContextAwareNormalizerInterface
{
    private TranslatorInterface $translator;
    private ActionServiceInterface $actionService;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        TranslatorInterface $translator,
        ActionServiceInterface $actionService,
        TokenStorageInterface $tokenStorage
    ) {
        $this->translator = $translator;
        $this->actionService = $actionService;
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

            //@TODO don't display hidden items by other players
            if ($item->getEquipment()->isStackable() &&
                count(array_filter($piles, function ($pile) use ($itemName, $itemStatuses)
                         {return $pile['name'] === $itemName && $this->compareStatusesForPiles($itemStatuses, $pile['statuses']);}))>0){

                //@TODO mush player see contaminated rations in a different pile
                //@TODO if ration is contaminated put it on top of the pile

                $pile=array_filter($piles, function ($pile) use ($itemName, $itemStatuses)
                        {return $pile['name'] === $itemName && $this->compareStatusesForPiles($itemStatuses, $pile['statuses']);});
                
                if ($pile['number']){
                    $pile['number'] = $pile['number']+1;
                }else{
                    $pile['number'] = 2;
                }
                
            } else{
                $piles[]=$this->equipmentNormalizer->normalize($item);
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
            return $this->filterStatusesForPiles($itemStatuses)===$this->filterStatusesForPiles($pileStatuses);
        } else{
            $itemContent=$itemStatuses->filter(fn (Status $status) => $status->getName===EquipmentStatusEnum::DOCUMENT_CONTENT)->first()->getContent();
            $pileContent=$itemStatuses->filter(fn (Status $status) => $status->getName===EquipmentStatusEnum::DOCUMENT_CONTENT)->first()->getContent();
            return $this->filterStatusesForPiles($itemStatuses)===$this->filterStatusesForPiles($pileStatuses) && $itemContent===$pileContent;
        }
            
    }
}
