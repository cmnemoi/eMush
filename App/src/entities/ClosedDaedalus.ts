import { ClosedPlayer, ClosedPlayerData } from "@/entities/ClosedPlayer";
import { DaedalusProject } from "./Daedalus";
import { EndCauseEnum } from "@/enums/endcause.enum";
import { ClosedExploration } from "@/entities/ClosedExploration";

type DaedalusStatistics = {
    title: string;
    lines: {name: string, value: number}[];
}

type ClosedDaedalusProjects = {
    neronProjects: {
        title: string;
        lines: DaedalusProject[];
    };
    researchProjects: {
        title: string;
        lines: DaedalusProject[];
    };
    pilgredProjects: {
        title: string;
        lines: DaedalusProject[];
    };
}

type TitleHolder = {
    title: string;
    characterKeys: string[];
}

type FunFact = {
    title: string;
    description: string;
    characterKey: string;
}

type ClosedDaedalusData = {
    "@id"?: string;
    id?: number;
    endCause?: EndCauseEnum;
    endDay?: integer;
    endCycle?: integer;
    statistics?: DaedalusStatistics;
    projects?: ClosedDaedalusProjects;
    players?: ClosedPlayerData[];
    titleHolders?: TitleHolder[];
    funFacts?: FunFact[];
    humanTriumphSum?: integer;
    mushTriumphSum?: integer;
    isCheater?: boolean;
};

export class ClosedDaedalus {
    public iri: string|null;
    public id: number|null;
    public endCause: EndCauseEnum|null;
    public endDay: integer|null;
    public endCycle: integer|null;
    public players: ClosedPlayer[]|null;
    public statistics!: DaedalusStatistics;
    public projects!: ClosedDaedalusProjects;
    public explorations: ClosedExploration[];
    public titleHolders!: TitleHolder[];
    public funFacts!: FunFact[];
    public humanTriumphSum: integer|null;
    public mushTriumphSum: integer|null;
    public isCheater!: boolean;

    constructor() {
        this.iri = null;
        this.id = null;
        this.endCause = null;
        this.endDay = null;
        this.endCycle = null;
        this.players = [];
        this.explorations = [];
        this.titleHolders = [];
        this.funFacts = [];
        this.humanTriumphSum = null;
        this.mushTriumphSum = null;
    }
    load(object :ClosedDaedalusData): ClosedDaedalus {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.endCause = object.endCause;
            this.endDay = object.endDay;
            this.endCycle = object.endCycle;
            this.statistics = object.statistics;
            this.projects = object.projects;
            this.explorations = object.closedExplorations
                .map((e: object) => (new ClosedExploration()).load(e))
                .sort((a: ClosedExploration, b: ClosedExploration) => a.createdAt > b.createdAt ? 1 : -1);
            if (typeof object.players !== 'undefined') {
                this.players = object.players.map((p: object) => (new ClosedPlayer()).load(p));
            }
            this.titleHolders = object.titleHolders;
            this.funFacts = object.funFacts;
            this.humanTriumphSum = object.humanTriumphSum;
            this.mushTriumphSum = object.mushTriumphSum;
            this.isCheater = object.isCheater;
        }
        return this;
    }
    jsonEncode(): object {
        const players : string[] = [];
        this.players?.forEach(player => (typeof player.iri === 'string' ? players.push(player.iri) : null));
        const data = {
            'id': this.id,
            'endCause': this.endCause,
            'endDay': this.endDay,
            'endCycle': this.endCycle,
            'players': players,
            'statistics': this.statistics,
            'projects': this.projects,
            'closedExplorations': this.explorations,
            'titleHolders': this.titleHolders,
            'funFacts': this.funFacts,
            'isCheater': this.isCheater,
            'humanTriumphSum': this.humanTriumphSum,
            'mushTriumphSum': this.mushTriumphSum
        };

        return data;
    }
    decode(jsonString : string): ClosedDaedalus {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }


}
