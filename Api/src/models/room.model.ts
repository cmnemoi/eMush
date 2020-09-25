import {Model, DataTypes} from 'sequelize';
import {database} from '../config/database';

export class Room extends Model {
    readonly id!: number;
    private _name!: string;
    private _statuses!: string;

    get name(): string {
        return this._name;
    }

    set name(value: string) {
        this._name = value;
    }

    get statuses(): string[] {
        return JSON.parse(this._statuses);
    }

    set statuses(value: string[]) {
        this._statuses = JSON.stringify(value);
    }
}

Room.init(
    {
        id: {
            type: DataTypes.INTEGER,
            primaryKey: true,
            autoIncrement: true,
        },
        name: {
            type: DataTypes.STRING,
            allowNull: false,
        },
        statuses: {
            type: DataTypes.JSON,
            allowNull: true,
        },
    },
    {
        tableName: 'room',
        timestamps: true,
        sequelize: database, // this bit is important
    }
);
