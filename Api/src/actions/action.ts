import {ActionResult} from '../enums/actionResult.enum';
import {ActionInterface} from "./action.interface";

export abstract class Action implements ActionInterface{
    public abstract async loadParams(params: any): Promise<boolean>;
    public abstract canExecute(): boolean;
    protected abstract async apply(): Promise<string>;
    protected abstract createLog(actionResult: string): void;

    public async execute(): Promise<string> {
        if (!this.canExecute()) {
            return ActionResult.NONE;
        }

        const result = await this.apply();
        this.createLog(result);

        return result;
    }
}
