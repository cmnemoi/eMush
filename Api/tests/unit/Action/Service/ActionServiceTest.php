<?php

namespace unit\Action\Service;

use Mockery;
use Mush\Action\Service\ActionService;
use Mush\Player\Service\ActionModifierServiceInterface;
use Mush\Status\Service\StatusServiceInterface;
use PHPUnit\Framework\TestCase;

class ActionServiceTest extends TestCase
{
    /**
     * @before
     */
    public function before()
    {
        $this->statusService = Mockery::mock(StatusServiceInterface::class);
        $this->actionModifierService = Mockery::mock(ActionModifierServiceInterface::class);

        $this->service = new ActionService(
            $this->actionModifierService,
            $this->statusService
        );
    }

    /**
     * @after
     */
    public function after()
    {
        Mockery::close();
    }

    public function testSuccessRateFormula()
    {
        $keyModificator = 1.5;
        $technician = 2;
        $expert = 0.2;

        //Base 6
        $this->assertEquals(6, $this->service->computeSuccessRate(6, 0, 1));
        $this->assertEquals(11, $this->service->computeSuccessRate(6, 3, 1));
        $this->assertEquals(18, $this->service->computeSuccessRate(6, 5, 1));
        //Base 25
        $this->assertEquals(25, $this->service->computeSuccessRate(25, 0, 1));
        $this->assertEquals(48, $this->service->computeSuccessRate(25, 3, 1));
        $this->assertEquals(76, $this->service->computeSuccessRate(25, 5, 1));

        //Modificator like adjustable wrench
        //Base 6
        $this->assertEquals(9, $this->service->computeSuccessRate(6, 0, $keyModificator));
        $this->assertEquals(17, $this->service->computeSuccessRate(6, 3, $keyModificator));
        $this->assertEquals(27, $this->service->computeSuccessRate(6, 5, $keyModificator));
        //Base 25
        $this->assertEquals(37, $this->service->computeSuccessRate(25, 0, $keyModificator));
        $this->assertEquals(73, $this->service->computeSuccessRate(25, 3, $keyModificator));
        $this->assertEquals(99, $this->service->computeSuccessRate(25, 5, $keyModificator));

        //Modificator with 3 adjustable wrench
        $this->assertEquals(37, $this->service->computeSuccessRate(25, 0, $keyModificator));
        $this->assertEquals(56, $this->service->computeSuccessRate(25, 0, $keyModificator ** 2));
        $this->assertEquals(84, $this->service->computeSuccessRate(25, 0, $keyModificator ** 3));

        //Technician Modificator with 3 adjustable wrench

        $this->assertEquals(30, $this->service->computeSuccessRate(25, 0, 1, $expert));
        $this->assertEquals(55, $this->service->computeSuccessRate(25, 0, $technician, $expert));
        $this->assertEquals(42, $this->service->computeSuccessRate(25, 0, $keyModificator ** 1, $expert));
        $this->assertEquals(61, $this->service->computeSuccessRate(25, 0, $keyModificator ** 2, $expert));
        $this->assertEquals(89, $this->service->computeSuccessRate(25, 0, $keyModificator ** 3, $expert));
    }
}
