const { expect } = require('chai');
const sinon = require('sinon');
const { formatText, helpers } = require("./formatText.js");

describe('formatText', () => {

    before(() => {
        sinon.stub(helpers, "computeImageHtml").returns("<img/>");
    });

    after(() => {
        helpers.computeImageHtml.restore();
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
        it('should replace :pm: and :pa: tags with an image', () => {
            const text = "Vous avez gagné 1 :pm: \nVous avez gagné 1 :pa:";

            const result = formatText(text);

            expect(result).to.equal("Vous avez gagné 1 <img/> \nVous avez gagné 1 <img/>");
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
    });
});
