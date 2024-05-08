<?php

declare(strict_types=1);

namespace Mush\Equipment\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class DroneInfo
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $nickName;

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 0])]
    private int $serialNumber;

    #[ORM\OneToOne(inversedBy: 'droneInfo', targetEntity: Drone::class, cascade: ['ALL'])]
    private Drone $drone;

    public function __construct(Drone $drone, int $nickName, int $serialNumber)
    {
        $this->drone = $drone;
        $this->nickName = $nickName;
        $this->serialNumber = $serialNumber;
    }

    public function getNickName(): int
    {
        return $this->nickName;
    }

    public function getSerialNumber(): int
    {
        return $this->serialNumber;
    }
}
