export const moderationReasons= [
    'multi_account',
    'mush_play_against_team',
    'killing_spree',
    'foul_play',
    'suicide',
    'hate_speech',
    'flood',
    'wrong_language',
    'exploit',
    'exploit_incentive',
    'leaking_private_information',
];

export const moderationSanctionTypes= [
    'ban_user',
    'quarantine_player',
    'warning',
    'delete_message',
    'delete_end_message',
    'hide_message',
];

export const sanctionDuration= [
    { key: 'permanent', value: null },
    { key: '1_day', value: 'P1D' },
    { key: '2_day', value: 'P2D' },
    { key: '3_day', value: 'P3D' },
    { key: '1_week', value: 'P7D' },
    { key: '2_week', value: 'P14D' },
    { key: '3_week', value: 'P21D' },
    { key: '1_month', value: 'P28D' },
    { key: '2_month', value: 'P60D' },
    { key: '3_month', value: 'P90D' },
];