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
	            ->setActionPoints([1,2,3])
	            ->setMovementPoints([0])
	            ->setHealthPoints([0])
	            ->setMoralPoints([0,1])
	            ->setCures([
		                DiseaseEnum::ACID_REFLUX,
		                DiseaseEnum::BLACK_BITE,
		                DiseaseEnum::COLD,
		                DiseaseEnum::FLU,
		                DiseaseEnum::FUNGIC_INFECTION,
		                DiseaseEnum::GASTROENTERITIS,
		                DiseaseEnum::MIGRAINE,
		                DiseaseEnum::RUBELLA,
		                DiseaseEnum::SINUS_STORM,
		                DiseaseEnum::SPACE_RABIES,
		                DiseaseEnum::SYPHILIS,
		                DiseaseEnum::TAPEWORM,
		                DiseaseEnum::VITAMIN_DEFICIENCY,
		                DisorderEnum::CHRONIC_MIGRAINE,
		                DisorderEnum::CHRONIC_VERTIGO,
		                DisorderEnum::COPROLALIA,
		                DisorderEnum::DEPRESSION,
		                DisorderEnum::PSYCHOTIC_EPISODES,
		                DisorderEnum::VERTIGO])
		         ->setDiseases([
		                DiseaseEnum::ACID_REFLUX,
		                DiseaseEnum::EXTREME_TINNITUS,
		                DiseaseEnum::FLU,
		                DiseaseEnum::FOOD_POISONING,
		                DiseaseEnum::FUNGIC_INFECTION,
		                DiseaseEnum::MIGRAINE,
		                DiseaseEnum::MUSH_ALLERGY,
		                DiseaseEnum::REJUVENATION,
		                DiseaseEnum::RUBELLA,
		                DiseaseEnum::SINUS_STORM,
		                DiseaseEnum::SKIN_INFLAMMATION,
		                DiseaseEnum::SLIGHT_NAUSEA,
		                DiseaseEnum::SMALLPOX,
		                DiseaseEnum::SPACE_RABIES,
		                DiseaseEnum::TAPEWORM,
		                DisorderEnum::AGORAPHOBIA,
		                DisorderEnum::CHRONIC_MIGRAINE,
		                DisorderEnum::CRABISM,
		                DisorderEnum::PSYCHOTIC_EPISODES,
		                DisorderEnum::VERTIGO])
		         ->setEffectsNumber([0,1,2,3,4])
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
