<?php

namespace Mush\Tests\unit\Action\Validator;

use Mockery;
use Mush\Action\Actions\AbstractAction;
use Mush\Action\Validator\IsReported;
use Mush\Action\Validator\IsReportedValidator;
use Mush\Alert\Entity\Alert;
use Mush\Alert\Entity\AlertElement;
use Mush\Alert\Enum\AlertEnum;
use Mush\Alert\Service\AlertServiceInterface;
use Mush\Daedalus\Entity\Daedalus;
use Mush\Equipment\Entity\GameEquipment;
use Mush\Place\Entity\Place;
use Mush\Player\Entity\Config\CharacterConfig;
use Mush\Player\Entity\Player;
use Mush\Player\Entity\PlayerInfo;
use Mush\Status\Entity\Config\StatusConfig;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
use Mush\Status\Enum\StatusEnum;
use Mush\User\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;

class IsReportedValidatorTest extends TestCase
{
    private IsReportedValidator $validator;
    private IsReported $constraint;

    /** @var AlertServiceInterface|Mockery\Mock */
    private AlertServiceInterface $alertService;

    /**
     * @before
     */
    public function before()
    {
        $this->alertService = \Mockery::mock(AlertServiceInterface::class);

        $this->validator = new IsReportedValidator($this->alertService);
        $this->constraint = new IsReported();
    }

    /**
     * @after
     */
    public function after()
    {
        \Mockery::close();
    }

    public function testValidFire()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room)
        ;
        $room->setDaedalus($daedalus);

        $fireConfig = new StatusConfig();
        $fireConfig->setStatusName(StatusEnum::FIRE);
        $fireStatus = new Status($room, $fireConfig);

        $alertElement = new AlertElement();
        $alertElement->setPlace($room);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::FIRES)->addAlertElement($alertElement);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getSupport' => null,
            ])
        ;

        $this->alertService
            ->shouldReceive('isFireReported')
            ->with($room)
            ->andReturn(false)
            ->once()
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValidFire()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room)
        ;
        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            new CharacterConfig()
        );
        $room->setDaedalus($daedalus);

        $fireConfig = new StatusConfig();
        $fireConfig->setStatusName(StatusEnum::FIRE);
        $fireStatus = new Status($room, $fireConfig);

        $alertElement = new AlertElement();
        $alertElement->setPlace($room)->setPlayerInfo($playerInfo);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::FIRES)->addAlertElement($alertElement);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getSupport' => null,
            ])
        ;

        $this->alertService
            ->shouldReceive('isFireReported')
            ->with($room)
            ->andReturn(true)
            ->once()
        ;

        $this->constraint->message = 'not valid';

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    public function testValidEquipment()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room)
        ;
        $room->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment($room);
        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $status = new Status($gameEquipment, $brokenConfig);

        $alertElement = new AlertElement();
        $alertElement->setEquipment($gameEquipment);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::BROKEN_EQUIPMENTS)->addAlertElement($alertElement);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getSupport' => $gameEquipment,
            ])
        ;

        $this->alertService
            ->shouldReceive('isEquipmentReported')
            ->with($gameEquipment)
            ->andReturn(false)
            ->once()
        ;

        $this->initValidator();
        $this->validator->validate($action, $this->constraint);
    }

    public function testNotValidEquipment()
    {
        $daedalus = new Daedalus();
        $room = new Place();
        $player = new Player();

        $player
            ->setDaedalus($daedalus)
            ->setPlace($room)
        ;
        $playerInfo = new PlayerInfo(
            $player,
            new User(),
            new CharacterConfig()
        );
        $room->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment($room);
        $brokenConfig = new StatusConfig();
        $brokenConfig->setStatusName(EquipmentStatusEnum::BROKEN);
        $status = new Status($gameEquipment, $brokenConfig);

        $alertElement = new AlertElement();
        $alertElement->setEquipment($gameEquipment)->setPlace($room)->setPlayerInfo($playerInfo);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::BROKEN_EQUIPMENTS)->addAlertElement($alertElement);

        $action = \Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getSupport' => $gameEquipment,
            ])
        ;

        $this->alertService
            ->shouldReceive('isEquipmentReported')
            ->with($gameEquipment)
            ->andReturn(true)
            ->once()
        ;

        $this->constraint->message = 'not valid';

        $this->initValidator($this->constraint->message);
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
