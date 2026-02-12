export type FormattingType = 'bold' | 'italic' | 'bolditalic' | 'strike';
export type RichTextEditorButtonType = 'erase' | FormattingType;

export interface RichTextEditorFormattingButtonConfig {
    type: RichTextEditorButtonType;
    label: string;
    title: string;
    action: 'clearFormatting' | 'applyFormatting';
    actionParam?: string;
}

export const richTextEditorFormattingButtons: RichTextEditorFormattingButtonConfig[] = [
    {
        type: 'bold',
        label: 'game.communications.boldButtonTitle',
        title: 'game.communications.boldButtonDescription',
        action: 'applyFormatting',
        actionParam: 'bold'
    },
    {
        type: 'italic',
        label: 'game.communications.italicButtonTitle',
        title: 'game.communications.italicButtonDescription',
        action: 'applyFormatting',
        actionParam: 'italic'
    },
    {
        type: 'bolditalic',
        label: 'game.communications.boldItalicButtonTitle',
        title: 'game.communications.boldItalicButtonDescription',
        action: 'applyFormatting',
        actionParam: 'bolditalic'
    },
    {
        type: 'strike',
        label: 'game.communications.strikeButtonTitle',
        title: 'game.communications.strikeButtonDescription',
        action: 'applyFormatting',
        actionParam: 'strike'
    },
    {
        type: 'erase',
        label: 'game.communications.eraseButtonTitle',
        title: 'game.communications.eraseButtonDescription',
        action: 'clearFormatting'
    }
];
