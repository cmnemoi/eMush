<?php

namespace Mush\Tests\unit\Action\Validator;

use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\HasDiseases;
use Mush\Action\Validator\HasDiseasesValidator;
use Mush\Disease\Entity\Config\DiseaseConfig;
use Mush\Disease\Entity\PlayerDisease;
use Mush\Disease\Enum\TypeEnum;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Player;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class HasDiseasesValidatorTest extends TestCase
{
    private HasDiseasesValidator $validator;
    private HasDiseases $constraint;

    /**
     * @before
     */
    public function before()
    {
        $this->validator = new HasDiseasesValidator();
        $this->constraint = new HasDiseases();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testValidDisease()
    {
        $this->constraint->isEmpty = true;
        $this->constraint->target = HasDiseases::PLAYER;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testInvalidDisease()
    {
        $this->constraint->isEmpty = true;
        $this->constraint->target = HasDiseases::PLAYER;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setType(TypeEnum::DISEASE);
        $playerDisease = new PlayerDisease();
        $playerDisease->setPlayer($player)->setDiseaseConfig($diseaseConfig);

        $player->addMedicalCondition($playerDisease);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->constraint->message = 'not valid';

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    public function testValidNoDisease()
    {
        $this->constraint->isEmpty = false;
        $this->constraint->target = HasDiseases::PLAYER;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setType(TypeEnum::DISEASE);
        $playerDisease = new PlayerDisease();
        $playerDisease->setPlayer($player)->setDiseaseConfig($diseaseConfig);
        $player->addMedicalCondition($playerDisease);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testInvalidNoDisease()
    {
        $this->constraint->isEmpty = false;
        $this->constraint->target = HasDiseases::PLAYER;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->constraint->message = 'not valid';

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    public function testValidWithType()
    {
        $this->constraint->isEmpty = true;
        $this->constraint->target = HasDiseases::PLAYER;
        $this->constraint->type = TypeEnum::INJURY;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setType(TypeEnum::DISEASE);
        $playerDisease = new PlayerDisease();
        $playerDisease->setPlayer($player)->setDiseaseConfig($diseaseConfig);
        $player->addMedicalCondition($playerDisease);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testInvalidWithType()
    {
        $this->constraint->isEmpty = true;
        $this->constraint->target = HasDiseases::PLAYER;
        $this->constraint->type = TypeEnum::DISEASE;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setType(TypeEnum::DISEASE);
        $playerDisease = new PlayerDisease();
        $playerDisease->setPlayer($player)->setDiseaseConfig($diseaseConfig);
        $player->addMedicalCondition($playerDisease);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
            ])
        ;

        $this->constraint->message = 'not valid';

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    public function testValidWithTarget()
    {
        $this->constraint->isEmpty = true;

        $room = new Place();

        $player = new Player();
        $player->setPlace($room);

        $targetPlayer = new Player();
        $targetPlayer->setPlace($room);

        $diseaseConfig = new DiseaseConfig();
        $diseaseConfig->setType(TypeEnum::DISEASE);
        $playerDisease = new PlayerDisease();
        $playerDisease->setPlayer($player)->setDiseaseConfig($diseaseConfig);

        $player->addMedicalCondition($playerDisease);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getTarget' => $targetPlayer,
            ])
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    protected function initValidator(string $expectedMessage = null)
    {
        $builder = \Mockery::mock(ConstraintViolationBuilder::class);
        $context = \Mockery::mock(ExecutionContext::class);

        if ($expectedMessage) {
            $builder->shouldReceive('addViolation')->andReturn($builder)->once();
            $context->shouldReceive('buildViolation')->with($expectedMessage)->andReturn($builder)->once();
        } else {
            $context->shouldReceive('buildViolation')->never();
        }

        /* @var ExecutionContext $context */
        $this->validator->initialize($context);

        return $this->validator;
    }
}
