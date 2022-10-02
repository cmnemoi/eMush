<template>
    <div :class="className">
        <slot name="header"> Hello </slot>
        <slot name="body" />
        <slot name="footer" />
    </div>
</template>

<script>
export default {
    name: "Pannel",
    props: {
        title: {
            type: String,
            // required: true
        },
        content: {
            type: String,
            // required: true
        },
        status: {
            type: String,
            required: false,
            default() {
                return 'current';
            }
        },
        className: {
            type: String,
            required: false,
            default() {
                return 'pannel-default';
            }
        }
    }
};
</script>

<style scoped>
    .pannel-default {
        width: 20%;
        background-color: green;
        border: solid;
    }
</style>