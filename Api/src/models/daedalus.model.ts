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
    @Column({name: 'oxygen'})
    public oxygen!: number;
    @Column({name: 'fuel'})
    public fuel!: number;
    @Column({name: 'hull'})
    public hull!: number;
    @Column({name: 'day'})
    public day!: number; // @FIXME is this column useful, day = floor(cycle/(24/GameConfig.cycleLength) + 1);
    @Column({name: 'cycle'})
    public cycle!: number;
    @Column({name: 'shield'})
    public shield!: number; // The Plasma Shield is -2 when inactive, -1 when broken, 0 and up when active
    @CreateDateColumn({name: 'created_at'})
    public createdAt!: Date;
    @UpdateDateColumn({name: 'updated_at'})
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
