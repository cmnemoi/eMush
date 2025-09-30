export type ShipHistory = {
    characterName: string;
    daysSurvived: number;
    nbExplorations: number;
    nbNeronProjects: number;
    nbResearchProjects: number;
    nbScannedPlanets: number;
    titles: string[];
    triumph: `${number} :triumph:` | `${number} :triumph_mush:`;
    endCause: string;
    daedalusId: number;
};

export type User = {
    id: integer;
    userId: string;
    username: string;
}
