type EternaltwinGame = {
    key: string;
    link: string;
    icon: string;
}

export const getEternaltwinGames = (): EternaltwinGame[] => {
    return [
        { key: 'alphabounce', link: 'https://alphabounce.eternaltwin.org/', icon: 'https://alphabounce.eternaltwin.org/favicon.ico' },
        { key: 'dinocard', link: 'https://dinocard.eternaltwin.org/', icon: 'https://dinocard.eternaltwin.org/favicon.ico' },
        { key: 'dinorpg', link: 'https://dinorpg.eternaltwin.org/', icon: 'https://dinorpg.eternaltwin.org/favicon.ico' },
        { key: 'directquiz', link: 'https://directquiz.org/', icon: 'https://directquiz.org/images/favicon.ico' },
        { key: 'epopotamo', link: 'https://epopotamo.eternaltwin.org/', icon: 'https://epopotamo.eternaltwin.org/favicon.ico' },
        { key: 'eternalfest', link: 'https://eternalfest.net/', icon: 'https://eternalfest.net/favicon.ico' },
        { key: 'kingdom', link: 'https://kingdom.eternaltwin.org/', icon: 'https://kingdom.eternaltwin.org/favicon.ico' },
        { key: 'mybrute', link: 'https://mybrute.eternaltwin.org/', icon: 'https://mybrute.eternaltwin.org/favicon.ico' },
        { key: 'myhordes', link: 'https://myhordes.eu/', icon: 'https://myhordes.eu/build/favicon/favicon.ico' },
        { key: 'neoparc', link: 'https://neoparc.eternaltwin.org/', icon: 'https://neoparc.eternaltwin.org/favicon.ico' },
        { key: 'pioupiouz', link: 'https://pioupiouz.eternaltwin.org/', icon: 'https://pioupiouz.eternaltwin.org/gfx/favicon.ico' }
    ];
};
