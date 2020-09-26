import {Model, DataTypes} from 'sequelize';
import {database} from '../config/database';
import {Player} from './player.model';
import {Room} from './room.model';

export class Daedalus extends Model {
    readonly id!: number;
    private _players!: Player[];
    private _rooms!: Room[];
    private _oxygen!: number;
    private _fuel!: number;
    private _hull!: number;
    private _day!: number;
    private _cycle!: number;
    private _shield!: number; // The Plasma Shield is -2 when inactive, -1 when broken, 0 and up when active
    private _updatedAt!: Date;

    get players(): Player[] {
        return this._players;
    }

    set players(value: Player[]) {
        this._players = value;
    }

    getPlayersAlive(): Player[] {
        return this.players.filter((player: Player) => player.healthPoint > 0);
    }

    get rooms(): Room[] {
        return this._rooms;
    }

    set rooms(value: Room[]) {
        this.setDataValue('rooms', value);
        this._rooms = value;
    }

    getRoom(roomName: string) {
        return this.rooms.find((element: Room) => element.name === roomName);
    }

    get oxygen(): number {
        return this._oxygen;
    }

    set oxygen(value: number) {
        this._oxygen = value;
    }

    get fuel(): number {
        return this._fuel;
    }

    set fuel(value: number) {
        this._fuel = value;
    }

    get hull(): number {
        return this._hull;
    }

    set hull(value: number) {
        this._hull = value;
    }

    get day(): number {
        return this._day;
    }

    set day(value: number) {
        this._day = value;
    }

    get cycle(): number {
        return this._cycle;
    }

    set cycle(value: number) {
        this._cycle = value;
    }

    get shield(): number {
        return this._shield;
    }

    set shield(value: number) {
        this._shield = value;
    }

    get updatedAt(): Date {
        return this._updatedAt;
    }

    set updatedAt(value: Date) {
        this._updatedAt = value;
    }
}

Daedalus.init(
    {
        id: {
            type: DataTypes.INTEGER,
            primaryKey: true,
            autoIncrement: true,
        },
        oxygen: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        fuel: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        shield: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        day: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
        cycle: {
            type: DataTypes.INTEGER,
            allowNull: false,
        },
    },
    {
        tableName: 'daedalus',
        timestamps: true,
        sequelize: database, // this bit is important
    }
);
