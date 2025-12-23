<?php

namespace Mush\Equipment\Entity\Mechanics;

use Doctrine\ORM\Mapping as ORM;
use Mush\Equipment\Enum\EquipmentMechanicEnum;

/**
 * Class implementing Kits.
 * Kits are Blueprint extensions that aren't normalized as an item named "blueprint", and thus can be given a custom item icon, name and description.
 *
 * ...why aren't they just called blueprint_custom or something, then?
 * ...
 * Blame Félicie.
 * (more seriously, because the first batch was two ingredientless blueprints, so it made sense to call them kits based on that.
 * But nothing in the code screams in terror if you give them required ingredients anyway.
 * It just wouldn't have the ingredients list displayed in the tooltip, unless you hardcode it into the description yourself in all languages.
 * All it'd take is a minor refactor of mush/Api/src/Equipment/Normalizer/EquipmentNormalizer.php:311,
 * and you could have kits that automatically list their ingredients just like regular blueprints, while keeping their unique name, description and icon.
 * Feel free to update this entire comment if you ever take the time to do it. And give yourself a pat on the back from me, free of charge.)
 *
 * !!!! IT IS NOT RECOMMENDED TO CONVERT EVERY BLUEPRINT INTO A KIT.
 * Every Kit needs its own translation unit in every language,
 * while regular blueprints automatically handle themselves based on a single translation unit common to all blueprints.
 * The more kits = the more potentially duplicate translations. Make each use of a kit worth the trouble!
 */
#[ORM\Entity]
class Kit extends Blueprint
{
    public function getMechanics(): array
    {
        $mechanics = parent::getMechanics();
        $mechanics[] = EquipmentMechanicEnum::KIT;

        return $mechanics;
    }
}
