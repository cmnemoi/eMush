export interface Contributor {
    name: string;
    role: string;
    active: boolean;
    coreTeam: boolean;
}

export const team: (Contributor)[] = [
    { name: 'Evian', role: 'admin', active: true, coreTeam: true },
    { name: 'Breut', role: 'admin', active: true, coreTeam: true },
    { name: 'gowoons', role: 'developer', active: true, coreTeam: false },
    { name: 'A7', role: 'developer', active: true, coreTeam: false },
    { name: 'zasfree', role: 'admin', active: true, coreTeam: true },
    { name: 'Simpkin', role: 'developer', active: false, coreTeam: false },
    { name: 'rackhaml', role: 'helper', active: false, coreTeam: false },
    { name: 'RSickenberg', role: 'developer', active: true, coreTeam: false },
    { name: 'Demurgos', role: 'developer', active: false, coreTeam: false },
    { name: 'Biosha', role: 'developer', active: false, coreTeam: false },
    { name: 'Tishwa', role: 'developer', active: false, coreTeam: false },
    { name: 'Sami / EaCOS', role: 'developer', active: false, coreTeam: false },
    { name: 'MoUoA', role: 'helper', active: false, coreTeam: false },
    { name: 'Yasgal', role: 'artist', active: true, coreTeam: false },
    { name: 'Zerah', role: 'developer', active: false, coreTeam: false },
    { name: 'ReNacK', role: 'developer', active: false, coreTeam: false },
    { name: 'amadare', role: 'developer', active: false, coreTeam: false },
    { name: 'Karyln', role: 'developer', active: false, coreTeam: false },
    { name: 'Bibni', role: 'developer', active: true, coreTeam: false },
    { name: 'Joker', role: 'developer', active: true, coreTeam: false },
    { name: 'Le Camarade Savva', role: 'developer', active: true, coreTeam: false },
    { name: 'Princess Félicie', role: 'developer', active: true, coreTeam: true },
    { name: 'valen', role: 'developer', active: true, coreTeam: false },
    { name: 'Fendi', role: 'developer', active: true, coreTeam: false },
    { name: 'Dżanek', role: 'developer', active: true, coreTeam: false },
    { name: 'Golfy038', role: 'developer', active: true, coreTeam: false },
    { name: 'Noctis', role: 'developer', active: true, coreTeam: true },
    { name: 'Valedres', role: 'developer', active: true, coreTeam: false },
    { name: 'Shadowombat', role: 'helper', active: true, coreTeam: true },
    { name: 'Brute Epaisse', role: 'helper', active: true, coreTeam: true },
    { name: 'Monoxyd', role: 'developer', active: true, coreTeam: false }
];

export const crowdin = "https://eternaltwin.crowdin.com/multilingual/c9f4ef84da7d855637e201101992f6ed/all?languages=fr,es,en&filter=basic&value=0";
