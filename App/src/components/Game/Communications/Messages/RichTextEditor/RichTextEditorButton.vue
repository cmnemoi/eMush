<template>
    <button
        type="button"
        class="format-button"
        @click="$emit('click')"
        :title="title"
    >
        <span>
            <component :is="formatTag">
                <img v-if="type === 'characters'" :src="getImgUrl('comms/buttonCharacters.png')" alt="characters">
                <img v-else-if="type === 'erase'" :src="getImgUrl('comms/buttonErase.png')" alt="erase">
                <div v-else v-html="label" />
            </component>
        </span>
    </button>
</template>

<script lang="ts">
import { defineComponent, computed } from "vue";
import { getImgUrl } from "@/utils/getImgUrl";

export default defineComponent({
    name: "RichTextEditorButton",
    props: {
        type: {
            type: String,
            required: true,
            validator: (value: string) => ['bold', 'italic', 'bolditalic', 'strike', 'characters'].includes(value)
        },
        label: {
            type: String,
            required: true
        },
        title: {
            type: String,
            required: true
        }
    },
    emits: ['click'],
    setup(props) {
        const formatTag = computed(() => {
            switch (props.type) {
            case 'bold':
                return 'strong';
            case 'italic':
                return 'em';
            case 'bolditalic':
                return 'strong';
            case 'strike':
                return 'del';
            default:
                return 'span';
            }
        });

        return {
            formatTag,
            getImgUrl
        };
    }
});
</script>

<style lang="scss" scoped>
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

.character-btn {
    font-size: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
