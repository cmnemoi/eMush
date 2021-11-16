import { Action } from "@/entities/Action";
import { Status } from "@/entities/Status";

export interface Door {
    id: number,
    key: string,
    name: string,
    actions: Array<Action>,
    direction: string,
    statuses: Array<Status>,
    isBroken: boolean
}
