import { Action } from "@/entities/Action";
import { Status } from "@/entities/Status";

export interface Equipment {
    id: number,
    key: string,
    name: string,
    description: string,
    actions: Array<Action>,
    statuses: Array<Status>,
    isBroken: boolean
}
