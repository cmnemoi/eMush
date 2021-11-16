export interface Action {
    id : number,
    key: string,
    canExecute: boolean
    name: string,
    description: string,
    actionPointCost: number,
    movementPointCost: number,
    successRate: number
}

