import ModerationService from "@/services/moderation.service";

export async function archiveReport(sanctionId: number, isAbusive: boolean): Promise<void> {
    const params = new URLSearchParams();
    params.append('isAbusive', String(isAbusive));

    await ModerationService.archiveReport(sanctionId, params);
}

export async function suspendSanction(sanctionId: number): Promise<void> {
    await ModerationService.suspendSanction(sanctionId);
}

export async function removeSanction(sanctionId: number): Promise<void> {
    await ModerationService.removeSanction(sanctionId);
}
