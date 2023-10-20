<template>
    <div :class="className">
        <label v-if="label" :for="id">{{ label }}</label>
        <input
            :id="id"
            :ref="id"
            :value="modelValue"
            :type="type"
            @input="$emit('update:modelValue', (type === 'number' ? Number($event.target.value):$event.target.value))"
        >
        <ErrorList v-if="errors" :errors="errors"></ErrorList>
    </div>
</template>

<script>
import ErrorList from "./ErrorList";

export default {
    name: "Input",
    components: {
        ErrorList
    },
    model: {
        prop: 'modelValue',
        event: 'update'
    },
    props: {
        label: {
            type: String,
            required: false
        },
        modelValue: {
            required: true
        },
        id: {
            type: String,
            required: true
        },
        type: {
            type: String,
            required: false
        },
        errors: {
            type: Array,
            required: false
        },
        className: {
            type: String,
            required: false,
            default() {
                return 'input-default';
            }
        }
    }
};
</script>

<style lang="scss" scoped>

.input-default {
    width: 31%;
    min-width: 200px;
    align-self: flex-end;
}

label {
    padding: 0 0.8em;
    transform: translateY(0.45em);
    word-break: break-word;
}

input {
    color: white;
    padding: 0.5em 0.8em;
    background: #222b6b;
    border: 1px solid transparentize(white, 0.8);
    outline: none;
    font-size: 1.3em;
}

</style>
