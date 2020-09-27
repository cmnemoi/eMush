import {Room} from './room.model';
import {Daedalus} from './daedalus.model';

import {Entity, PrimaryGeneratedColumn, Column, ManyToOne, CreateDateColumn, UpdateDateColumn} from "typeorm";

@Entity()
export class Player {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @Column()
    public user!: string;
    @Column()
    public character!: string;
    @ManyToOne(type => Daedalus, daedalus => daedalus.players)
    public daedalus!: Daedalus;
    @ManyToOne(type => Room, room => room.players)
    public room!: Room;
    @Column("simple-array")
    public skills!: string[];
    @Column("simple-array")
    public items!: string[];
    @Column("simple-array")
    public statuses!: string[];
    @Column()
    public healthPoint!: number;
    @Column()
    public moralPoint!: number;
    @Column()
    public actionPoint!: number;
    @Column()
    public movementPoint!: number;
    @Column()
    public satiety!: number;
    @Column()
    public isMush!: boolean;
    @Column()
    public isDirty!: boolean;
    @CreateDateColumn()
    public createdAt!: Date;
    @UpdateDateColumn()
    public updatedAt!: Date;
}
