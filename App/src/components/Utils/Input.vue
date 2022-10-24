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

<style scoped>

.input-default {
    padding: 5px 5px;
}

input {
    width: 100%;
    padding: 10px 0;
    font-size: 16px;
    color: #fff;
    margin-bottom: 5px;
    border: none;
    border-bottom: 1px solid #fff;
    outline: none;
    background: transparent;
}


</style>
