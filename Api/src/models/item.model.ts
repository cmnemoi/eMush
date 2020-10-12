import {
    Column,
    CreateDateColumn,
    Entity, JoinColumn,
    ManyToOne,
    PrimaryGeneratedColumn,
    UpdateDateColumn,
} from 'typeorm';
import {Player} from './player.model';
import {Room} from './room.model';
import {ItemTypeEnum} from '../enums/itemType.enum';
import {ItemsEnum} from '../enums/items.enum';

@Entity()
export class Item {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @Column({name: 'name'})
    public name!: ItemsEnum;
    @Column({name: 'type'})
    public type!: ItemTypeEnum;
    @Column('simple-array', {name: 'statuses'})
    public statuses: string[] = [];
    @ManyToOne(type => Room, room => room.items)
    @JoinColumn({name: 'room_id'})
    public room!: Room | null;
    @ManyToOne(type => Player, player => player.items)
    @JoinColumn({name: 'player_id'})
    public player!: Player | null;
    @Column({name: 'is_dismantable'})
    public isDismantable!: boolean;
    @Column({name: 'is_heavy'})
    public isHeavy!: boolean;
    @ManyToOne(type => Player)
    @JoinColumn({name: 'personal_id'})
    public personal!: Player;
    @Column({name: 'is_stackable'})
    public isStackable!: boolean;
    @Column({name: 'is_hideable'})
    public isHideable!: boolean;
    @Column({name: 'is_movable'})
    public isMovable!: boolean;
    @Column({name: 'is_fire_destroyable'})
    public isFireDestroyable!: boolean;
    @Column({name: 'is_fire_breakable'})
    public isFireBreakable!: boolean;
    @CreateDateColumn({name: 'created_at'})
    public createdAt!: Date;
    @UpdateDateColumn({name: 'updated_at'})
    public updatedAt!: Date;

    isPersonal(): boolean {
        return this.player !== null;
    }
}
