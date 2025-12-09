import sinon from 'sinon';

import * as formatTextModule from './formatText';
import { describe, it, beforeEach, afterEach, expect } from 'vitest';
import { formatText } from './formatText';

describe('formatText', () => {

    describe('Simple tests', () => {
        it('return an empty string when given an empty string', () => {
            const text = "";

            const result = formatText(text);

            expect(result).to.equal("");
        });
        it('should replace **abc** with <strong>abc</strong>', () => {
            const text = "**Raluca** a pris un Débris métallique.";

            const result = formatText(text);

            expect(result).to.equal("<strong>Raluca</strong> a pris un Débris métallique.");
        });
        it('should replace *abc* with <em>abc</em>', () => {
            const text = "*Raluca* a pris un Débris métallique.";

            const result = formatText(text);

            expect(result).to.equal("<em>Raluca</em> a pris un Débris métallique.");
        });
        it('should replace :hp:, :pm:, :pa: and :pmo tags with an image', () => {
            const text = `Vous avez gagné 1 :pm:
            Vous avez gagné 1 :pa:
            Vous avez perdu 3 :hp:
            Vous avez perdu 3 :pmo:`;

            const result = formatText(text).replace(/<img[^>]*>/g, "<img/>");

            expect(result).to.equal(`Vous avez gagné 1 <img/>
            Vous avez gagné 1 <img/>
            Vous avez perdu 3 <img/>
            Vous avez perdu 3 <img/>`);
        });
        it('should not replace :does_not_exists:', () => {
            const text = `Quel est le sens de la vie :does_not_exists: ?`;

            const result = formatText(text);

            expect(result).to.equal(text);
        });
        it('should replace // with <br>', () => {
            const text = `Raluca a pris un Débris métallique.//Raluca a pris un Débris métallique.`;

            const result = formatText(text);

            expect(result).to.equal(`Raluca a pris un Débris métallique.<br>Raluca a pris un Débris métallique.`);
        });
        it('should replace ://\\n with <br>', () => {
            const text = `Raluca a pris un Débris métallique://
            Raluca a pris un Débris métallique.`;

            const result = formatText(text);

            expect(result).to.equal(`Raluca a pris un Débris métallique:<br>
            Raluca a pris un Débris métallique.`);
        });
        it('should replace 1 :pmo: by 1 image', () => {
            const text = "Si vous n'êtes pas Mush, chaque douche a 25% de chance de vous rapporter +1 :hp: OU + 1 :pmo: OU + 2:pm:.";

            const result = formatText(text).replace(/<img\b[^>]*>/g, "<img/>");

            expect(result).to.equal("Si vous n'êtes pas Mush, chaque douche a 25% de chance de vous rapporter +1 <img/> OU + 1 <img/> OU + 2<img/>.");
        });
        it('should replace [Règlement](https://emush.eternaltwin.org/rules) by a link', () => {
            const text = "[Règlement](https://emush.eternaltwin.org/rules)";

            const result = formatText(text);

            expect(result).to.equal("<a href='https://emush.eternaltwin.org/rules' title='https://emush.eternaltwin.org/rules' target='_blank' rel='noopener noreferrer'>Règlement</a>");
        });
        it('should not replace [Unknown](https://unknown.host.org) by a link', () => {
            const text = "[Unknown](https://unknown.host.org)";

            const result = formatText(text);

            expect(result).to.equal(text);
        });
        it('should replace https://emush.eternaltwin.org/rules by a link', () => {
            const text = "https://emush.eternaltwin.org/rules";

            const result = formatText(text);

            expect(result).to.equal("<a href='https://emush.eternaltwin.org/rules' title='https://emush.eternaltwin.org/rules' target='_blank' rel='noopener noreferrer'>https://emush.eternaltwin.org/rules</a>");
        });
        it('should handle line breaks after a link', () => {
            const text = "https://emush.eternaltwin.org/rules//hello";

            const result = formatText(text);

            expect(result).to.equal("<a href='https://emush.eternaltwin.org/rules' title='https://emush.eternaltwin.org/rules' target='_blank' rel='noopener noreferrer'>https://emush.eternaltwin.org/rules</a><br>hello");
        });
        it('should not replace https://unknown.host.org by a link', () => {
            const text = "https://unknown.host.org";

            const result = formatText(text);

            expect(result).to.equal(text);
        });
    });


    describe("Complex tests", () => {
        it('should allow multiple bold and italic elements', () => {
            const text = `**Raluca** a laché *un* **Débris métallique**
            **Raluca** a pris *un* **Débris métallique.**`;

            const result = formatText(text);

            expect(result).to.equal(`<strong>Raluca</strong> a laché <em>un</em> <strong>Débris métallique</strong>
            <strong>Raluca</strong> a pris <em>un</em> <strong>Débris métallique.</strong>`);
        });
        it('should allow combination of bold and italic', () => {
            const text = "***INVENTAIRE***";

            const result = formatText(text);

            expect([
                "<strong><em>INVENTAIRE</em></strong>",
                "<em><strong>INVENTAIRE</strong></em>",
                "<strong><em>INVENTAIRE</strong></em>",
                "<em><strong>INVENTAIRE</em></strong>"
            ]).to.include(result);
        });
        it('should allow multiple nested bold, italic and strike elements', () => {
            const text = "**~~mixed *nested* text~~**";
            const result = formatText(text);

            expect(result).to.equal("<strong><s>mixed <em>nested</em> text</s></strong>");
        });
    });
});
