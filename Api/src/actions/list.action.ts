import {MoveAction} from './move.action';
import {Action} from './action';

const listActionsNameToClass: {[key: string]: string} = {
    move: 'MoveAction',
};

const actions = {
    MoveAction,
};

export function getActionClass(action: string): string | undefined {
    return listActionsNameToClass[action];
}

export function createInstance(actionClass: string, ...args: any[]): Action {
    return new (actions as any)[actionClass](...args);
}
