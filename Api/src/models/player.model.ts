import {Room} from './room.model';
import {Daedalus} from './daedalus.model';

import {
    Column,
    CreateDateColumn,
    Entity, JoinColumn,
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
import {ItemsEnum} from '../enums/items.enum';
import {User} from './user.model';
import {GameStatusEnum} from '../enums/gameStatus.enum';

@Entity()
export class Player {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @ManyToOne(type => User, user => user.games)
    public user!: User;
    @Column({name: 'game_status'})
    public gameStatus!: GameStatusEnum;
    @Column({name: 'character'})
    public character!: CharactersEnum;
    @ManyToOne(type => Daedalus, daedalus => daedalus.players)
    @JoinColumn({name: 'daedalus_id'})
    public daedalus!: Daedalus;
    @ManyToOne(type => Room, room => room.players)
    @JoinColumn({name: 'room_id'})
    public room!: Room;
    @Column('simple-array', {name: 'skills'})
    public skills!: string[];
    @OneToMany(type => Item, item => item.player)
    public items!: Item[];
    @Column('simple-array', {name: 'statuses'})
    public statuses!: StatusEnum[];
    @Column({name: 'health_point'})
    public healthPoint!: number;
    @Column({name: 'moral_point'})
    public moralPoint!: number;
    @Column({name: 'action_point'})
    public actionPoint!: number;
    @Column({name: 'movement_point'})
    public movementPoint!: number;
    @Column({name: 'satiety'})
    public satiety!: number;
    @Column({name: 'mush'})
    public mush!: boolean;
    @CreateDateColumn({name: 'created_at'})
    public createdAt!: Date;
    @UpdateDateColumn({name: 'updated_at'})
    public updatedAt!: Date;

    public isStarving(): boolean {
        return this.statuses.includes(StatusEnum.STARVING);
    }

    // @TODO: proper definition of is mush, a status or a field?
    public isMush(): boolean {
        return this.mush;
    }

    // @TODO: proper definition of dead, with a kind of status, or a new field?
    public isDead(): boolean {
        return this.healthPoint === 0;
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
        return this.items.some(
            (arrayItem: Item) => itemName === arrayItem.name
        );
    }
}
