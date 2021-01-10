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

    public function load(ObjectManager $manager)
    {
        $freeCost = $this->buildActionCost(0);
        $manager->persist($freeCost);

        $oneActionPointCost = $this->buildActionCost(1);
        $manager->persist($oneActionPointCost);

        $twoActionPointCost = $this->buildActionCost(2);
        $manager->persist($twoActionPointCost);

        $threeActionPointCost = $this->buildActionCost(3);
        $manager->persist($threeActionPointCost);

        $fourActionPointCost = $this->buildActionCost(4);
        $manager->persist($fourActionPointCost);

        $oneMovementPoint = $this->buildActionCost(0, 1);
        $manager->persist($oneMovementPoint);

        $manager->flush();

        $this->addReference(self::ACTION_COST_FREE, $freeCost);
        $this->addReference(self::ACTION_COST_ONE_ACTION, $oneActionPointCost);
        $this->addReference(self::ACTION_COST_TWO_ACTION, $twoActionPointCost);
        $this->addReference(self::ACTION_COST_THREE_ACTION, $threeActionPointCost);
        $this->addReference(self::ACTION_COST_FOUR_ACTION, $fourActionPointCost);
        $this->addReference(self::ACTION_COST_ONE_MOVEMENT, $oneMovementPoint);
    }

    private function buildActionCost(int $actionPoint, int $movementPoint = 0, int $moralPoint = 0): ActionCost
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
