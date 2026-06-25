<?php

declare(strict_types=1);

namespace Mush\Disease\Entity\Config;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Dto\DiseaseCauseConfigDto;
use Mush\Game\Entity\Collection\ProbaCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'disease_cause_config')]
#[ApiResource(
    paginationItemsPerPage: 25,
    normalizationContext: ['groups' => ['disease_cause_config_read']],
    denormalizationContext: ['groups' => ['disease_cause_config_write']],
    operations: [
        new GetCollection(
            filters: ['default.search_filter', 'default.order_filter'],
        ),
        new Post(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Get(
            security: 'is_granted("ROLE_ADMIN")',
        ),
        new Put(
            security: 'is_granted("ROLE_ADMIN")',
        ),
    ],
)]
class DiseaseCauseConfig
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    #[Groups(['disease_cause_config_read'])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[Groups(['disease_cause_config_read', 'disease_cause_config_write'])]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Groups(['disease_cause_config_read', 'disease_cause_config_write'])]
    private string $causeName;

    #[ORM\Column(type: 'array')]
    #[Groups(['disease_cause_config_read', 'disease_cause_config_write'])]
    private array $diseases;

    public function __construct()
    {
        $this->diseases = [];
    }

    public static function fromDto(DiseaseCauseConfigDto $dto): self
    {
        $diseaseCauseConfig = new self();
        $diseaseCauseConfig->setName($dto->key);
        $diseaseCauseConfig->setCauseName($dto->name);
        $diseaseCauseConfig->setDiseases($dto->diseases);

        return $diseaseCauseConfig;
    }

    public function updateFromDto(DiseaseCauseConfigDto $dto): self
    {
        $this->setName($dto->key);
        $this->setCauseName($dto->name);
        $this->setDiseases($dto->diseases);

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCauseName(): string
    {
        return $this->causeName;
    }

    public function setCauseName(string $causeName): self
    {
        $this->causeName = $causeName;

        return $this;
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

    public function buildName(string $configName): self
    {
        $this->name = $this->causeName . '_' . $configName;

        return $this;
    }

    public function getDiseases(): ProbaCollection
    {
        return new ProbaCollection($this->diseases);
    }

    public function setDiseases(array $diseases): self
    {
        $this->diseases = $diseases;

        return $this;
    }
}
