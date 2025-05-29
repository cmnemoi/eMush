<template>
    <div class="message-input-advanced-overlay" v-if="visible" @click.self="cancel">
        <div class="text-format-dialog">
            <div class="format-controls">
                <RichTextEditorButton
                    v-for="button in richTextEditorButtons"
                    :key="button.type + button.action"
                    :type="button.type"
                    :label="$t(button.label)"
                    :title="$t(button.title)"
                    @click="executeRichEditorAction(button)"
                />
            </div>
            <RichTextEditorCharacterEmotes v-if="showCharacterGrid" :characters="characters" @character-selected="insertCharacter" />

            <textarea
                v-model="editedText"
                class="edit-area"
                @select="selectHighlightedText"
                @keydown.esc.exact.prevent="cancel"
                @keydown.enter.exact.prevent="confirm"
                ref="textEditor"
            ></textarea>

            <div class="preview-area" v-html="formattedPreview"></div>

            <div class="dialog-buttons">
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
                    :title="$t('game.communications.buttonValidateAdvEditor')">
                    <img :src="getImgUrl('comms/submit.gif')" alt="send">
                </button>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import { characterEnum, CharacterInfos } from "@/enums/character";
import { formatText } from "@/utils/formatText";
import RichTextEditorCharacterEmotes from "./RichTextEditorCharacterEmotes.vue";
import RichTextEditorButton from "./RichTextEditorButton.vue";
import { richTextEditorButtons, RichTextEditorButtonConfig, FormattingType } from "./RichTextEditorConfig";
import {
    applySelectedTextFormatting,
    clearSelectedTextFormattingLogic,
    insertTextAtPositionLogic,
    formatCharacterName,
    TextSelection
} from "@/utils/richTextFormatter";

export default defineComponent({
    name: "RichTextEditor",
    components: {
        RichTextEditorCharacterEmotes,
        RichTextEditorButton
    },
    props: {
        initialText: {
            type: String,
            default: ""
        },
        visible: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            editedText: this.initialText,
            selection: {
                start: 0,
                end: 0
            },
            showCharacterGrid: false,
            characters: characterEnum as {[key: string]: CharacterInfos},
            richTextEditorButtons
        };
    },
    computed: {
        formattedPreview(): string {
            // Conversion des marqueurs markdown en HTML pour la prévisualisation
            return formatText(this.editedText);
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

        executeRichEditorAction(button: RichTextEditorButtonConfig): void {
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
            case 'toggleCharacterGrid':
                this.toggleCharacterGrid();
                break;
            }
        },

        applyFormatting(type: FormattingType): void {
            const element = this.$refs.textEditor as HTMLTextAreaElement;
            const selection = this.getTextSelection(element);

            if (!this.isValidSelection(selection)) {
                return;
            }

            try {
                const { newFullText, modifiedPart } = applySelectedTextFormatting(this.editedText, selection, type);
                this.editedText = newFullText;
                this.updateCursorPosition(element, selection.start + modifiedPart.length);
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

        insertCharacter(characterName: string): void {
            const element = this.$refs.textEditor as HTMLTextAreaElement;
            const cursorPosition = element.selectionStart;

            try {
                const formattedCharacter = formatCharacterName(characterName);
                this.editedText = insertTextAtPositionLogic(this.editedText, cursorPosition, formattedCharacter);
                this.updateCursorPosition(element, cursorPosition + formattedCharacter.length);
                this.closeCharacterGrid();
            } catch (error) {
                console.error('Error inserting character:', error);
            }
        },

        cancel(): void {
            this.$emit('cancel');
            this.showCharacterGrid = false;
            this.editedText = "";
        },

        confirm(): void {
            this.$emit('send', this.editedText);
            this.showCharacterGrid = false;
            this.editedText = "";
        },

        toggleCharacterGrid(): void {
            this.showCharacterGrid = !this.showCharacterGrid;
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
            while (index < this.editedText.length && /[*~]/.test(this.editedText[index])) {
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

<!-- Formattage CSS  =================================================================================  -->
<style lang="scss" scoped>

    .message-input-advanced-overlay {
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0);
        display: flex;
        flex-direction: row;
        position: sticky;
        max-width: 97%;
        justify-content: left;
        align-items: left;
        z-index: 1000;
    }

    .text-format-dialog {
        background-color: #fff;
        border-radius: 3px;
        padding: 10px;
        position: relative;
        width: 400px;
        max-width: 100%;
        min-width: 290px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        gap: 10px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.2);
        span {
            display: inline; /* Forcer les <span> à être inline */
        }
    }

    .format-controls {
        display: flex;
        flex-flow: row wrap;
        gap: 5px;
        justify-content: flex-start;
        margin-bottom: 10px;
        flex-wrap: wrap;
    }

    .edit-area {
        width: 100%;
        min-height: 90px;
        padding: 8px;
        border: 1px solid #aad4e5;
        border-radius: 3px;
        font-family: inherit;
        resize: vertical;
    }

    .preview-area {
        width: 100%;
        min-height: 120px;
        max-height: 150px;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 3px;
        background-color: #f9f9f9;
        overflow-y: auto;
        scroll-behavior: smooth;
        display: inline;
        :deep(em) {
            color: lighten(#cf1830, 15);
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
        width: 24px;
        margin-left: 4px;
        &:hover {
            background-color: #00B0EC;
        }
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
            width: 16px;
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
