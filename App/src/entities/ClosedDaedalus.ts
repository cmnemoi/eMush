import { ClosedPlayer } from "@/entities/ClosedPlayer";
import { DaedalusProject } from "./Daedalus";

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

export class ClosedDaedalus {
    public iri: string|null;
    public id: number|null;
    public endCause: string|null;
    public endDay: integer|null;
    public endCycle: integer|null;
    public players: ClosedPlayer[]|null;
    public statistics!: DaedalusStatistics;
    public projects!: ClosedDaedalusProjects;
    public titleHolders!: TitleHolder[];
    public funFacts!: FunFact[];

    constructor() {
        this.iri = null;
        this.id = null;
        this.endCause = null;
        this.endDay = null;
        this.endCycle = null;
        this.players = [];
        this.titleHolders = [];
        this.funFacts = [];
    }
    load(object :any): ClosedDaedalus {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.endCause = object.endCause;
            this.endDay = object.endDay;
            this.endCycle = object.endCycle;
            this.statistics = object.statistics;
            this.projects = object.projects;
            if (typeof object.players !== 'undefined') {
                const players: ClosedPlayer[] = [];
                object.players.forEach((playerData: any) => {
                    const player = (new ClosedPlayer()).load(playerData);
                    players.push(player);
                });
                this.players = players;
            }
            this.titleHolders = object.titleHolders;
            this.funFacts = object.funFacts;
        }
        return this;
    }
    jsonEncode(): object {
        const players : string[] = [];
        this.players?.forEach(player => (typeof player.iri === 'string' ? players.push(player.iri) : null));
        const data : any = {
            'id': this.id,
            'endCause': this.endCause,
            'endDay': this.endDay,
            'endCycle': this.endCycle,
            'players': players,
            'statistics': this.statistics,
            'projects': this.projects,
            'titleHolders': this.titleHolders,
            'funFacts': this.funFacts
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
