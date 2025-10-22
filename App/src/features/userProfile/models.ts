export type ShipHistory = {
    data: {
        characterName: string;
        daysSurvived: integer;
        nbExplorations: integer;
        nbNeronProjects: integer;
        nbResearchProjects: integer;
        nbScannedPlanets: integer;
        titles: string[];
        triumph: `${integer} :triumph:` | `${integer} :triumph_mush:`;
        endCause: string;
        daedalusId: integer;
    }[];
    totalItems: integer;
};

export type User = {
    id: integer;
    userId: string;
    username: string;
}
