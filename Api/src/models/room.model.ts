import {
    Column,
    CreateDateColumn,
    Entity,
    ManyToOne,
    OneToMany,
    PrimaryGeneratedColumn,
    UpdateDateColumn,
} from 'typeorm';
import {Daedalus} from './daedalus.model';
import {Player} from './player.model';

@Entity()
export class Room {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @Column()
    public name!: string;
    @ManyToOne(type => Daedalus, daedalus => daedalus.rooms)
    public daedalus!: Daedalus;
    @OneToMany(type => Player, player => player.room)
    public players!: Player[];
    @Column('simple-array')
    public statuses!: string[];
    @CreateDateColumn()
    public createdAt!: Date;
    @UpdateDateColumn()
    public updatedAt!: Date;
}
