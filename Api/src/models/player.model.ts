import {Room} from './room.model';
import {Daedalus} from './daedalus.model';

import {
    Column,
    CreateDateColumn,
    Entity,
    ManyToOne,
    OneToMany,
    PrimaryGeneratedColumn,
    UpdateDateColumn,
} from 'typeorm';
import {Item} from './item.model';
import {StatusEnum} from '../enums/status.enum';
import {CharactersEnum} from '../enums/characters.enum';
import {SkillsEnum} from '../enums/skills.enum';
import * as _ from 'lodash';
import {ItemsEnum} from "../enums/items.enum";

@Entity()
export class Player {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @Column()
    public user!: string;
    @Column()
    public character!: CharactersEnum;
    @ManyToOne(type => Daedalus, daedalus => daedalus.players)
    public daedalus!: Daedalus;
    @ManyToOne(type => Room, room => room.players)
    public room!: Room;
    @Column('simple-array')
    public skills!: string[];
    @OneToMany(type => Item, item => item.player)
    public items!: Item[];
    @Column('simple-array')
    public statuses!: StatusEnum[];
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

    public setDirty(): void {
        this.isDirty = true;
    }
    public isStarving(): boolean {
        return this.statuses.includes(StatusEnum.STARVING);
    }
    public canTakeHeavyItems(): boolean {
        return this.skills.includes(SkillsEnum.SOLID);
    }
    public addStatus(status: StatusEnum): Player {
        this.statuses.push(status);

        return this;
    }

    public removeStatus(status: StatusEnum): Player {
        _.remove(
            this.statuses,
            (arrayStatus: StatusEnum) => arrayStatus === status
        );

        return this;
    }

    public hasStatus(status: StatusEnum): boolean {
        return this.statuses.includes(status);
    }

    public addSkill(skill: SkillsEnum): Player {
        this.skills.push(skill);

        return this;
    }

    public hasSkill(skill: SkillsEnum): boolean {
        return this.skills.includes(skill);
    }

    public addItem(item: Item): Player {
        this.items.push(item);
        item.player = this;

        return this;
    }

    public removeItem(item: Item): Player {
        _.remove(this.items, (arrayItem: Item) => arrayItem.id === item.id);
        item.player = null;

        return this;
    }

    public hasItem(item: Item): boolean {
        return this.items.some((arrayItem: Item) => item.id === arrayItem.id);
    }

    public hasItemName(itemName: ItemsEnum): boolean {
        return this.items.some((arrayItem: Item) => itemName === arrayItem.name);
    }
}
