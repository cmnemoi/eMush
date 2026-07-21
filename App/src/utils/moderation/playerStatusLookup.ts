import { ModerationViewPlayer } from "@/entities/ModerationViewPlayer";
import ModerationService from "@/services/moderation.service";

export function findPlayerInfo(playerInfo: ModerationViewPlayer[], playerId: number | null): ModerationViewPlayer | undefined {
    if (playerId === null || playerId === undefined) {
        return undefined;
    }
    return playerInfo.find(player => player.id === playerId);
}

export function getPlayerStatus(playerInfo: ModerationViewPlayer[], playerId: number | null): boolean {
    return findPlayerInfo(playerInfo, playerId)?.isAlive ?? true;
}

export function getPlayerMush(playerInfo: ModerationViewPlayer[], playerId: number | null): boolean {
    return findPlayerInfo(playerInfo, playerId)?.isMush ?? false;
}

export function getDaedalusId(playerInfo: ModerationViewPlayer[], playerId: number | null): number | null {
    return findPlayerInfo(playerInfo, playerId)?.daedalusId ?? null;
}

export async function loadPlayerInfo(playerInfo: ModerationViewPlayer[], playerId: number | null): Promise<void> {
    if (playerId === null || playerId === undefined) {
        return;
    }
    if (findPlayerInfo(playerInfo, playerId)) {
        return;
    }
    try {
        const response = await ModerationService.getModerationViewPlayer(playerId);
        playerInfo.push(new ModerationViewPlayer().load(response.data));
    } catch (error) {
        console.error(error);
    }
}
