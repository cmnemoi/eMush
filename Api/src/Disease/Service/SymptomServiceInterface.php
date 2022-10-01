<?php

namespace Mush\Disease\Service;

use Mush\Disease\Entity\Config\SymptomConfig;
use Mush\Player\Entity\Player;

interface SymptomServiceInterface
{
    public function handleCycleSymptom(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    // public function handlePostActionSymptom(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    public function handleStatusAppliedSymptom(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    public function handleBreakouts(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    public function handleCatAllergy(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    public function handleDrooling(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    public function handleFearOfCats(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    public function handleFoamingMouth(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    public function handlePsychoticAttacks(SymptomConfig $symptomConfig, Player $player): void;

    public function handleSepticemia(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    public function handleSneezing(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;

    public function handleVomiting(SymptomConfig $symptomConfig, Player $player, \DateTime $time): void;
}
