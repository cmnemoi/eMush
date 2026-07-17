export function formatModerationDate(date: string | Date, locale: string, t: (key: string) => string): string {
    const currentDate = new Date();
    const reportDate = new Date(date);

    if (currentDate.toDateString() === reportDate.toDateString()) {
        return `${t('moderation.sanctionDetail.today')} ${t('moderation.sanctionDetail.to')} ${reportDate.toLocaleTimeString(locale, { hour: "numeric", minute: "numeric" })}`;
    }

    if (new Date(currentDate.setDate(currentDate.getDate() - 1)).toDateString() === reportDate.toDateString()) {
        return `${t('moderation.sanctionDetail.yesterday')} ${t('moderation.sanctionDetail.to')} ${reportDate.toLocaleTimeString(locale, { hour: "numeric", minute: "numeric" })}`;
    }

    return reportDate.toLocaleDateString(locale, { month: "long", day: "numeric", hour: "numeric", minute: "numeric" });
}
