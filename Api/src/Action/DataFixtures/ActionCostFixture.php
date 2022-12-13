<?php

namespace Mush\Action\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Mush\Action\Entity\ActionCost;

class ActionCostFixture extends Fixture
{
    public const ACTION_COST_FREE = 'free';
    public const ACTION_COST_ONE_ACTION = 'modifier.one.action';
    public const ACTION_COST_TWO_ACTION = 'modifier.two.action';
    public const ACTION_COST_THREE_ACTION = 'modifier.three.action';
    public const ACTION_COST_FOUR_ACTION = 'modifier.four.action';
    public const ACTION_COST_ONE_MOVEMENT = 'modifier.one.movement';
    public const ACTION_COST_TWO_MOVEMENT = 'modifier.two.movement';

    /**
     * @return void
     */
    public function load(ObjectManager $manager)
    {
        $freeCost = $this->buildActionCost(0)->buildName();
        $manager->persist($freeCost);

        $oneActionPointCost = $this->buildActionCost(1)->buildName();
        $manager->persist($oneActionPointCost);

        $twoActionPointCost = $this->buildActionCost(2)->buildName();
        $manager->persist($twoActionPointCost);

        $threeActionPointCost = $this->buildActionCost(3)->buildName();
        $manager->persist($threeActionPointCost);

        $fourActionPointCost = $this->buildActionCost(4)->buildName();
        $manager->persist($fourActionPointCost);

        $oneMovementPoint = $this->buildActionCost(0, 1)->buildName();
        $manager->persist($oneMovementPoint);

        $twoMovementPoint = $this->buildActionCost(0, 2)->buildName();
        $manager->persist($twoMovementPoint);

        $manager->flush();

        $this->addReference(self::ACTION_COST_FREE, $freeCost);
        $this->addReference(self::ACTION_COST_ONE_ACTION, $oneActionPointCost);
        $this->addReference(self::ACTION_COST_TWO_ACTION, $twoActionPointCost);
        $this->addReference(self::ACTION_COST_THREE_ACTION, $threeActionPointCost);
        $this->addReference(self::ACTION_COST_FOUR_ACTION, $fourActionPointCost);
        $this->addReference(self::ACTION_COST_ONE_MOVEMENT, $oneMovementPoint);
        $this->addReference(self::ACTION_COST_TWO_MOVEMENT, $twoMovementPoint);
    }

    private function buildActionCost(?int $actionPoint, ?int $movementPoint = 0, ?int $moralPoint = 0): ActionCost
    {
        $actionCost = new ActionCost();
        $actionCost
            ->setActionPointCost($actionPoint)
            ->setMovementPointCost($movementPoint)
            ->setMoralPointCost($moralPoint)
        ;

        return $actionCost;
    }
}
