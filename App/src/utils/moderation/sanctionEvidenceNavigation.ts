import { ClosedPlayer } from "@/entities/ClosedPlayer";
import { ModerationSanction } from "@/entities/ModerationSanction";
import router from "@/router";
import ApiService from "@/services/api.service";
import urlJoin from "url-join";

export async function getClosedDaedalusId(closedPlayerId: number): Promise<number> {
    const result = await ApiService.get(urlJoin(import.meta.env.VITE_APP_API_URL, 'closed_players', String(closedPlayerId)));
    const closedPlayer = (new ClosedPlayer()).load(result.data);
    return closedPlayer.closedDaedalusId;
}

export async function goToClosedShip(closedPlayerId: number): Promise<void> {
    const closedDaedalusId = await getClosedDaedalusId(closedPlayerId);
    router.push({ name: 'TheEnd', params: { closedDaedalusId } });
}

export function goToPlayerDetail(playerId: number): void {
    router.push({ name: 'ModerationViewPlayerDetail', params: { playerId } });
}

export async function goToReportEvidence(sanction: ModerationSanction): Promise<void> {
    const sanctionEvidence = sanction.sanctionEvidence;
    const evidenceClass = sanctionEvidence.className;

    if (
        evidenceClass === 'message' ||
        evidenceClass === 'roomLog' ||
        evidenceClass === 'commanderMission' ||
        evidenceClass === 'comManagerAnnouncement'
    ) {
        goToPlayerDetail(sanction.user.playerId as number);
    } else if (evidenceClass === 'closedPlayer') {
        await goToClosedShip(sanctionEvidence.id);
    }
}
