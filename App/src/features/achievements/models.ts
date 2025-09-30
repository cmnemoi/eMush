export type Statistic = {
    key: string;
    name: string;
    description: string;
    count: integer;
    formattedCount: string;
    isRare: boolean;
};

export type Achievement = {
    key: string;
    name: string;
    statisticKey: string;
    statisticName: string;
    statisticDescription: string;
    points: integer;
    formattedPoints: string;
    isRare: boolean;
};
