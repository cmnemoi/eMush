<template>
    <form class="chat-input">
        <div v-if="!showFormatDialog" class="form-container">
            <textarea
                v-model="text"
                ref="input"
                class="messageInput-area"
                :placeholder="$t('game.communications.myMessageHere')"
                @keydown.enter.exact.prevent="sendNewMessage"
                @keydown.enter.ctrl.exact.prevent="breakLine"
                @keydown.enter.shift.exact.prevent="breakLine"
                @keyup="resize()"
                @focusout ="updateTypedMessage(text)"
                @keyup.enter.exact.prevent="clearTypedMessage"
            />
            <div class="buttons-container">
                <Tippy tag="button" class="format-button" @click.prevent="editAdvancedMessage">
                    <img :src="getImgUrl('comms/format.png')" alt="format">
                    <template #content>
                        <h1 v-html="$t('game.communications.messageInputAdvanced')"/>
                        <p v-html="$t('game.communications.messageInputAdvancedDescription')"/>
                    </template>
                </Tippy>
                <button
                    class="submit-button"
                    :disabled="text <= 0"
                    @click="sendNewMessage()"
                    @click.stop="clearTypedMessage">
                    <img :src="getImgUrl('comms/submit.gif')" alt="submit">
                </button>
            </div>
        </div>

        <MessageInputAdvanced

            :visible="showFormatDialog"
            :initial-text="text"
            @cancel="closeFormatDialog"
            @send="sendNewMessage"
        />

    </form>
</template>

<script lang="ts">
import { mapActions, mapGetters } from "vuex";
import { Channel } from "@/entities/Channel";
import { Message } from "@/entities/Message";
import { defineComponent } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";
import MessageInputAdvanced from "./MessageInputAdvanced.vue";
import { Tippy } from "vue-tippy";

export default defineComponent ({
    name: "MessageInput",
    components: {
        MessageInputAdvanced  // Enregistrez le composant ici
    },
    props: {
        channel: {
            type: Channel,
            required: true
        },
        parent: {
            type: Message,
            required: false
        }
    },
    data(): any {
        return {
            text: this.typedMessage,
            showFormatDialog: false
        };
    },
    computed: {
        ...mapGetters('communication', [
            'typedMessage'
        ])
    },
    methods: {
        getImgUrl,
        sendNewMessage(messageToSend?: string): void {
            // Utiliser le paramètre s'il est fourni, sinon utiliser this.text
            const textToSend = messageToSend !== undefined ? messageToSend : this.text;
            this.showFormatDialog = false;

            if (textToSend.length > 0) {
                // Remplacer les sauts de ligne
                const formattedText = textToSend.replace(/\n/g, "//");

                // Envoyer le message
                this.sendMessage({
                    text: formattedText,
                    parent: this.parent,
                    channel: this.channel
                });
                this.text = "";
                this.showFormatDialog = false;
            }
        },
        breakLine (): void {
            // find current caret position
            const element = this.$refs.input;
            const caretPos = element.selectionStart;

            // insert \n at the caret position
            element.value = element.value.slice(0, caretPos) + "\n" + element.value.slice(caretPos);

            // move caret to the end of the inserted "//"
            element.selectionStart = element.selectionEnd = caretPos + 1;
        },
        ...mapActions('communication', [
            'sendMessage',
            'updateTypedMessage'
        ]),
        clearTypedMessage(): void {
            this.updateTypedMessage('');
            this.text = '';
        },
        resize() {
            const element = this.$refs.input;
            if (!element) return;
            element.style.height = "auto";
            element.style.height = element.scrollHeight + 2 + "px";
        },
        // Afficher laboite de dialogue de formatage avancé
        editAdvancedMessage(): void {
            this.showFormatDialog = true;
            console.log("⚠️ MessageInputAdvanced component should appears");
        },
        // Ferme le dialogue sans appliquer les changements
        closeFormatDialog(): void {
            this.showFormatDialog = false;
        }
        // TODO a supprimer : Met à jour le texte avec la version formatée
        /*
        updateFormattedText(formattedText: string): void {
            this.text = formattedText;
            this.updateTypedMessage(formattedText);
            this.showFormatDialog = false;
            setTimeout(() => {
                if (this.$refs.input) { // Attendre que this.$Refs soit accessible dans le DOM
                    this.resize();
                }
            }, 0);
        }*/
    },
    mounted() {
        this.text = this.$refs.input.value = this.typedMessage;
        this.resize();
    },
    watch: {
        typedMessage() {
            this.text = this.$refs.input.value = this.typedMessage;
            this.resize();
        }
    }
});
</script>

<style lang="scss" scoped>
    .chat-input {
        display: flex;
        position: relative;
        flex-direction: row;
        padding: 7px 7px 4px 7px;
        overflow: hidden;
    }
    .form-container {
        display: flex;
        flex-direction: row;
        align-items: flex-end;
        gap: 5px;
        margin-top:2px;
        width: 100%;
    }
    .messageInput-area {
        flex: 1; /* Le textarea prend tout l'espace disponible */
        resize: vertical;
        overflow-y: scroll;
        min-height: 58px;
        max-height: 348px;
        padding: 3px 5px;
        font-style: italic;
        opacity: 0.75;
        box-shadow: 0 1px 0 white;
        border: 1px solid #aad4e5;
        border-radius: 3px;
        @extend %game-scrollbar;

        &:active,
        &:focus {
            font-style: initial;
            opacity: 1;
        }
    }
    .format-button, .submit-button {
        cursor: pointer;
        @include button-style();
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;

        &:first-child {
            margin-bottom: 4px; /* Espace entre les deux boutons */
        }

        img {
            width: 24px;
            max-height: 24px;
        }
        /*margin-left: 4px;*/
    }

</style>
