import {ActionResult} from '../enums/actionResult.enum';

export abstract class Action {
    public abstract async loadParams(params: any): Promise<boolean>;
    protected abstract canExecute(): boolean;
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
