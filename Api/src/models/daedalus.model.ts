import {Model, DataTypes} from 'sequelize';
import {database} from '../config/database';

export class Daedalus extends Model {
    private id!: number;

    get getId(): number {
        return this.id;
    }
}

Daedalus.init(
    {
        id: {
            type: DataTypes.INTEGER,
            primaryKey: true,
            autoIncrement: true,
        },
    },
    {
        tableName: 'daedalus',
        timestamps: true,
        sequelize: database, // this bit is important
    }
);

Daedalus.sync().then(() => console.log('Daedalus table created'));
