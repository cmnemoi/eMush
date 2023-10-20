<template>
    <div :class="className">
        <div class="header">
            <slot name="header"> Hello </slot>
        </div>
        <slot name="body" />
        <slot name="footer" />
    </div>
</template>

<script>
export default {
    name: "Pannel",
    props: {
        title: {
            type: String
            // required: true
        },
        content: {
            type: String
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

<style lang="scss" scoped>
.pannel-default {
    width: 31%;
    min-width: 200px;
    position: relative;
    background: #222b6b;
    border: 1px solid transparentize(white, 0.8);
    border-left-width: 4px;

    .header {
        flex-direction: row;

        &::v-deep span {
            padding: .6em .8em;
            // background: #222b6b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: help;
        }
    }
    
    &::v-deep {
        span {
            flex: 1;
            padding: .2em .8em .2em 1.6em;
            line-height: 1.2em;

            /* &:hover, &:focus {
                overflow: visible;
                z-index: 5;
                background: transparentize(#222b6b, 0.4);
                padding-right: 0.8em;
            } */
        }

        & > span:last-of-type { margin-bottom: .6em; }
    }
}

</style>
