import {
    Column,
    CreateDateColumn,
    Entity,
    OneToMany,
    PrimaryGeneratedColumn,
    UpdateDateColumn,
} from 'typeorm';
import {Player} from './player.model';
import {Item} from './item.model';

@Entity()
export class User {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @Column({name: 'user_id'})
    public userId!: string;
    @Column({name: 'username'})
    public username!: string;
    @OneToMany(type => Player, player => player.user)
    public games!: Player[];
    @CreateDateColumn({name: 'created_at'})
    public createdAt!: Date;
    @UpdateDateColumn({name: 'updated_at'})
    public updatedAt!: Date;
}
