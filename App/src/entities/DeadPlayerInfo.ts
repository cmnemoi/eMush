import { Character } from "@/entities/Character";

export interface DeadPlayerInfo {
    id: number,
    character: Character,
    endCauseKey: string,
    endCauseValue: string,
    endCauseDescription: string,
    players: Array<DeadPlayerInfo>
}
