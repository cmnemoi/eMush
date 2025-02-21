import { test, expect } from 'vitest';
import { Project, BonusSkill } from './Project';

test('toString should return project text representation', () => {
    const project = new Project();
    const bonusSkill1: BonusSkill = { key:'cool_perk', name:'Cool Perk', description: 'A cool perk!' };
    const bonusSkill2: BonusSkill = { key:'cool_perk_also', name:'Cool Perk Also', description: 'A cool perk also!' };
    project.name = 'Omega-extinguisher';
    project.description = 'Eliminates fires forever!';
    project.progress = '50%';
    project.bonusSkills = [
        bonusSkill1,
        bonusSkill2
    ];
    expect(project.toString()).toBe('**Omega-extinguisher** : Cool Perk, Cool Perk Also // *Eliminates fires forever!* 50%');
});
