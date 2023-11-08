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
    { name: 'Simpkin', role: 'admin', active: true, coreTeam: true },
    { name: 'rackhaml', role: 'helper', active: false, coreTeam: false },
    { name: 'Haux49', role: 'developer', active: false, coreTeam: false },
    { name: 'Demurgos', role: 'developer', active: false, coreTeam: false },
    { name: 'Biosha', role: 'developer', active: false, coreTeam: false },
    { name: 'Tishwa', role: 'developer', active: false, coreTeam: false },
    { name: 'Sami / EaCOS', role: 'developer', active: false, coreTeam: false },
    { name: 'MoUoA', role: 'helper', active: false, coreTeam: false },
    { name: 'Yasgal', role: 'artist', active: true, coreTeam: false },
    { name: 'Zerah', role: 'developer', active: false, coreTeam: false },
    { name: 'ReNacK', role: 'developer', active: false, coreTeam: false },
    { name: 'amadare', role: 'developer', active: false, coreTeam: false },
    { name: 'Dylan57', role: 'translator', active: false, coreTeam: false },
    { name: 'masterx050', role: 'translator', active: false, coreTeam: false },
    { name: 'Aura', role: 'translator', active: false, coreTeam: false },
    { name: 'res7less', role: 'translator', active: false, coreTeam: false },
    { name: 'JunoDaBat', role: 'translator', active: false, coreTeam: false },
    { name: 'AubeRouge', role: 'translator', active: false, coreTeam: false },
    { name: 'ekkit', role: 'translator', active: false, coreTeam: false },
    { name: 'Aphadion', role: 'translator', active: false, coreTeam: false },
    { name: 'Dridridu45', role: 'translator', active: false, coreTeam: false },
    { name: 'factoryman942', role: 'translator', active: false, coreTeam: false },
    { name: 'Guilherande', role: 'translator', active: false, coreTeam: false },
    { name: 'RockRom', role: 'translator', active: false, coreTeam: false },
    { name: 'unukun', role: 'translator', active: false, coreTeam: false },
];

export const crowdin = "https://eternaltwin.crowdin.com/multilingual/c9f4ef84da7d855637e201101992f6ed/all?languages=fr,es,en&filter=basic&value=0";
