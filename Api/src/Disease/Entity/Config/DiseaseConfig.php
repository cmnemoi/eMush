<?php

namespace Mush\Disease\Entity\Config;

use Doctrine\ORM\Mapping as ORM;
use Mush\Disease\Dto\DiseaseConfigDto;
use Mush\Disease\Enum\MedicalConditionTypeEnum;
use Mush\RoomLog\Entity\LogParameterInterface;
use Mush\RoomLog\Enum\LogParameterKeyEnum;

#[ORM\Entity]
#[ORM\Table(name: 'disease_config')]
class DiseaseConfig implements LogParameterInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', length: 255, nullable: false)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $diseaseName;

    #[ORM\Column(type: 'string', nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => MedicalConditionTypeEnum::DISEASE])]
    private string $type = MedicalConditionTypeEnum::DISEASE;

    #[ORM\Column(type: 'array', nullable: false)]
    private array $modifierConfigs = [];

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $canHealNaturally = true;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $duration = [1, 4];

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 1])]
    private int $healActionResistance = 1;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $mushCanHave = false;

    #[ORM\Column(type: 'array', nullable: false, options: ['default' => 'a:0:{}'])]
    private array $removeLower = [];

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => 'none'])]
    private string $eventWhenAppeared = 'none';

    public static function fromDto(DiseaseConfigDto $dto): self
    {
        $disease = new self();
        $disease->setName($dto->key);
        $disease->setDiseaseName($dto->name);
        $disease->setType($dto->type);
        $disease->setNaturalHeal($dto->canHealNaturally);
        $disease->setDuration($dto->duration);
        $disease->setHealActionResistance($dto->healActionResistance);
        $disease->setMushHave($dto->mushCanHave);
        $disease->setModifierConfigs($dto->modifierConfigs);
        $disease->setRemoveLower($dto->removeLower);
        $disease->setEventWhenAppeared($dto->eventWhenAppeared);

        return $disease;
    }

    public function updateFromDto(DiseaseConfigDto $dto): self
    {
        $this->setName($dto->key);
        $this->setDiseaseName($dto->name);
        $this->setType($dto->type);
        $this->setNaturalHeal($dto->canHealNaturally);
        $this->setDuration($dto->duration);
        $this->setHealActionResistance($dto->healActionResistance);
        $this->setMushHave($dto->mushCanHave);
        $this->setModifierConfigs($dto->modifierConfigs);
        $this->setRemoveLower($dto->removeLower);
        $this->setEventWhenAppeared($dto->eventWhenAppeared);

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiseaseName(): string
    {
        return $this->diseaseName;
    }

    public function setDiseaseName(string $diseaseName): self
    {
        $this->diseaseName = $diseaseName;

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
        $this->name = $this->diseaseName . '_' . $configName;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getModifierConfigs(): array
    {
        return $this->modifierConfigs;
    }

    public function setModifierConfigs(array $modifierConfigs): self
    {
        $this->modifierConfigs = $modifierConfigs;

        return $this;
    }

    public function canNaturalHeal(): bool
    {
        return $this->canHealNaturally;
    }

    public function setNaturalHeal(bool $canHealNaturally): self
    {
        $this->canHealNaturally = $canHealNaturally;

        return $this;
    }

    public function getDuration(): array
    {
        return $this->duration;
    }

    public function setDuration(array $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getHealActionResistance(): int
    {
        return $this->healActionResistance;
    }

    public function setHealActionResistance(int $healActionResistance): self
    {
        $this->healActionResistance = $healActionResistance;

        return $this;
    }

    public function canMushHave(): bool
    {
        return $this->mushCanHave;
    }

    public function setMushHave(bool $mushCanHave): self
    {
        if (self::isNotAnInjury()) {
            $this->mushCanHave = $mushCanHave;
        } else {
            $this->mushCanHave = true;
        }

        return $this;
    }

    public function getEventWhenAppeared(): string
    {
        return $this->eventWhenAppeared;
    }

    public function setEventWhenAppeared(string $eventWhenAppeared): self
    {
        $this->eventWhenAppeared = $eventWhenAppeared;

        return $this;
    }

    public function getClassName(): string
    {
        return self::class;
    }

    public function getLogName(): string
    {
        return $this->getDiseaseName();
    }

    public function getLogKey(): string
    {
        return LogParameterKeyEnum::DISEASE;
    }

    public function getRemoveLower(): array
    {
        return $this->removeLower;
    }

    public function setRemoveLower(array $removeLower): self
    {
        $this->removeLower = $removeLower;

        return $this;
    }

    public function isPhysicalDisease(): bool
    {
        return $this->type === MedicalConditionTypeEnum::DISEASE;
    }

    public function isNotAnInjury(): bool
    {
        return $this->type !== MedicalConditionTypeEnum::INJURY;
    }
}
