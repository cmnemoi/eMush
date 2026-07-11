import { toArray } from "@/utils/toArray";
import { Hunter } from "./Hunter";
import { SpaceBattlePatrolShip } from "./SpaceBattlePatrolShip";
import { SpaceBattleTurret } from "./SpaceBattleTurret";

type SpaceBattleData = {
    hunters?: Array<Hunter> | Record<string, Hunter>;
    patrolShips?: Array<SpaceBattlePatrolShip> | Record<string, SpaceBattlePatrolShip>;
    turrets?: Array<SpaceBattleTurret> | Record<string, SpaceBattleTurret>;
};

export class SpaceBattle {
    public hunters: Array<Hunter>;
    public patrolShips: Array<SpaceBattlePatrolShip>;
    public turrets: Array<SpaceBattleTurret>;

    public constructor() {
        this.hunters = [];
        this.patrolShips = [];
        this.turrets = [];
    }

    public load(object: SpaceBattleData): SpaceBattle {
        if (typeof object === "undefined") {
            return this;
        }
        this.hunters = [];
        this.patrolShips = [];
        this.turrets = [];

        if (typeof object.hunters !== "undefined") {
            toArray(object.hunters).forEach((hunter: Hunter) => {
                this.hunters.push(new Hunter().load(hunter));
            });
        }

        if (typeof object.patrolShips !== "undefined") {
            toArray(object.patrolShips).forEach((patrolShip: SpaceBattlePatrolShip) => {
                this.patrolShips.push(new SpaceBattlePatrolShip().load(patrolShip));
            });
        }

        if (typeof object.turrets !== "undefined") {
            toArray(object.turrets).forEach((turret: SpaceBattleTurret) => {
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
