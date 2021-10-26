<?php

namespace Mush\Test\Action\Validator;

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
use Mush\Player\Entity\Player;
use Mush\Status\Entity\Status;
use Mush\Status\Enum\EquipmentStatusEnum;
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
        $this->alertService = Mockery::mock(AlertServiceInterface::class);

        $this->validator = new IsReportedValidator($this->alertService);
        $this->constraint = new IsReported();
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
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

        $alertElement = new AlertElement();
        $alertElement->setPlace($room);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::FIRES)->addAlertElement($alertElement);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => null,
            ])
        ;

        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::FIRES, $daedalus)
            ->andReturn($alert)
            ->once()
        ;

        $this->alertService
            ->shouldReceive('getAlertFireElement')
            ->with($alert, $room)
            ->andReturn($alertElement)
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
        $room->setDaedalus($daedalus);

        $alertElement = new AlertElement();
        $alertElement->setPlace($room)->setPlayer($player);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::FIRES)->addAlertElement($alertElement);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => null,
            ])
        ;

        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::FIRES, $daedalus)
            ->andReturn($alert)
            ->once()
        ;

        $this->alertService
            ->shouldReceive('getAlertFireElement')
            ->with($alert, $room)
            ->andReturn($alertElement)
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

        $gameEquipment = new GameEquipment();
        $status = new Status($gameEquipment, EquipmentStatusEnum::BROKEN);

        $alertElement = new AlertElement();
        $alertElement->setEquipment($gameEquipment);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::BROKEN_EQUIPMENTS)->addAlertElement($alertElement);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $gameEquipment,
            ])
        ;

        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::BROKEN_EQUIPMENTS, $daedalus)
            ->andReturn($alert)
            ->once()
        ;

        $this->alertService
            ->shouldReceive('getAlertEquipmentElement')
            ->with($alert, $gameEquipment)
            ->andReturn($alertElement)
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
        $room->setDaedalus($daedalus);

        $gameEquipment = new GameEquipment();
        $status = new Status($gameEquipment, EquipmentStatusEnum::BROKEN);

        $alertElement = new AlertElement();
        $alertElement->setEquipment($gameEquipment)->setPlace($room)->setPlayer($player);

        $alert = new Alert();
        $alert->setDaedalus($daedalus)->setName(AlertEnum::BROKEN_EQUIPMENTS)->addAlertElement($alertElement);

        $action = Mockery::mock(AbstractAction::class);
        $action
            ->shouldReceive([
                'getPlayer' => $player,
                'getParameter' => $gameEquipment,
            ])
        ;

        $this->alertService
            ->shouldReceive('findByNameAndDaedalus')
            ->with(AlertEnum::BROKEN_EQUIPMENTS, $daedalus)
            ->andReturn($alert)
            ->once()
        ;

        $this->alertService
            ->shouldReceive('getAlertEquipmentElement')
            ->with($alert, $gameEquipment)
            ->andReturn($alertElement)
            ->once()
        ;

        $this->constraint->message = 'not valid';

        $this->initValidator($this->constraint->message);
        $this->validator->validate($action, $this->constraint);
    }

    protected function initValidator(?string $expectedMessage = null)
    {
        $builder = Mockery::mock(ConstraintViolationBuilder::class);
        $context = Mockery::mock(ExecutionContext::class);

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
