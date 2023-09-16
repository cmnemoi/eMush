import { Hunter } from "./Hunter";
import { SpaceBattlePatrolShip } from "./SpaceBattlePatrolShip";
import { SpaceBattleTurret } from "./SpaceBattleTurret";


export class SpaceBattle {
    public hunters: Array<Hunter>;
    public patrolShips: Array<SpaceBattlePatrolShip>;
    public turrets: Array<SpaceBattleTurret>;

    public constructor() {
        this.hunters = [];
        this.patrolShips = [];
        this.turrets = [];
    }

    public load(object: any): SpaceBattle {
        if (typeof object === "undefined") {
            return this;
        }
        this.hunters = [];
        this.patrolShips = [];
        this.turrets = [];

        if (typeof object.hunters !== "undefined") {
            object.hunters.forEach((hunter: any) => {
                this.hunters.push(new Hunter().load(hunter));
            });
        }

        if (typeof object.patrolShips !== "undefined") {
            object.patrolShips.forEach((patrolShip: any) => {
                this.patrolShips.push(new SpaceBattlePatrolShip().load(patrolShip));
            });
        }

        if (typeof object.turrets !== "undefined") {
            object.turrets.forEach((turret: any) => {
                this.turrets.push(new SpaceBattleTurret().load(turret));
            });
            // sort turrets by their name instead of their id so they are always displayed in the same order
            this.turrets.sort((a, b) => {
                return a.displayOrder - b.displayOrder;
            });
        }
        
        return this;
    }

    public jsonEncode(): string {
        return JSON.stringify(this);
    }

    public decode(jsonString : string): SpaceBattle {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }
}