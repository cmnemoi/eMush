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
import {Door} from './door.model';
import {Item} from './item.model';
import * as _ from 'lodash';

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
    @OneToMany(type => Item, item => item.room)
    public items!: Item[];
    @ManyToMany(type => Door, door => door.rooms)
    @JoinTable()
    public doors!: Door[];
    @Column('simple-array')
    public statuses!: string[];
    @CreateDateColumn()
    public createdAt!: Date;
    @UpdateDateColumn()
    public updatedAt!: Date;

    public addItem(item: Item): Room {
        this.items.push(item);
        item.room = this;

        return this;
    }

    public removeItem(item: Item): Room {
        _.remove(this.items, (arrayItem: Item) => arrayItem.id === item.id);
        item.room = null;

        return this;
    }

    public hasItem(item: Item): boolean {
        return this.items.some((arrayItem: Item) => item.id === arrayItem.id);
    }
}
