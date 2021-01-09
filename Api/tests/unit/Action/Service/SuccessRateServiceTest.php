<?php

namespace unit\Action\Service;

use Mush\Action\Service\SuccessRateService;
use PHPUnit\Framework\TestCase;

class SuccessRateServiceTest extends TestCase
{
    public function testSuccessRate()
    {
        $service = new SuccessRateService();
        $keyModificator = 1.5;
        $technician = 2;
        $expert = 0.2;

        //Base 6
        $this->assertEquals(6, $service->getSuccessRate(6, 0, 1));
        $this->assertEquals(11, $service->getSuccessRate(6, 3, 1));
        $this->assertEquals(18, $service->getSuccessRate(6, 5, 1));
        //Base 25
        $this->assertEquals(25, $service->getSuccessRate(25, 0, 1));
        $this->assertEquals(48, $service->getSuccessRate(25, 3, 1));
        $this->assertEquals(76, $service->getSuccessRate(25, 5, 1));

        //Modificator like adjustable wrench
        //Base 6
        $this->assertEquals(9, $service->getSuccessRate(6, 0, $keyModificator));
        $this->assertEquals(17, $service->getSuccessRate(6, 3, $keyModificator));
        $this->assertEquals(27, $service->getSuccessRate(6, 5, $keyModificator));
        //Base 25
        $this->assertEquals(37, $service->getSuccessRate(25, 0, $keyModificator));
        $this->assertEquals(73, $service->getSuccessRate(25, 3, $keyModificator));
        $this->assertEquals(99, $service->getSuccessRate(25, 5, $keyModificator));

        //Modificator with 3 adjustable wrench
        $this->assertEquals(37, $service->getSuccessRate(25, 0, $keyModificator));
        $this->assertEquals(56, $service->getSuccessRate(25, 0, $keyModificator ** 2));
        $this->assertEquals(84, $service->getSuccessRate(25, 0, $keyModificator ** 3));

        //Technician Modificator with 3 adjustable wrench

        $this->assertEquals(30, $service->getSuccessRate(25, 0, 1, $expert));
        $this->assertEquals(55, $service->getSuccessRate(25, 0, $technician, $expert));
        $this->assertEquals(42, $service->getSuccessRate(25, 0, $keyModificator ** 1, $expert));
        $this->assertEquals(61, $service->getSuccessRate(25, 0, $keyModificator ** 2, $expert));
        $this->assertEquals(89, $service->getSuccessRate(25, 0, $keyModificator ** 3, $expert));
    }
}
