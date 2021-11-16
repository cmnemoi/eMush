import { Daedalus } from "@/entities/Daedalus";
import { Room } from "@/entities/Room";
import { Item } from "@/entities/Item";
import { Status } from "@/entities/Status";
import { Action } from "@/entities/Action";
import { Character } from "@/entities/Character";
import { QuantityPoint } from "@/entities/QuantityPoint";

export interface Player {
    id: number;
    gameStatus: string|null;
    character: Character;
    actionPoint: QuantityPoint|null;
    movementPoint: QuantityPoint|null;
    healthPoint: QuantityPoint|null;
    moralPoint: QuantityPoint|null;
    triumph: number|null;
    daedalus: Daedalus|null;
    items: Array<Item>;
    diseases: Array<Status>;
    statuses: Array<Status>;
    actions: Array<Action>;
    room: Room|null;
}
