import { describe, it, expect } from 'vitest';
import {
    cleanExistingFormatting,
    applyFormattingForType,
    formatEmote,
    applySelectedTextFormatting,
    clearSelectedTextFormattingLogic,
    insertTextAtPositionLogic,
    TextSelection,
    FormatOperationResult
} from './richTextFormatter';
import { FormattingType } from '@/components/Game/Communications/Messages/RichTextEditor/RichTextEditorConfig';

describe('richTextFormatter', () => {

    // Helper function for test setup (Given)
    function givenTextAndSelection(text: string, start: number, end: number): { text: string; selection: TextSelection } {
        return { text, selection: { start, end } };
    }

    describe('cleanExistingFormatting', () => {
        it('should remove bold formatting markers', () => {
            const result = cleanExistingFormatting('**bold text**');
            expect(result).toBe('bold text');
        });

        it('should remove italic formatting markers', () => {
            const result = cleanExistingFormatting('*italic text*');
            expect(result).toBe('italic text');
        });

        it('should remove bold-italic formatting markers', () => {
            const result = cleanExistingFormatting('***bold italic text***');
            expect(result).toBe('bold italic text');
        });

        it('should remove strike-through formatting markers', () => {
            const result = cleanExistingFormatting('~~strike text~~');
            expect(result).toBe('strike text');
        });

        it('should remove mixed and nested formatting markers', () => {
            const result = cleanExistingFormatting('**~~mixed *nested* text~~**');
            expect(result).toBe('mixed nested text');
        });

        it('should remove all formatting from text with internal markers', () => {
            const result = cleanExistingFormatting('**text with *internal* marker and ~~more~~**');
            expect(result).toBe('text with internal marker and more');
        });

        it('should return the same text if no formatting is present', () => {
            const result = cleanExistingFormatting('plain text');
            expect(result).toBe('plain text');
        });

        it('should return an empty string for an empty input', () => {
            const result = cleanExistingFormatting('');
            expect(result).toBe('');
        });

        it('should handle text with only formatting markers', () => {
            const result = cleanExistingFormatting('***');
            expect(result).toBe('');
        });

        it('should handle text with only single formatting markers', () => {
            const result = cleanExistingFormatting('*');
            expect(result).toBe('');
        });
    });

    describe('applyFormattingForType', () => {
        it('should apply bold formatting to single word', () => {
            const result = applyFormattingForType('test', 'bold');
            expect(result).toBe('**test**');
        });

        it('should apply bold formatting to multiple words', () => {
            const result = applyFormattingForType('multiple words test', 'bold');
            expect(result).toBe('**multiple words test**');
        });

        it('should apply italic formatting to single word', () => {
            const result = applyFormattingForType('test', 'italic');
            expect(result).toBe('*test*');
        });

        it('should apply italic formatting to multiple words', () => {
            const result = applyFormattingForType('another test phrase', 'italic');
            expect(result).toBe('*another test phrase*');
        });

        it('should apply bolditalic formatting to single word', () => {
            const result = applyFormattingForType('test', 'bolditalic');
            expect(result).toBe('***test***');
        });

        it('should apply bolditalic formatting to multiple words', () => {
            const result = applyFormattingForType('complex bold italic example', 'bolditalic');
            expect(result).toBe('***complex bold italic example***');
        });

        it('should apply strike formatting to single word', () => {
            const result = applyFormattingForType('test', 'strike');
            expect(result).toBe('~~test~~');
        });

        it('should apply strike formatting to multiple words', () => {
            const result = applyFormattingForType('strike through this sentence', 'strike');
            expect(result).toBe('~~strike through this sentence~~');
        });

        it('should apply bold formatting to already italic text (nested)', () => {
            const result = applyFormattingForType('*already italic*', 'bold');
            expect(result).toBe('***already italic***');
        });

        it('should apply italic formatting to already bold text (nested)', () => {
            const result = applyFormattingForType('**already bold**', 'italic');
            expect(result).toBe('***already bold***');
        });

        it('should return original text for unknown formatting type', () => {
            const result = applyFormattingForType('test', 'unknown' as FormattingType);
            expect(result).toBe('test');
        });

        it('should handle empty string input', () => {
            const result = applyFormattingForType('', 'bold');
            expect(result).toBe('****');
        });
    });

    describe('formatEmote', () => {
        it('should wrap emote name with colons', () => {
            const result = formatEmote('mush');
            expect(result).toBe(':mush:');
        });

        it('should handle empty string', () => {
            const result = formatEmote('');
            expect(result).toBe('::');
        });
    });

    describe('applySelectedTextFormatting', () => {
        it('should apply bold formatting to selected text in the middle', () => {
            const { text, selection } = givenTextAndSelection('hello world', 6, 11); // selects "world"
            const result = applySelectedTextFormatting(text, selection, 'bold');
            expect(result.newFullText).toBe('hello **world**');
            expect(result.modifiedPart).toBe('**world**');
        });

        it('should apply bold formatting to selected multiple words in the middle', () => {
            const { text, selection } = givenTextAndSelection('this is a test sentence', 8, 23); // selects "a test sentence"
            const result = applySelectedTextFormatting(text, selection, 'bold');
            expect(result.newFullText).toBe('this is **a test sentence**');
            expect(result.modifiedPart).toBe('**a test sentence**');
        });

        it('should apply italic formatting to selected text at the beginning', () => {
            const { text, selection } = givenTextAndSelection('hello world', 0, 5); // selects "hello"
            const result = applySelectedTextFormatting(text, selection, 'italic');
            expect(result.newFullText).toBe('*hello* world');
            expect(result.modifiedPart).toBe('*hello*');
        });

        it('should apply strike formatting to selected text at the end', () => {
            const { text, selection } = givenTextAndSelection('hello world', 6, 11); // selects "world"
            const result = applySelectedTextFormatting(text, selection, 'strike');
            expect(result.newFullText).toBe('hello ~~world~~');
            expect(result.modifiedPart).toBe('~~world~~');
        });

        it('should apply italic formatting to a partial word selection', () => {
            const { text, selection } = givenTextAndSelection('beautiful day', 0, 4); // selects "beau"
            const result = applySelectedTextFormatting(text, selection, 'italic');
            expect(result.newFullText).toBe('*beau*tiful day');
            expect(result.modifiedPart).toBe('*beau*');
        });

        it('should apply strike formatting to a selection spanning existing formatting', () => {
            const { text, selection } = givenTextAndSelection('text with **bold** part', 10, 22); // selects "**bold** par"
            const result = applySelectedTextFormatting(text, selection, 'strike');
            expect(result.newFullText).toBe('text with ~~bold par~~t');
            expect(result.modifiedPart).toBe('~~bold par~~');
        });

        it('should apply bolditalic formatting to a selection with mixed existing formatting', () => {
            const { text, selection } = givenTextAndSelection('some *italic* and ~~strike~~ text', 5, 28); // selects "*italic* and ~~strike~~"
            const result = applySelectedTextFormatting(text, selection, 'bolditalic');
            expect(result.newFullText).toBe('some ***italic and strike*** text');
            expect(result.modifiedPart).toBe('***italic and strike***');
        });

        it('should clean existing formatting before applying new formatting', () => {
            const { text, selection } = givenTextAndSelection('hello *world*', 6, 13); // selects "*world*"
            const result = applySelectedTextFormatting(text, selection, 'bold');
            expect(result.newFullText).toBe('hello **world**');
            expect(result.modifiedPart).toBe('**world**');
        });

        it('should handle empty selection', () => {
            const { text, selection } = givenTextAndSelection('hello world', 5, 5); // empty selection
            const result = applySelectedTextFormatting(text, selection, 'bold');
            expect(result.newFullText).toBe('hello**** world');
            expect(result.modifiedPart).toBe('****');
        });
    });

    describe('clearSelectedTextFormattingLogic', () => {
        it('should clear bold formatting from selected text', () => {
            const { text, selection } = givenTextAndSelection('hello **world**', 6, 15); // selects "**world**"
            const result = clearSelectedTextFormattingLogic(text, selection);
            expect(result.newFullText).toBe('hello world');
            expect(result.modifiedPart).toBe('world');
        });

        it('should clear bold formatting from selected multiple words', () => {
            const { text, selection } = givenTextAndSelection('this is **a test sentence**', 8, 27); // selects "**a test sentence**"
            const result = clearSelectedTextFormattingLogic(text, selection);
            expect(result.newFullText).toBe('this is a test sentence');
            expect(result.modifiedPart).toBe('a test sentence');
        });

        it('should clear italic formatting from selected text', () => {
            const { text, selection } = givenTextAndSelection('hello *world*', 6, 13); // selects "*world*"
            const result = clearSelectedTextFormattingLogic(text, selection);
            expect(result.newFullText).toBe('hello world');
            expect(result.modifiedPart).toBe('world');
        });

        it('should clear strike formatting from selected text', () => {
            const { text, selection } = givenTextAndSelection('hello ~~world~~', 6, 15); // selects "~~world~~"
            const result = clearSelectedTextFormattingLogic(text, selection);
            expect(result.newFullText).toBe('hello world');
            expect(result.modifiedPart).toBe('world');
        });

        it('should clear italic formatting from a partial word selection', () => {
            const { text, selection } = givenTextAndSelection('*beautiful* day', 0, 5); // selects "*beau"
            const result = clearSelectedTextFormattingLogic(text, selection);
            expect(result.newFullText).toBe('beautiful* day'); // Only *beau* is cleaned, leaving the trailing *
            expect(result.modifiedPart).toBe('beau');
        });

        it('should clear strike formatting from a selection spanning existing formatting', () => {
            const { text, selection } = givenTextAndSelection('text with ~~bold~~ part', 10, 22); // selects "~~bold~~ par"
            const result = clearSelectedTextFormattingLogic(text, selection);
            expect(result.newFullText).toBe('text with bold part');
            expect(result.modifiedPart).toBe('bold par');
        });

        it('should clear mixed formatting from a selection with nested formatting', () => {
            const { text, selection } = givenTextAndSelection('some ***italic and strike*** text', 5, 28); // selects "***italic and strike***"
            const result = clearSelectedTextFormattingLogic(text, selection);
            expect(result.newFullText).toBe('some italic and strike text');
            expect(result.modifiedPart).toBe('italic and strike');
        });

        it('should handle nested formatting', () => {
            const { text, selection } = givenTextAndSelection('hello **~~world~~**', 6, 19); // selects "**~~world~~**"
            const result = clearSelectedTextFormattingLogic(text, selection);
            expect(result.newFullText).toBe('hello world');
            expect(result.modifiedPart).toBe('world');
        });

        it('should return original text if no formatting is present in selection', () => {
            const { text, selection } = givenTextAndSelection('hello world', 6, 11); // selects "world"
            const result = clearSelectedTextFormattingLogic(text, selection);
            expect(result.newFullText).toBe('hello world');
            expect(result.modifiedPart).toBe('world');
        });

        it('should handle empty selection', () => {
            const { text, selection } = givenTextAndSelection('hello world', 5, 5); // empty selection
            const result = clearSelectedTextFormattingLogic(text, selection);
            expect(result.newFullText).toBe('hello world');
            expect(result.modifiedPart).toBe('');
        });
    });

    describe('insertTextAtPositionLogic', () => {
        it('should insert text at the beginning of the string', () => {
            const result = insertTextAtPositionLogic('world', 0, 'hello ');
            expect(result).toBe('hello world');
        });

        it('should insert text in the middle of the string', () => {
            const result = insertTextAtPositionLogic('helloworld', 5, ' ');
            expect(result).toBe('hello world');
        });

        it('should insert text at the end of the string', () => {
            const result = insertTextAtPositionLogic('hello', 5, ' world');
            expect(result).toBe('hello world');
        });

        it('should handle inserting into an empty string', () => {
            const result = insertTextAtPositionLogic('', 0, 'hello');
            expect(result).toBe('hello');
        });

        it('should handle inserting an empty string', () => {
            const result = insertTextAtPositionLogic('hello world', 5, '');
            expect(result).toBe('hello world');
        });
    });
});
