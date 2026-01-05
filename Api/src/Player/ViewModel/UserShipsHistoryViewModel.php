<?php

declare(strict_types=1);

namespace Mush\Player\ViewModel;

use Mush\Game\ViewModel\ViewModelInterface;

final readonly class UserShipsHistoryViewModel implements ViewModelInterface
{
    public function __construct(
        public string $characterBody,
        public string $characterName,
        public int $daysSurvived,
        public int $nbExplorations,
        public int $nbNeronProjects,
        public int $nbResearchProjects,
        public int $nbScannedPlanets,
        /** @var string[] $titles */
        public array $titles,
        public int $triumph,
        public string $endCause,
        public int $daedalusId,
        public bool $playerWasMush,
    ) {}

    public static function fromQueryRow(array $row): self
    {
        return new self(
            (string) $row['character_name'],
            (string) $row['character_name'],
            (int) $row['days_survived'],
            (int) $row['nb_explorations'],
            (int) $row['nb_neron_projects'],
            (int) $row['nb_research_projects'],
            (int) $row['nb_scanned_planets'],
            json_decode($row['titles'], true),
            (int) $row['triumph'],
            (string) $row['end_cause'],
            (int) $row['daedalus_id'],
            (bool) $row['was_mush'],
        );
    }
}
