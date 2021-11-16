import { QuantityPoint } from "@/entities/QuantityPoint";

export interface Daedalus {
    id: number|null;
    day: number|null;
    cycle: number|null;
    oxygen: QuantityPoint|null;
    fuel: QuantityPoint|null;
    hull: QuantityPoint|null;
    shield: QuantityPoint|null;
    currentCycle: QuantityPoint|null;
    nextCycle: Date|null;
    cryogenizedPlayers: number;
    humanPlayerAlive: number;
    humanPlayerDead: number;
    mushPlayerAlive: number;
    mushPlayerDead: number;
    crewPlayer: QuantityPoint | null;
}
