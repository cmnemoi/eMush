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
    @Column()
    public userId!: string;
    @Column()
    public username!: string;
    @OneToMany(type => Player, player => player.user)
    public games!: Player[];
    @CreateDateColumn()
    public createdAt!: Date;
    @UpdateDateColumn()
    public updatedAt!: Date;
}
