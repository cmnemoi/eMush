import {MoveAction} from './move.action';
import {Action} from './action';
import {ActionsEnum} from '../enums/actions.enum';
import {EatAction} from './eat.action';
import {TakeAction} from './take.action';
import {DropAction} from './drop.action';

interface ActionClass {
    name: ActionsEnum;
    class: string;
}

const listActionsNameToClass: ActionClass[] = [
    {
        name: ActionsEnum.MOVE,
        class: 'MoveAction',
    },
    {
        name: ActionsEnum.EAT,
        class: 'EatAction',
    },
    {
        name: ActionsEnum.TAKE,
        class: 'TakeAction',
    },
    {
        name: ActionsEnum.DROP,
        class: 'DropAction',
    },
];
const actions = {
    MoveAction,
    EatAction,
    TakeAction,
    DropAction,
};

export function getActionClass(action: ActionsEnum): string | null {
    const actionObject = listActionsNameToClass.find(
        value => value.name === action
    );

    return typeof actionObject === 'undefined' ? null : actionObject.class;
}

/* eslint-disable  @typescript-eslint/no-explicit-any */
export function createInstance(actionClass: string, ...args: any[]): Action {
    return new (actions as any)[actionClass](...args);
}
