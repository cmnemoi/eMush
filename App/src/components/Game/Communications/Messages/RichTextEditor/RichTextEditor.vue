<template>
    <div class="message-input-advanced-overlay" v-if="visible" @click.self="cancel">
        <div class="text-format-dialog">
            <div class="preview-area" v-html="formattedPreview"></div>

            <div class="toolbar">
                <div class="toolbar-formatting">
                    <RichTextEditorFormattingButton
                        v-for="button in richTextEditorFormattingButtons"
                        :key="button.type + button.action"
                        :type="button.type"
                        :label="$t(button.label)"
                        :title="$t(button.title)"
                        @click="executeRichEditorAction(button)"
                    />
                </div>
                <div class="toolbar-dialog-buttons">
                    <button
                        class="format-button"
                        @click="cancel"
                    >
                        <img :src="getImgUrl('comms/close.png')" alt="cancel">
                    </button>
                    <button
                        type="button"
                        class="format-button confirm-btn"
                        @click="confirm"
                        :disabled="editedTextLength > maxLength"
                        :title="$t('game.communications.buttonValidateAdvEditor')">
                        <img :src="getImgUrl('comms/submit.gif')" alt="send">
                    </button>
                </div>
            </div>

            <div class="edit-area-wrapper">
                <textarea
                    v-model="editedText"
                    class="edit-area"
                    @select="selectHighlightedText"
                    @keydown.esc.exact.prevent="cancel"
                    @keydown.enter.exact.prevent="confirm"
                    ref="textEditor"
                />
                <div class="character-count" :class="{ 'getting-close': editedTextLength > maxLength/2, 'over-limit': editedTextLength > maxLength}">
                    {{ editedTextLength }} / {{ maxLength}}
                </div>
            </div>

            <ul class="emote-tabs">
                <RichTextEditorEmoteButton
                    v-for="(button, i) in richTextEditorEmoteButtons"
                    :key="i"
                    :config="button"
                    :selected="selectedEmoteTab == i"
                    @select="selectedEmoteTab = i"
                />
            </ul>
            <RichTextEditorEmotePanel v-if="selectedEmoteTab != -1" :config="richTextEditorEmoteButtons[selectedEmoteTab]" @emote="insertEmote" />
            <div v-else class="emote-line"/>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { characterEnum, CharacterInfos } from "@/enums/character";
import { formatText } from "@/utils/formatText";
import RichTextEditorEmotePanel from "./RichTextEditorEmotePanel.vue";
import RichTextEditorEmoteButton  from "./RichTextEditorEmoteButton.vue";
import RichTextEditorFormattingButton from "./RichTextEditorFormattingButton.vue";
import {
    richTextEditorFormattingButtons, RichTextEditorFormattingButtonConfig, FormattingType,
    richTextEditorEmoteButtons
} from "./RichTextEditorConfig";
import {
    applySelectedTextFormatting,
    clearSelectedTextFormattingLogic,
    insertTextAtPositionLogic,
    formatEmote,
    TextSelection,
    getFormattingLengthForType
} from "@/utils/richTextFormatter";

export default defineComponent({
    name: "RichTextEditor",
    components: {
        RichTextEditorEmoteButton,
        RichTextEditorEmotePanel,
        RichTextEditorFormattingButton
    },
    props: {
        initialText: {
            type: String,
            default: ""
        },
        visible: {
            type: Boolean,
            default: false
        },
        maxLength: {
            type: Number,
            required: true
        }
    },
    data() {
        return {
            editedText: this.initialText,
            selectedEmoteTab: 0,
            selection: {
                start: 0,
                end: 0
            },
            showCharacterGrid: false,
            characters: characterEnum as {[key: string]: CharacterInfos},
            richTextEditorEmoteButtons,
            richTextEditorFormattingButtons
        };
    },
    emits: ['cancel', 'send'],
    computed: {
        formattedPreview(): string {
            // Conversion des marqueurs markdown en HTML pour la prévisualisation
            return formatText(this.editedText.replace(/\n/g, "//"));
        },
        editedTextLength(): number {
            return this.editedText ? this.editedText.length + this.editedText.split('\n').length - 1 : 0;
        }
    },
    watch: {
        visible(newVal: boolean) {
            if (!newVal) {
                return;
            }
            this.editedText = this.initialText;
            this.$nextTick(() => {
                (this.$refs.textEditor as HTMLTextAreaElement).focus();
            });
        }
    },
    methods: {
        getImgUrl,
        selectHighlightedText(): void {
            const element = this.$refs.textEditor as HTMLTextAreaElement;
            this.selection = {
                start: element.selectionStart,
                end: element.selectionEnd
            };
        },

        executeRichEditorAction(button: RichTextEditorFormattingButtonConfig): void {
            switch (button.action) {
            case 'clearFormatting':
                this.clearFormatting();
                break;
            case 'applyFormatting':
                if (!button.actionParam) {
                    return;
                }
                this.applyFormatting(button.actionParam as FormattingType);
                break;
            }
        },

        applyFormatting(type: FormattingType): void {
            const element = this.$refs.textEditor as HTMLTextAreaElement;
            let selection = this.getTextSelection(element);

            // If no text was selected create a new formatted block at cursor position
            if (!this.isValidSelection(selection)) {
                selection = {
                    start: element.selectionStart,
                    end: element.selectionStart
                };
            }

            try {
                const { newFullText, modifiedPart } = applySelectedTextFormatting(this.editedText, selection, type);
                const cursorPosition = selection.start + modifiedPart.length - getFormattingLengthForType(type);
                this.editedText = newFullText;
                this.updateCursorPosition(element, cursorPosition);
            } catch (error) {
                console.error('Error applying formatting:', error);
            }
        },

        clearFormatting(): void {
            const element = this.$refs.textEditor as HTMLTextAreaElement;
            const selection = this.getTextSelection(element);

            if (!this.isValidSelection(selection)) {
                return;
            }

            try {
                const { newFullText, modifiedPart } = clearSelectedTextFormattingLogic(this.editedText, selection);
                this.editedText = newFullText;
                this.updateCursorPosition(element, selection.start + modifiedPart.length);
            } catch (error) {
                console.error('Error clearing formatting:', error);
            }
        },

        insertEmote(emote: string): void {
            const element = this.$refs.textEditor as HTMLTextAreaElement;
            const cursorPosition = element.selectionStart;

            try {
                const formattedCharacter = formatEmote(emote);
                this.editedText = insertTextAtPositionLogic(this.editedText, cursorPosition, formattedCharacter);
                this.updateCursorPosition(element, cursorPosition + formattedCharacter.length);
                this.closeCharacterGrid();
            } catch (error) {
                console.error('Error inserting emote:', error);
            }
        },

        cancel(): void {
            this.$emit('cancel');
            this.showCharacterGrid = false;
            this.editedText = "";
        },

        confirm(): void {
            if (this.editedTextLength <= this.maxLength) {
                this.$emit('send', this.editedText);
                this.showCharacterGrid = false;
                this.editedText = "";
            }
        },

        isValidSelection(selection: TextSelection): boolean {
            return selection.start !== selection.end;
        },

        getTextSelection(element: HTMLTextAreaElement): TextSelection {
            const originalStart = element.selectionStart;
            const originalEnd = element.selectionEnd;

            // Only on correct selection, search for extended selection (with * or ~ symbols)
            if (originalStart !== originalEnd) {
                const newStart = this.getPositionbeforeSelected(originalStart);
                const newEnd = this.getPositionAfterSelected(originalEnd);

                if (newStart !== originalStart || newEnd !== originalEnd) {
                    element.setSelectionRange(newStart, newEnd);
                }
            }

            return {
                start: element.selectionStart,
                end: element.selectionEnd
            };
        },
        getPositionbeforeSelected(start: number): number {
            // used to search formatting tag before selection, return new start position
            let index = start - 1;
            while (index >= 0 && /[*~]/.test(this.editedText[index])) {
                index--;
            }
            return index + 1;
        },
        getPositionAfterSelected(end: number): number {
            // used to search formatting tag after selection, return new end position
            let index = end;
            while (index < this.editedTextLength && /[*~]/.test(this.editedText[index])) {
                index++;
            }
            return index;
        },

        updateCursorPosition(element: HTMLTextAreaElement, position: number): void {
            this.$nextTick(() => {
                element.focus();
                element.selectionStart = element.selectionEnd = position;
            });
        },

        closeCharacterGrid(): void {
            this.showCharacterGrid = false;
        }
    }
});
</script>

<style lang="scss" scoped>
@use "sass:color";

.message-input-advanced-overlay {
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0);
    display: flex;
    flex-direction: row;
    position: sticky;
    max-width: 100%;
    justify-content: left;
    z-index: 1000;
}

.text-format-dialog {
    background-color: #fff;
    border-radius: 3px;
    padding: 10px;
    position: relative;
    width: 400px;
    max-width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    gap: 5px;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
    span {
        display: inline; /* Forcer les <span> à être inline */
    }
}

.toolbar {
    display: flex;
    flex-flow: row wrap;
    gap: 5px;
    justify-content: space-between;
    flex-wrap: wrap;
}

.toolbar-formatting {
    flex-flow: row wrap;
    justify-content: flex-start;
    gap: 5px;
}

.toolbar-dialog-buttons{
    flex-flow: row wrap;
    justify-content: flex-end;
    gap: 10px;
}


.edit-area-wrapper {
    position: relative;

    .edit-area {
        width: 100%;
        min-height: 90px;
        padding: 8px;
        border: 1px solid #aad4e5;
        border-radius: 3px;
        font-family: inherit;
        resize: vertical;
    }

    .character-count {
        position: absolute;
        bottom: 3px;
        right: 3px;
        font-size: 11px;
        font-style: italic;
        font-weight: bold;
        color: transparent; // Only display when over the limit
        opacity: 0;
        padding: 2px 5px;
        border-radius: 3px;
        pointer-events: none;

        &.getting-close {
            color: rgba(128, 128, 128, 0.75);
            opacity: 1;
        }

        &.over-limit {
            color: #d32f2f;
            opacity: 1;
        }
    }
}

.preview-area {
    width: 100%;
    min-height: 90px;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    background-color: #eee;
    overflow-y: auto;
    scroll-behavior: smooth;
    display: inline;
    word-break: break-word;
    :deep(em) {
        color: color.adjust(#cf1830, $lightness: 15%);
    }
}

.dialog-buttons {
    display: flex;
    justify-content: flex-end;
    flex-flow: row wrap;
    gap: 10px;
    margin-top: 10px;
}

.confirm-btn {
    background-color: #008EE5;
    border: 1px solid #008EE5;
    color: white;

    &:hover {
        background-color: #015e97;
    }
}

.format-button {
    display: inline-block;
    cursor: pointer;
    @include button-style();

    & {
        width: 24px;
        margin-left: 4px;
    }

    &:hover {
        background-color: #00B0EC;
    }
}

.emote-tabs {
    justify-content: space-between;
    margin-right: -4px;
}

.emote-line {
    margin-top: -5px;
    border: 2px solid #0074df;
    border-bottom-left-radius: 3px;
    border-bottom-right-radius: 3px;
}

.character-grid {
    display: grid;
    position: absolute; /* Positionnement absolu pour superposer */
    top: 40px;
    left: 0px;
    z-index: auto; /* S'assure que la grille est au-dessus des autres éléments */
    grid-template-columns: repeat(5, 1fr); /* 4 colonnes pour 16 personnages */
    gap: 3px;
    margin-bottom: 10px;
    max-height: 220px;
    overflow-y: auto;
    border: 1px solid #aad4e5;
    border-radius: 3px;
    padding: 3px;
    background-color: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2); /* Ajoute une ombre pour l'effet popup */
}

.character-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 2px;
    border-radius: 3px;
    transition: background-color 0.2s;

    &:hover {
        background-color: #e9f5fb;
    }

    img {
        height: 16px;
        object-fit: cover;
        border-radius: 5%;
    }

    .character-name {
        margin-top: 4px;
        font-size: 11px;
        text-align: center;
    }
}

.character-btn {
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}

</style>
