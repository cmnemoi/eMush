export interface ActionInterface {
    loadParams(params: any): Promise<boolean>;
    canExecute(): boolean;
    execute(): Promise<string>;
}
