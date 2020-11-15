<?php

namespace Mush\Item\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Mush\Game\DataFixtures\GameConfigFixtures;
use Mush\Game\Entity\GameConfig;
use Mush\Game\Enum\SkillEnum;
use Mush\Item\Entity\Items\Fruit;
use Mush\Item\Entity\Items\Plant;
use Mush\Item\Enum\GameFruitEnum;
use Mush\Item\Enum\GamePlantEnum;
use Mush\Action\Enum\SpecialEffectEnum;
use Mush\Status\Enum\DiseaseEnum;
use Mush\Status\Enum\DisorderEnum;

class ItemConfigFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        /** @var GameConfig $gameConfig */
        $gameConfig = $this->getReference(GameConfigFixtures::DEFAULT_GAME_CONFIG);

        

        $bananaType = new Fruit();
        $bananaType
            ->setActionPoints([1])
            ->setMovementPoints([0])
            ->setHealthPoints([1])
            ->setMoralPoints([1])

        ;

        $banana = new Item();
        $banana
            ->setGameConfig($gameConfig)
            ->setName(GameFruitEnum::BANANA)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$bananaType]))
        ;
        $manager->persist($bananaType);
        $manager->persist($banana);

        $bananaTreeType = new Plant();
        $bananaTreeType
            ->setFruit($banana)
            ->setMaxMaturationTime(36)
            ->setMinMaturationTime(36)
            ->setMaxOxygen(1)
            ->setMinOxygen(1)
        ;

        $bananaTree = new Item();
        $bananaTree
            ->setGameConfig($gameConfig)
            ->setName(GamePlantEnum::BANANA_TREE)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$bananaTreeType]))
        ;
        $manager->persist($bananaTreeType);
        $manager->persist($bananaTree);


        $alienFruitPlant=[
            GamePlantEnum::CREEPNUT => GameFruitEnum::CREEPIST,
            GamePlantEnum::MEZTINE => GameFruitEnum::CACTAX,
            GamePlantEnum::GUNTIFLOP => GameFruitEnum::BIFFLON,
            GamePlantEnum::PLOSHMINA => GameFruitEnum::PULMMINAGRO,
            GamePlantEnum::PRECATI => GameFruitEnum::PRECATUS,
            GamePlantEnum::BOTTINE => GameFruitEnum::BUTTALIEN,
            GamePlantEnum::FRAGILANE => GameFruitEnum::PLATACIA,
            GamePlantEnum::ANEMOLE => GameFruitEnum::TUBILISCUS,
            GamePlantEnum::PENICRAFT => GameFruitEnum::GRAAPSHOOT,
            GamePlantEnum::KUBINUS => GameFruitEnum::FIBONICCUS,
            GamePlantEnum::CALEBOOT => GameFruitEnum::MYCOPIA,
            GamePlantEnum::FILANDRA => GameFruitEnum::ASPERAGUNK
            ]
        
        // @TODO change the structure to include the number of cycle before the disease start
        $alienFruitType = new Fruit();
	        $alienFruitType
	            ->setActionPoints([1=>90 , 2=>9 , 3=>1])
	            ->setMovementPoints([0])
	            ->setHealthPoints([0])
	            ->setMoralPoints([0=>30,1=>70])
		         ->setDiseasesNames([
		                DiseaseEnum::CAT_ALLERGY => 1,
							DiseaseEnum::MUSH_ALLERGY => 1,
							DiseaseEnum::SEPSIS => 1,
							DiseaseEnum::SLIGHT_NAUSEA => 1,
							DiseaseEnum::SMALLPOX => 1,
							DiseaseEnum::SYPHILIS => 1,
							DisorderEnum::AILUROPHOBIA => 1,
							DisorderEnum::COPROLALIA => 1,
							DisorderEnum::SPLEEN => 1,
							DisorderEnum::WEAPON_PHOBIA => 1,
							DisorderEnum::CHRONIC_VERTIGO => 1,
							DisorderEnum::PARANOIA => 1,
							DiseaseEnum::ACID_REFLUX => 2,
							DiseaseEnum::SKIN_INFLAMMATION => 2,
							DisorderEnum::AGORAPHOBIA => 2,
							DisorderEnum::CHRONIC_MIGRAINE => 2,
							DisorderEnum::VERTIGO => 2,
							DisorderEnum::DEPRESSION => 2,
							DisorderEnum::PSYCOTIC_EPISODE => 2,
							DisorderEnum::CRABISM => 4,
							DiseaseEnum::BLACK_BITE => 4,
							DiseaseEnum::COLD => 4,
							DiseaseEnum::EXTREME_TINNITUS => 4,
							DiseaseEnum::FOOD_POISONING => 4,
							DiseaseEnum::FUNGIC_INFECTION => 4,
							DiseaseEnum::REJUVENATION => 4,
							DiseaseEnum::RUBELLA => 4,
							DiseaseEnum::SINUS_STORM => 4,
							DiseaseEnum::SPACE_RABIES => 4,
							DiseaseEnum::VITAMIN_DEFICIENCY => 4,
							DiseaseEnum::FLU => 8,
							DiseaseEnum::GASTROENTERIS => 8,
							DisorderEnum::MIGRAINE => 8,
							DiseaseEnum::TAPEWORM => 8])
				   ->setDiseaseChances([100 => 64, 25 => 1, 30 => 2,35=> 3,40=> 4,45=> 5,
				                                       50=> 6, 55=> 5,60=> 4,65=> 3,70=> 2, 65 => 1])
		         ->setDiseaseDelayMin([0 => 1,1 => 1,2 =>1 ,3 => 1,4 => 1,5 =>1 ,
		                                              6 =>1 ,7 => 1,8 => 1,9 => 1,10 => 1, 11 => 1])
		         ->setDiseaseDelayLengh([0 => 1,1 => 1,2 =>1 ,3 => 1,4 => 1,5 =>1 ,6 =>1 ,7 => 1,8 => 1])
		         ->setEffectsNumber([0 => 35, 1 => 40 , 2 => 15, 3 => 9,4 => 1])
		         ->setExtraEffect([ExtraEffectEnum::EXTRA_PA_GAIN=> 50])
	        ;
	        $manager->persist($alienFruitType);
	        
        foreach($alienFruitPlant as $fruitName=>$plantName){
	        $alienFruit = new Item();
           $alienFruit
            ->setGameConfig($gameConfig)
            ->setName($fruitName)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$alienFruitType]))
            ;
            $manager->persist($alienFruit);
	        
	        $alienPlantType = new Plant();
	        $alienPlantType
	            ->setFruit($alienFruit)
	            ->setMaxMaturationTime(46)
	            ->setMinMaturationTime(1)
	            ->setMaxOxygen(1)
	            ->setMinOxygen(1)
	        ;
	        
	         $alienPlant = new Item();
            $alienPlant
            ->setGameConfig($gameConfig)
            ->setName($plantName)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$alienPlantType]))
        ;
        $manager->persist($alienPlantType);
        $manager->persist($alienPlant);
        }


        $junkinType = new Fruit();
        $junkinType
            ->setActionPoints([3])
            ->setMovementPoints([0])
            ->setHealthPoints([1])
            ->setMoralPoints([1])
            ->setDiseases([DiseaseEnum::JUNKBUMPKINITIS])
            ->setDiseasesNumber([1])
        ;

        $junkin
            ->setGameConfig($gameConfig)
            ->setName(GameFruitEnum::JUNKIN)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(true)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$junkinType]))
        ;
        $manager->persist($junkinType);
        $manager->persist($junkin);

        $bumpjunkinType = new Plant();
        $bumpjunkinType
            ->setFruit($junkin)
            ->setMaxMaturationTime(7)
            ->setMinMaturationTime(11)
            ->setMaxOxygen(1)
            ->setMinOxygen(1)
        ;

        $bananaTree = new Item();
        $bananaTree
            ->setGameConfig($gameConfig)
            ->setName(GamePlantEnum::BUMPJUNKIN)
            ->setIsHeavy(false)
            ->setIsTakeable(true)
            ->setIsDropable(true)
            ->setIsStackable(false)
            ->setIsHideable(true)
            ->setIsFireDestroyable(true)
            ->setIsFireBreakable(false)
            ->setTypes(new ArrayCollection([$bumpjunkinType]))
        ;
        $manager->persist($bumpjunkinType);
        $manager->persist($bumpjunkin);
        

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GameConfigFixtures::class,
        ];
    }
}
