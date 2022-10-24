<template>
    <ul :class="containerClass" v-if="!noLiSurround">
        <li v-if="firstLastButton" :class="[pageClass, firstPageSelected() ? disabledClass : '']">
            <a
                @click="selectFirstPage()"
                @keyup.enter="selectFirstPage()"
                :class="pageLinkClass"
                :tabindex="firstPageSelected() ? -1 : 0"
                v-html="firstButtonText"
            ></a>
        </li>

        <li
            v-if="!(firstPageSelected() && hidePrevNext)"
            :class="[prevClass, firstPageSelected() ? disabledClass : '']"
        >
            <a
                @click="prevPage()"
                @keyup.enter="prevPage()"
                :class="prevLinkClass"
                :tabindex="firstPageSelected() ? -1 : 0"
                v-html="prevText"
            ></a>
        </li>

        <li
            v-for="page in pages"
            :key="page.index"
            :class="[pageClass, page.selected ? activeClass : '', page.disabled ? disabledClass : '', page.breakView ? breakViewClass : '']"
        >
            <a v-if="page.breakView" :class="[pageLinkClass, breakViewLinkClass]" tabindex="0">
                <slot name="breakViewContent">{{ breakViewText }}</slot>
            </a>
            <a v-else-if="page.disabled" :class="pageLinkClass" tabindex="0">{{ page.content }}</a>
            <a
                v-else
                @click="handlePageSelected(page.index + 1)"
                @keyup.enter="handlePageSelected(page.index + 1)"
                :class="pageLinkClass"
                tabindex="0"
            >{{ page.content }}</a>
        </li>

        <li
            v-if="!(lastPageSelected() && hidePrevNext)"
            :class="[nextClass, lastPageSelected() ? disabledClass : '']"
        >
            <a
                @click="nextPage()"
                @keyup.enter="nextPage()"
                :class="nextLinkClass"
                :tabindex="lastPageSelected() ? -1 : 0"
                v-html="nextText"
            ></a>
        </li>

        <li v-if="firstLastButton" :class="[pageClass, lastPageSelected() ? disabledClass : '']">
            <a
                @click="selectLastPage()"
                @keyup.enter="selectLastPage()"
                :class="pageLinkClass"
                :tabindex="lastPageSelected() ? -1 : 0"
                v-html="lastButtonText"
            ></a>
        </li>
    </ul>

    <div :class="containerClass" v-else>
        <a
            v-if="firstLastButton"
            @click="selectFirstPage()"
            @keyup.enter="selectFirstPage()"
            :class="[pageLinkClass, firstPageSelected() ? disabledClass : '']"
            tabindex="0"
            v-html="firstButtonText"
        ></a>
        <a
            v-if="!(firstPageSelected() && hidePrevNext)"
            @click="prevPage()"
            @keyup.enter="prevPage()"
            :class="[prevLinkClass, firstPageSelected() ? disabledClass : '']"
            tabindex="0"
            v-html="prevText"
        ></a>
        <template v-for="page in pages">
            <a
                v-if="page.breakView"
                :key="page.index"
                :class="[pageLinkClass, breakViewLinkClass, page.disabled ? disabledClass : '']"
                tabindex="0"
            >
                <slot name="breakViewContent">{{ breakViewText }}</slot>
            </a>
            <a
                v-else-if="page.disabled"
                :key="page.index"
                :class="[pageLinkClass, page.selected ? activeClass : '', disabledClass]"
                tabindex="0"
            >{{ page.content }}</a>
            <a
                v-else
                :key="page.index"
                @click="handlePageSelected(page.index + 1)"
                @keyup.enter="handlePageSelected(page.index + 1)"
                :class="[pageLinkClass, page.selected ? activeClass : '']"
                tabindex="0"
            >{{ page.content }}</a>
        </template>
        <a
            v-if="!(lastPageSelected() && hidePrevNext)"
            @click="nextPage()"
            @keyup.enter="nextPage()"
            :class="[nextLinkClass, lastPageSelected() ? disabledClass : '']"
            tabindex="0"
            v-html="nextText"
        ></a>
        <a
            v-if="firstLastButton"
            @click="selectLastPage()"
            @keyup.enter="selectLastPage()"
            :class="[pageLinkClass, lastPageSelected() ? disabledClass : '']"
            tabindex="0"
            v-html="lastButtonText"
        ></a>
    </div>
</template>

<script>
import { defineComponent } from "vue";
// Credit to: https://github.com/cloudeep/vuejs-paginate-next

export default defineComponent ({
    name: "Pagination",
    data() {
        return {
            innerValue: 1
        };
    },
    props: {
        modelValue: {
            type: Number
        },
        pageCount: {
            type: Number,
            required: true
        },
        initialPage: {
            type: Number,
            default: 1
        },
        forcePage: {
            type: Number
        },
        clickHandler: {
            type: Function,
            default: () => { /* do nothing */ }
        },
        pageRange: {
            type: Number,
            default: 3
        },
        marginPages: {
            type: Number,
            default: 1
        },
        prevText: {
            type: String,
            default: 'Prev'
        },
        nextText: {
            type: String,
            default: 'Next'
        },
        breakViewText: {
            type: String,
            default: '…'
        },
        containerClass: {
            type: String,
            default: 'pagination'
        },
        pageClass: {
            type: String,
            default: 'page-item'
        },
        pageLinkClass: {
            type: String,
            default: 'page-link'
        },
        prevClass: {
            type: String,
            default: 'page-item'
        },
        prevLinkClass: {
            type: String,
            default: 'page-link'
        },
        nextClass: {
            type: String,
            default: 'page-item'
        },
        nextLinkClass: {
            type: String,
            default: 'page-link'
        },
        breakViewClass: {
            type: String
        },
        breakViewLinkClass: {
            type: String
        },
        activeClass: {
            type: String,
            default: 'active'
        },
        disabledClass: {
            type: String,
            default: 'disabled'
        },
        noLiSurround: {
            type: Boolean,
            default: false
        },
        firstLastButton: {
            type: Boolean,
            default: false
        },
        firstButtonText: {
            type: String,
            default: 'First'
        },
        lastButtonText: {
            type: String,
            default: 'Last'
        },
        hidePrevNext: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        selected: {
            get: function () {
                return this.modelValue || this.innerValue;
            },
            set: function (newValue) {
                this.innerValue = newValue;
            }
        },
        pages: function () {
            const items = {};
            if (this.pageCount <= this.pageRange) {
                for (let index = 0; index < this.pageCount; index++) {
                    const page = {
                        index: index,
                        content: index + 1,
                        selected: index === (this.selected - 1)
                    };
                    items[index] = page;
                }
            } else {
                const halfPageRange = Math.floor(this.pageRange / 2);
                const setPageItem = index => {
                    const page = {
                        index: index,
                        content: index + 1,
                        selected: index === (this.selected - 1)
                    };
                    items[index] = page;
                };
                const setBreakView = index => {
                    const breakView = {
                        disabled: true,
                        breakView: true
                    };
                    items[index] = breakView;
                };
                // 1st - loop thru low end of margin pages
                for (let i = 0; i < this.marginPages; i++) {
                    setPageItem(i);
                }
                // 2nd - loop thru selected range
                let selectedRangeLow = 0;
                if (this.selected - halfPageRange > 0) {
                    selectedRangeLow = this.selected - 1 - halfPageRange;
                }
                let selectedRangeHigh = selectedRangeLow + this.pageRange - 1;
                if (selectedRangeHigh >= this.pageCount) {
                    selectedRangeHigh = this.pageCount - 1;
                    selectedRangeLow = selectedRangeHigh - this.pageRange + 1;
                }
                for (let i = selectedRangeLow; i <= selectedRangeHigh && i <= this.pageCount - 1; i++) {
                    setPageItem(i);
                }
                // Check if there is breakView in the left of selected range
                if (selectedRangeLow > this.marginPages) {
                    setBreakView(selectedRangeLow - 1);
                }
                // Check if there is breakView in the right of selected range
                if (selectedRangeHigh + 1 < this.pageCount - this.marginPages) {
                    setBreakView(selectedRangeHigh + 1);
                }
                // 3rd - loop thru high end of margin pages
                for (let i = this.pageCount - 1; i >= this.pageCount - this.marginPages; i--) {
                    setPageItem(i);
                }
            }
            return items;
        }
    },
    methods: {
        handlePageSelected(selected) {
            if (this.selected === selected) return;
            this.innerValue = selected;
            this.$emit('update:modelValue', selected);
            this.clickHandler(selected);
        },
        prevPage() {
            if (this.selected <= 1) return;
            this.handlePageSelected(this.selected - 1);
        },
        nextPage() {
            if (this.selected >= this.pageCount) return;
            this.handlePageSelected(this.selected + 1);
        },
        firstPageSelected() {
            return this.selected === 1;
        },
        lastPageSelected() {
            return (this.selected === this.pageCount) || (this.pageCount === 0);
        },
        selectFirstPage() {
            if (this.selected <= 1) return;
            this.handlePageSelected(1);
        },
        selectLastPage() {
            if (this.selected >= this.pageCount) return;
            this.handlePageSelected(this.pageCount);
        }
    },
    beforeMount() {
        this.innerValue = this.initialPage;
    },
    beforeUpdate() {
        if (this.forcePage === undefined) return;
        if (this.forcePage !== this.selected) {
            this.selected = this.forcePage;
        }
    }
});
</script>

<style lang="scss" scoped>

$color: #222b6b;
$border-color: #3d4dbf;
$border-radius: .3rem;

.page-link {
    display: flex;
    padding: 4px 14px 6px;
    cursor: pointer;
    background-color: $color;
    border-top: 1px solid $border-color;
    outline: 1px solid #1D2028;
}

.page-item:not(.disabled):not(.active) .page-link {
    &:active,
    &:hover {
        background-color: lighten($color, 15%);
        border-top-color: lighten($border-color, 15%);
        z-index: 2;
    }
}

.page-item.disabled .page-link {
    opacity: .6;
    cursor: default;
}

.page-item.active .page-link {
    background-color: lighten($color, 30%);
    border-top-color: lighten($border-color, 20%);
    font-weight: bold;
}

.page-item:first-child .page-link {
    border-top-left-radius: $border-radius;
    border-bottom-left-radius: $border-radius;
}

.page-item:last-child .page-link {
    border-top-right-radius: $border-radius;
    border-bottom-right-radius: $border-radius;
}

</style>
