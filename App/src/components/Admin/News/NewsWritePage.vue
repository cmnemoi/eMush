<template>
    <div v-if="news" class="news_detail">
        <table>
            <tbody>
                <tr>
                    <th>{{ $t('admin.newsWrite.frenchTitle') }}</th>
                    <td><Input v-model="news.frenchTitle" :errors="errors.frenchTitle" /></td>
                </tr>
                <tr>
                    <th>{{ $t('admin.newsWrite.englishTitle') }}</th>
                    <td><Input v-model="news.englishTitle" :errors="errors.englishTitle"/></td>
                </tr>
                <tr>
                    <th>{{ $t('admin.newsWrite.spanishTitle') }}</th>
                    <td><Input v-model="news.spanishTitle" :errors="errors.spanishTitle"/></td>
                </tr>
                <tr>
                    <th>{{ $t('admin.newsWrite.frenchContent') }}</th>
                    <td><textarea v-model="news.frenchContent" /></td>
                </tr>
                <tr>
                    <th>{{ $t('admin.newsWrite.englishContent') }}</th>
                    <td><textarea v-model="news.englishContent" /></td>
                </tr>
                <tr>
                    <th>{{ $t('admin.newsWrite.spanishContent') }}</th>
                    <td><textarea v-model="news.spanishContent" /></td>
                </tr>
                <tr v-if="!news.id">
                    <th><button
                        class="action-button"
                        type="button"
                        @click="newPoll">
                        {{ $t('admin.newsWrite.newPoll') }}
                    </button></th>
                    <td><input v-model="pollTitle" maxlength="250"/></td>
                </tr>

                <tr v-for="option in pollOptions" :key="option.id">
                    <th><button
                        class="action-button"
                        type="button"
                        @click="removePollOption(option)">
                        {{ $t('admin.newsWrite.removePollOption') }}
                    </button></th>
                    <td><input v-model="option.name" maxlength="150"/></td>
                </tr>

                <tr v-if="poll">
                    <th><button
                        class="action-button"
                        type="button"
                        @click="newPollOption">
                        {{ $t('admin.newsWrite.newPollOption') }}
                    </button></th>
                    <td>
                        {{ $t('admin.newsWrite.maxVotes') }}
                        <input type="number" min="1" v-model="maxVotes">
                    </td>
                </tr>

            </tbody>
        </table>
        <div class="flex-row wrap">
            <div class="checkbox-container">
                <input
                    type="checkbox"
                    id="news_is_pinned"
                    v-model="news.isPinned"
                />
                <label for="news_is_pinned">{{ $t('admin.newsWrite.isPinned') }}</label>
            </div>
            <div>
                <Input
                    type="date"
                    v-model="news.publicationDate"
                />
                <label for="news_is_pinned">{{ $t('admin.newsWrite.publicationDate') }}</label>
            </div>
        </div>
        <button
            class="action-button"
            :disabled="!news.frenchTitle || !news.frenchContent"
            type="submit"
            @click="update">
            {{ $t('admin.save') }}
        </button>
    </div>
</template>

<script lang="ts">
import { defineComponent } from "vue";
import Input from "@/components/Utils/Input.vue";
import NewsService from "@/services/news.service";
import { News } from "@/entities/News";
import { handleErrors } from "@/utils/apiValidationErrors";
import { mapActions } from "vuex";
import UserService from "@/services/user.service";
import { Poll } from "@/entities/Poll";

interface PollOption {
    id : number,
    name : string
}

interface NewsData {
    news: News | null,
    errors: any,
    pollTitle : string,
    pollOptions : Array<PollOption>,
    poll : boolean,
    lastOptionId : number,
    maxVotes : number

}

export default defineComponent({
    name: "NewsWritePage",
    components: {
        Input
    },
    data() : NewsData {
        return {
            news: null,
            errors: {},
            pollTitle : "",
            pollOptions : [],
            poll : false,
            lastOptionId : 0,
            maxVotes : 1
        };
    },
    methods: {
        async create(): Promise<void> {
            if(!this.news) {
                console.error("News is null");
                return;
            }

            if (this.poll === true)
            {
                await this.createPoll()
                    .then((result) => {
                        this.news.poll = result;
                    })
                    .catch((error: any) => {
                        this.errors = handleErrors(error);
                    });
            }


            await NewsService.createNews(this.news)
                .then((result: News | null) => {
                    this.news = result;
                    this.errors = {};
                })
                .catch((error: any) => {
                    this.errors = handleErrors(error);
                });
        },
        update(): void {
            if(!this.news) {
                console.error("News is null");
                return;
            }
            if (!this.news.id) {
                this.create();
                return;
            }
            NewsService.updateNews(this.news)
                .then((result: News | null) => {
                    this.news = result;
                    this.errors = {};
                })
                .catch((error: any) => {
                    this.errors = handleErrors(error);
                });
        },
        newPoll(): void {
            this.poll = true;
        },
        newPollOption(): void {
            this.lastOptionId += 1;
            const o : PollOption = { id : this.lastOptionId, name : '' };

            this.pollOptions.push(o);
        },
        removePollOption(option : any): void {
            const optionIndex = this.pollOptions.indexOf(option);
            if (optionIndex !== -1)
            {
                this.pollOptions.splice(optionIndex, 1);
            }
        },
        async createPoll(): Promise<Poll> {
            const data : Array<string> = this.pollOptions.map((o : PollOption) => o.name);


            return await UserService.createPoll(this.pollTitle, this.maxVotes, data);

        }
    },
    beforeMount() {
        const newsId = String(this.$route.params.newsId);
        if(newsId === 'undefined') {
            this.news = new News();
            return;
        }
        NewsService.getNewsById(Number(newsId))
            .then((result: News | null) => {
                this.news = result;
            })
            .catch((error: any) => {
                this.errors = handleErrors(error);
            });

    }
});
</script>

<style lang="scss" scoped>

table {
    background: #222b6b;
    // border-radius: 5px;
    border-collapse: collapse;
    border: thin solid #1B2256;
    margin-bottom: 1%;

    tbody tr {
        border-top: 1px solid rgba(0,0,0,0.2);
    }

    textarea {
        background: transparent;
        border: thin solid rgba(255, 255, 255, .25);
        color: #fff;
        line-height: 1.4em;
        padding: 0.4em;
        width: 100%;
        height: 16em;
        resize: vertical;
    }

    th, td {
        padding: 1em 0.5em 1em 1.2em;
        vertical-align: middle;
        :deep(a), :deep(button) {
            @include button-style();

            & {
                width: fit-content;
                padding: 2px 15px 4px;
            }
        }
    }

    th {
        // opacity: .75;
        letter-spacing: .05em;
        text-align: left;
        font-weight: bold;
        width: 20%;
    }

}
</style>
