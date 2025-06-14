import sinon from 'sinon';

import { describe, it, beforeEach, afterEach, expect } from 'vitest';
import { formatText, helpers } from './formatText';

describe('formatText', () => {

    beforeEach(() => {
        sinon.stub(helpers, "computeEmoteHtmlByKey").returns("<img/>");
        sinon.stub(helpers, "computeUiIconHtmlByKey").returns("<img/>");
        sinon.stub(helpers, "computeCharacterImageHtmlByKey").returns("<img/>");
        sinon.stub(helpers, "computeAlertImageHtmlByKey").returns("<img/>");
        sinon.stub(helpers, "computeItemStatusImageHtmlByKey").returns("<img/>");
        sinon.stub(helpers, "computePlayerStatusImageHtmlByKey").returns("<img/>");
        sinon.stub(helpers, "computeTitleImageHtmlByKey").returns("<img/>");
    });

    afterEach(() => {
        (helpers.computeEmoteHtmlByKey as any).restore();
        (helpers.computeUiIconHtmlByKey as any).restore();
        (helpers.computeCharacterImageHtmlByKey as any).restore();
        (helpers.computeAlertImageHtmlByKey as any).restore();
        (helpers.computeItemStatusImageHtmlByKey as any).restore();
        (helpers.computePlayerStatusImageHtmlByKey as any).restore();
        (helpers.computeTitleImageHtmlByKey as any).restore();
    });

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

            const result = formatText(text);

            expect(result).to.equal(`Vous avez gagné 1 <img/>
            Vous avez gagné 1 <img/>
            Vous avez perdu 3 <img/>
            Vous avez perdu 3 <img/>`);
        });
        it('should replace :hungry: with an image', () => {
            const text = `Vous avez faim :hungry:`;

            const result = formatText(text);

            expect(result).to.equal(`Vous avez faim <img/>`);
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

            const result = formatText(text);

            expect(result).to.equal("Si vous n'êtes pas Mush, chaque douche a 25% de chance de vous rapporter +1 <img/> OU + 1 <img/> OU + 2<img/>.");
        });
        it('should replace [Twinpedia](https://twin.tithom.fr/mush) by a link', () => {
            const text = "[Twinpedia](https://twin.tithom.fr/mush)";

            const result = formatText(text);

            expect(result).to.equal("<a href='https://twin.tithom.fr/mush' title='https://twin.tithom.fr/mush'>Twinpedia</a>");
        });
        it('should replace [Règlement](https://emush.eternaltwin.org/rules) by a link', () => {
            const text = "[Règlement](https://emush.eternaltwin.org/rules)";

            const result = formatText(text);

            expect(result).to.equal("<a href='https://emush.eternaltwin.org/rules' title='https://emush.eternaltwin.org/rules'>Règlement</a>");
        });
        it('should replace https://emush.eternaltwin.org/rules by a link', () => {
            const text = "https://emush.eternaltwin.org/rules";

            const result = formatText(text);

            expect(result).to.equal("<a href='https://emush.eternaltwin.org/rules' title='https://emush.eternaltwin.org/rules'>https://emush.eternaltwin.org/rules</a>");
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
