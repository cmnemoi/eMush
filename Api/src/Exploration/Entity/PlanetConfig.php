<?php

declare(strict_types=1);

namespace Mush\Exploration\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mush\Game\Entity\Collection\ProbaCollection;

#[ORM\Entity]
#[ORM\Table(name: 'planet_config')]
class PlanetConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name = '';

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:1:{s:0:"";i:0;}'])]
    private array $maximumSectors = ['' => 0];

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:1:{s:0:"";i:0;}'])]
    private array $sectorsWeight = ['' => 0];

    public static function fromConfigData(array $data): self
    {
        $config = new self();
        $config->setName($data['name']);
        $config->setSectorsWeight($data['sectorsWeight']);
        $config->setMaximumSectors($data['maximumSectors']);

        return $config;
    }

    public function update(array $data): self
    {
        $this->setName($data['name']);
        $this->setSectorsWeight($data['sectorsWeight']);
        $this->setMaximumSectors($data['maximumSectors']);

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMaximumSectors(): ProbaCollection
    {
        return new ProbaCollection($this->maximumSectors);
    }

    public function setMaximumSectors(array|ProbaCollection $maximumSectors): self
    {
        if ($maximumSectors instanceof ProbaCollection) {
            $maximumSectors = $maximumSectors->toArray();
        }

        $this->maximumSectors = $maximumSectors;

        return $this;
    }

    public function getSectorsWeight(): ProbaCollection
    {
        return new ProbaCollection($this->sectorsWeight);
    }

    public function setSectorsWeight(array|ProbaCollection $sectorsWeight): self
    {
        if ($sectorsWeight instanceof ProbaCollection) {
            $sectorsWeight = $sectorsWeight->toArray();
        }

        $this->sectorsWeight = $sectorsWeight;

        return $this;
    }
}
