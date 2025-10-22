export type RankingDaedalus = {
    data: {
        id: number,
        endCause: string,
        daysSurvived: number,
        cyclesSurvived: number,
        humanTriumphSum: string,
        mushTriumphSum: string,
        highestHumanTriumph: string,
        highestMushTriumph: string
    }[],
    totalItems: integer,
};
