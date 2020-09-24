import {Model, DataTypes} from 'sequelize';
import {database} from '../config/database';

export class Room extends Model {
    private id!: number;

    get getId(): number {
        return this.id;
    }
}

Room.init(
    {
        id: {
            type: DataTypes.INTEGER,
            primaryKey: true,
            autoIncrement: true,
        },
    },
    {
        tableName: 'room',
        timestamps: true,
        sequelize: database, // this bit is important
    }
);

Room.sync().then(() => console.log('Room table created'));
