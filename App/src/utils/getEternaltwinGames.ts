type EternaltwinGame = {
    key: string;
    link: string;
}

export const getEternaltwinGames = (): EternaltwinGame[] => {
    return [
        { key: 'alphabounce', link: 'https://alphabounce.eternaltwin.org/' },
        { key: 'dinocard', link: 'https://dinocard.eternaltwin.org/' },
        { key: 'dinorpg', link: 'https://dinorpg.eternaltwin.org/' },
        { key: 'directquiz', link: 'https://directquiz.org/' },
        { key: 'epopotamo', link: 'https://epopotamo.eternaltwin.org/' },
        { key: 'eternalfest', link: 'https://eternalfest.net/' },
        { key: 'kingdom', link: 'https://kingdom.eternaltwin.org/' },
        { key: 'mybrute', link: 'https://mybrute.eternaltwin.org/' },
        { key: 'myhordes', link: 'https://myhordes.eu/' },
        { key: 'neoparc', link: 'https://neoparc.eternaltwin.org/' }
    ];
};
