import {
    Column,
    CreateDateColumn,
    Entity,
    JoinTable,
    ManyToMany,
    ManyToOne,
    OneToMany,
    PrimaryGeneratedColumn,
    UpdateDateColumn,
} from 'typeorm';
import {Daedalus} from './daedalus.model';
import {Player} from './player.model';
import {Room} from './room.model';
import {StatusEnum} from '../enums/status.enum';
import {StateEnum} from '../enums/state.enum';

@Entity()
export class Door {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @Column()
    public name!: string;
    @Column('simple-array')
    public statuses!: string[];
    @ManyToMany(type => Room, room => room.doors)
    public rooms!: Room[];

    public isBroken(): boolean {
        return this.statuses.includes(StateEnum.BROKEN);
    }
}
