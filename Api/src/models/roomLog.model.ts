import {Model, DataTypes} from 'sequelize';
import {database} from '../config/database';

export class RoomLog extends Model {
    readonly id!: number;
    private _roomId!: number;
    private _log!: string;
    private _createdAt!: Date;

    get roomId(): number {
        return this._roomId;
    }

    set roomId(value: number) {
        this._roomId = value;
    }

    get log(): string {
        return this._log;
    }

    set log(value: string) {
        this._log = value;
    }

    get createdAt(): Date {
        return this._createdAt;
    }

    set createdAt(value: Date) {
        this._createdAt = value;
    }
}

RoomLog.init(
    {
        id: {
            type: DataTypes.INTEGER,
            primaryKey: true,
            autoIncrement: true,
        },
        roomId: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        log: {
            type: DataTypes.STRING,
            allowNull: false,
        },
        createdAt: {
            type: DataTypes.DATE,
            allowNull: false,
        },
    },
    {
        tableName: 'room_log',
        sequelize: database, // this bit is important
    }
);
