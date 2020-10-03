import {MoveAction} from './move.action';
import {Action} from './action';
import {ActionsEnum} from "../enums/actions.enum";
import {EatAction} from "./eat.action";

interface ActionClass{
    name: ActionsEnum,
    class: string
}

const listActionsNameToClass: ActionClass[] = [
    {
        name: ActionsEnum.MOVE,
        class: "MoveAction"
    },
    {
        name: ActionsEnum.EAT,
        class: "EatAction"
    }
];
const actions = {
    MoveAction,
    EatAction,
};

export function getActionClass(action: ActionsEnum): string | null {
    const actionObject = listActionsNameToClass.find(value => value.name === action)

    return (typeof actionObject === "undefined") ? null : actionObject.class;

}

export function createInstance(actionClass: string, ...args: any[]): Action {
    return new (actions as any)[actionClass](...args);
}
