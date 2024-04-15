<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\CanHeal;
use Mush\Action\Validator\CanHealValidator;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\DiseaseStatusEnum;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\Equipment\Entity\GameItem;
use Mush\Equipment\Enum\ToolItemEnum;
use Mush\Place\Entity\Place;
use Mush\Place\Enum\RoomEnum;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

/**
 * @internal
 */
final class CanHealValidatorTest extends TestCase
{
    private CanHealValidator $validator;
    private CanHeal $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new CanHealValidator();
        $this->constraint = new CanHeal();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    // should be valid
    public function testInMedlabPlayerLowHealth()
    {
        $medlab = new Place();
        $medlab->setName(RoomEnum::MEDLAB);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player
            ->setPlace($medlab);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setMaxHealthPoint(12)->setInitHealthPoint(8);
        $target = new Player();
        $target
            ->setPlayerVariables($targetPlayerConfig)
            ->setPlace($medlab);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ]);

        // valid
        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    // should be valid
    public function testInMedlabPlayerWithDisease()
    {
        $medlab = new Place();
        $medlab->setName(RoomEnum::MEDLAB);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player
            ->setPlace($medlab);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setMaxHealthPoint(12)->setInitHealthPoint(12);
        $target = new Player();
        $target
            ->setPlayerVariables($targetPlayerConfig)
            ->setPlace($medlab);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setType(MedicalConditionTypeEnum::DISEASE);

        $targetDisease = new PlayerDisease();
        $targetDisease
            ->setPlayer($target)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseaseConfig($diseaseConfig)
            ->setResistancePoint(0);

        $target->addMedicalCondition($targetDisease);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ]);

        // valid
        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    // should be not valid
    public function testInMedlabHealthyPlayer()
    {
        $medlab = new Place();
        $medlab->setName(RoomEnum::MEDLAB);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player
            ->setPlace($medlab);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setMaxHealthPoint(12)->setInitHealthPoint(12);
        $target = new Player();
        $target
            ->setPlayerVariables($targetPlayerConfig)
            ->setPlace($medlab);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ]);

        // not valid
        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    // valid
    public function testMedikitPlayerLowHealth()
    {
        $room = new Place();
        $room->setName(RoomEnum::BRAVO_BAY);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setPlace($room);

        $equipment = new GameItem($player);
        $equipment->setName(ToolItemEnum::MEDIKIT);
        $player->addEquipment($equipment);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setMaxHealthPoint(12)->setInitHealthPoint(8);
        $target = new Player();
        $target
            ->setPlayerVariables($targetPlayerConfig)
            ->setPlace($room);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ]);

        // valid
        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    // should be not valid
    public function testMedikitSelfHeal()
    {
        $constraint = new CanHeal();
        $constraint->target = CanHeal::PLAYER;

        $room = new Place();
        $room->setName(RoomEnum::BRAVO_BAY);

        $characterConfig = new CharacterConfig();
        $characterConfig->setMaxHealthPoint(12)->setInitHealthPoint(12);
        $player = new Player();
        $player->setPlace($room)->setPlayerVariables($characterConfig);

        $equipment = new GameItem($player);
        $equipment->setName(ToolItemEnum::MEDIKIT);
        $player->addEquipment($equipment);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setMaxHealthPoint(12)->setInitHealthPoint(8);
        $target = new Player();
        $target
            ->setPlayerVariables($targetPlayerConfig)
            ->setPlace($room);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ]);

        // not valid
        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $constraint);
    }

    // not valid
    public function testMedikitPlayerWithDisease()
    {
        $room = new Place();
        $room->setName(RoomEnum::BRAVO_BAY);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setPlace($room);

        $equipment = new GameItem($player);
        $equipment->setName(ToolItemEnum::MEDIKIT);
        $player->addEquipment($equipment);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setMaxHealthPoint(12)->setInitHealthPoint(12);
        $target = new Player();
        $target
            ->setPlayerVariables($targetPlayerConfig)
            ->setPlace($room);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setType(MedicalConditionTypeEnum::DISEASE);

        $targetDisease = new PlayerDisease();
        $targetDisease
            ->setPlayer($target)
            ->setStatus(DiseaseStatusEnum::ACTIVE)
            ->setDiseaseConfig($diseaseConfig)
            ->setResistancePoint(0);

        $target->addMedicalCondition($targetDisease);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ]);

        // not valid
        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    // not valid
    public function testMedikitHealthyTarget()
    {
        $room = new Place();
        $room->setName(RoomEnum::BRAVO_BAY);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setPlace($room);

        $equipment = new GameItem($player);
        $equipment->setName(ToolItemEnum::MEDIKIT);
        $player->addEquipment($equipment);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setMaxHealthPoint(12)->setInitHealthPoint(12);
        $target = new Player();
        $target
            ->setPlayerVariables($targetPlayerConfig)
            ->setPlace($room);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ]);

        // not valid
        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValidLackMedicalSupplies()
    {
        $medlab = new Place();
        $medlab->setName(RoomEnum::MEDLAB);

        $laboratory = new Place();
        $laboratory->setName(RoomEnum::LABORATORY);

        $characterConfig = new CharacterConfig();
        $player = new Player();
        $player->setPlace($laboratory);

        $targetPlayerConfig = new CharacterConfig();
        $targetPlayerConfig->setMaxHealthPoint(12)->setInitHealthPoint(8);
        $target = new Player();
        $target
            ->setPlayerVariables($targetPlayerConfig)
            ->setPlace($laboratory);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getTarget' => $target,
                'getPlayer' => $player,
            ]);

        // not valid
        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint, 'visibility');
    }

    protected function initValidator(?string $expectedMessage = null)
    {
        $builder = \Mockery::mock(ConstraintViolationBuilder::class);
        $context = \Mockery::mock(ExecutionContext::class);

        if ($expectedMessage) {
            $builder->shouldReceive('addViolation')->andReturn($builder)->once();
            $context->shouldReceive('buildViolation')->with($expectedMessage)->andReturn($builder)->once();
        } else {
            $context->shouldReceive('buildViolation')->never();
        }

        // @var ExecutionContext $context
        $this->validator->initialize($context);

        return $this->validator;
    }
}
