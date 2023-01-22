import { ClosedPlayer } from "@/entities/ClosedPlayer";

export class ClosedDaedalus {
    public iri: string|null;
    public id: number|null;
    public endCause: string|null;
    public endDay: integer|null;
    public endCycle: integer|null;
    public players: ClosedPlayer[]|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.endCause = null;
        this.endDay = null;
        this.endCycle = null;
        this.players = [];
    }
    load(object :any): ClosedDaedalus {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.endCause = object.endCause;
            this.endDay = object.endDay;
            this.endCycle = object.endCycle;
            if (typeof object.players !== 'undefined') {
                const players: ClosedPlayer[] = [];
                object.players.forEach((playerData: any) => {
                    const player = (new ClosedPlayer()).load(playerData);
                    players.push(player);
                });
                this.players = players;
            }
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