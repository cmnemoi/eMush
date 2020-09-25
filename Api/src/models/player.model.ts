import {Model, DataTypes} from 'sequelize';
import {database} from '../config/database';
import {Room} from './room.model';
import {Daedalus} from './daedalus.model';

export class Player extends Model {
    readonly _id!: number;
    private _user!: string;
    private _character!: string;
    private _daedalus!: Daedalus;
    private _room!: Room;
    private _skills!: string;
    private _items!: string;
    private _statuses!: string;
    private _healthPoint!: number;
    private _moralPoint!: number;
    private _actionPoint!: number;
    private _movementPoint!: number;
    private _satiety!: number;
    private _isMush!: boolean;
    private _isDirty!: boolean;

    get user(): string {
        return this._user;
    }

    set user(value: string) {
        this._user = value;
    }

    get character(): string {
        return this._character;
    }

    set character(value: string) {
        this._character = value;
    }

    get daedalus(): Daedalus {
        return this._daedalus;
    }

    set daedalus(value: Daedalus) {
        this.setDataValue('daedalus', value);
        this._daedalus = value;
    }

    get room(): Room {
        return this._room;
    }

    set room(value: Room) {
        this.setDataValue('room', value);
        this._room = value;
    }

    get skills(): string[] {
        return JSON.parse(this._skills);
    }

    set skills(value: string[]) {
        this._skills = JSON.stringify(value);
    }

    get items(): string[] {
        return JSON.parse(this._items);
    }

    set items(value: string[]) {
        this._items = JSON.stringify(value);
    }

    get statuses(): string[] {
        return JSON.parse(this._statuses);
    }

    set statuses(value: string[]) {
        this._statuses = JSON.stringify(value);
    }

    get healthPoint(): number {
        return this._healthPoint;
    }

    set healthPoint(value: number) {
        this._healthPoint = value;
    }

    get moralPoint(): number {
        return this._moralPoint;
    }

    set moralPoint(value: number) {
        this._moralPoint = value;
    }

    get actionPoint(): number {
        return this._actionPoint;
    }

    set actionPoint(value: number) {
        this._actionPoint = value;
    }

    get movementPoint(): number {
        return this._movementPoint;
    }

    set movementPoint(value: number) {
        this._movementPoint = value;
    }

    get satiety(): number {
        return this._satiety;
    }

    set satiety(value: number) {
        this._satiety = value;
    }

    get isMush(): boolean {
        return this._isMush;
    }

    set isMush(value: boolean) {
        this._isMush = value;
    }

    get isDirty(): boolean {
        return this._isDirty;
    }

    set isDirty(value: boolean) {
        this._isDirty = value;
    }
}

Player.init(
    {
        id: {
            type: DataTypes.INTEGER,
            primaryKey: true,
            autoIncrement: true,
        },
        user: {
            type: DataTypes.INTEGER,
        },
        character: {
            type: DataTypes.STRING,
        },
        skills: {
            type: DataTypes.JSON,
            allowNull: true,
        },
        items: {
            type: DataTypes.JSON,
            allowNull: true,
        },
        statuses: {
            type: DataTypes.JSON,
            allowNull: true,
        },
        healthPoint: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        moralPoint: {
            type: DataTypes.INTEGER,
        },
        actionPoint: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        movementPoint: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        satiety: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        isMush: {
            type: DataTypes.BOOLEAN,
            allowNull: false,
        },
        isDirty: {
            type: DataTypes.BOOLEAN,
            allowNull: false,
        },
    },
    {
        tableName: 'player',
        timestamps: true,
        sequelize: database, // this bit is important
    }
);
