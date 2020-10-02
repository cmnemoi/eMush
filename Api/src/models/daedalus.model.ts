import {Player} from './player.model';
import {Room} from './room.model';
import {
    Column,
    CreateDateColumn,
    Entity,
    OneToMany,
    PrimaryGeneratedColumn,
    UpdateDateColumn,
} from 'typeorm';

@Entity()
export class Daedalus {
    @PrimaryGeneratedColumn()
    readonly id!: number;
    @OneToMany(type => Player, player => player.daedalus)
    public players!: Player[];
    @OneToMany(type => Room, room => room.daedalus)
    public rooms!: Room[];
    @Column()
    public oxygen!: number;
    @Column()
    public fuel!: number;
    @Column()
    public hull!: number;
    @Column()
    public day!: number;
    @Column()
    public cycle!: number;
    @Column()
    public shield!: number; // The Plasma Shield is -2 when inactive, -1 when broken, 0 and up when active
    @CreateDateColumn()
    public createdAt!: Date;
    @UpdateDateColumn()
    public updatedAt!: Date;

    getPlayersAlive(): Player[] {
        return this.players.filter((player: Player) => player.healthPoint > 0);
    }

    getRoom(roomName: string): Room | null {
        const room = this.rooms.find(
            (element: Room) => element.name === roomName
        );
        return typeof room === 'undefined' ? null : room;
    }
}
