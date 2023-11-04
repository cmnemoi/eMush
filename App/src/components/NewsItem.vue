<template>
    <section :class="'section ' + (news.hidden ? 'hidden' : '')">
        <div class="title" v-if="localeIsFrench()" @click="$emit('click')">
            <img class="news-cover" src="@/assets/images/mush-cover.png">
            <h2>{{ news.frenchTitle }}</h2>
            <p><img class="flag" src="@/assets/images/lang_fr.png" alt="🇫🇷"> {{ $t('newsPage.updatedAt') }} {{ formatDate(news.updatedAt) }}</p>
        </div>
        <div class="content" v-if="localeIsFrench()">
            <p v-html="formatNewsContent(news.frenchContent)" />
        </div>
        <div class="title" v-if="localeIsEnglish()" @click="$emit('click')">
            <img class="news-cover" src="@/assets/images/mush-cover.png">
            <h2>{{ news.englishTitle }}</h2>
            <p><img class="flag" src="@/assets/images/lang_en.png" alt="🇬🇧"> {{ $t('newsPage.updatedAt') }} {{ formatDate(news.updatedAt) }}</p>
        </div>
        <div class="content" v-if="localeIsEnglish()">
            <p v-html="formatNewsContent(news.englishContent)" />
        </div>
    </section>
</template>

<script lang="ts">
import { News } from "@/entities/News";
import { GameLocales } from "@/i18n";
import { formatText } from "@/utils/formatText";
import { defineComponent } from "vue";

export default defineComponent ({
    name: 'NewsItem',
    props: {
        news: {
            type: News,
            required: true
        }
    },
    emits: ['click'],
    methods: {
        localeIsFrench() {
            return this.$i18n.locale.split('-')[0] === GameLocales.FR;
        },
        localeIsEnglish() {
            return this.$i18n.locale.split('-')[0] === GameLocales.EN;
        },
        formatDate(date: Date | null) {
            return date ? date.toLocaleDateString(this.$i18n.locale) : '';
        },
        formatNewsContent(content: string | null) {
            return content ? formatText(content) : '';
        },
    }
});
</script>

<style scoped lang="scss">

.section {
    border: 1px solid #576077;
    background-color: #222b6b;
    box-shadow: 0px 4px 4px rgba(0, 0, 0, 0.4);
    padding: 1.2em;
    margin: .4em;

    img {
        width: fit-content;
        height: fit-content;
    }

    p { line-height: 1.4em; }

    a { color: #D24781; }

    &:not(:first-child) .title { cursor: pointer; } // for hidden news interaction cue
}

.title {
    display: block;

    img.news-cover {
        float:left;
        width: 48px;
        margin-right: 1.2em;
    }

    h2, p {
        margin: 0.1em;
    }

    h2 {
        font-size: 2.2rem;
        color: #D24781;
    }

    p {
        opacity: 0.85;
        font-size: 0.8em;
        letter-spacing: 0.03em;
    }

    .flag {
        vertical-align: baseline;
        padding-right: 0.4em;
    }
}

.hidden {
    opacity: 0.75;

    .content p { display: none; }
}

</style>