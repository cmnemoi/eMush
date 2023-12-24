<?php

declare(strict_types=1);

namespace Mush\Hunter\Service;

use Mush\Daedalus\Entity\Daedalus;
use Mush\Hunter\Entity\HunterCollection;
use Mush\Hunter\Enum\HunterEnum;
use Mush\Hunter\Enum\HunterVariableEnum;

class HunterNormalizerHelper implements HunterNormalizerHelperInterface
{
    private const NUMBER_OF_HUNTERS_TO_NORMALIZE = 17;

    /**
     * Function which returns the hunters to normalize.
     * We don't normalize all attacking hunters, because there would be too many of them displayed on the screen.
     * This function :
     * - caps the number of hunters to normalize to 17
     * - ensures that there is at least one hunter of each type
     * - prioritizes hunters with low health.
     */
    public function getHuntersToNormalize(Daedalus $daedalus): HunterCollection
    {
        $attackingHunters = $daedalus->getAttackingHunters()->getAllHuntersSortedBy(HunterVariableEnum::HEALTH);

        // we want to normalize only 17 hunters. If there are less than 17 hunters, no treatment is needed
        if ($attackingHunters->count() <= self::NUMBER_OF_HUNTERS_TO_NORMALIZE) {
            return $attackingHunters;
        }

        $huntersToNormalize = new HunterCollection();

        // we need one advanced hunter of each type
        foreach ($this->getAdvancedHuntersToNormalize($attackingHunters) as $hunter) {
            $huntersToNormalize->add($hunter);
        }

        // then we fill the rest with simple hunters
        $numberOfSimpleHuntersToNormalize = self::NUMBER_OF_HUNTERS_TO_NORMALIZE - $huntersToNormalize->count();
        foreach ($this->getSimpleHuntersToNormalize($attackingHunters, $numberOfSimpleHuntersToNormalize) as $hunter) {
            $huntersToNormalize->add($hunter);
        }

        return $huntersToNormalize;
    }

    private function getAdvancedHuntersToNormalize(HunterCollection $hunters): HunterCollection
    {
        $advancedHunters = $hunters->getAllHuntersExcept(HunterEnum::HUNTER);
        $advancedHuntersToNormalize = new HunterCollection();

        foreach (HunterEnum::getAdvancedHunters() as $hunterName) {
            $advancedHunter = $advancedHunters->getOneHunterByType($hunterName);
            if (!$advancedHunter) {
                continue;
            }

            $advancedHuntersToNormalize->add($advancedHunter);
        }

        return $advancedHuntersToNormalize;
    }

    private function getSimpleHuntersToNormalize(HunterCollection $hunters, int $number): HunterCollection
    {
        return new HunterCollection($hunters->getAllHuntersByType(HunterEnum::HUNTER)->slice(0, $number));
    }
}
