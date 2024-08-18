import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import store from "@/store";
import { News } from "@/entities/News";
import qs from "qs";

// @ts-ignore
const NEWS_ENDPOINT = urlJoin(import.meta.env.VITE_APP_API_URL, "news");

const NewsService = {
    createNews: async(news: News): Promise<News | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });

        const newsData = await ApiService.post(NEWS_ENDPOINT, news.toRecord())
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let createdNews = null;
        if (newsData.data) {
            createdNews = (new News()).load(newsData.data);
        }

        return createdNews;

    },
    getNewsById: async(newsId: number): Promise<News | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });

        const newsData = await ApiService.get(NEWS_ENDPOINT + '/' + newsId)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let news = null;
        if (newsData.data) {
            news = (new News()).load(newsData.data);
        }

        return news;
    },
    updateNews: async(news: News): Promise<News | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });

        const newsData = await ApiService.put(NEWS_ENDPOINT + '/' + news.id, news)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let updatedNews = null;
        if (newsData.data) {
            updatedNews = (new News()).load(newsData.data);
        }

        return updatedNews;
    },
    getAllNews: async (): Promise<News[]> => {
        let news: News[] = [];
        store.dispatch('gameConfig/setLoading', { loading: true });

        await ApiService.get(NEWS_ENDPOINT).then((response) => {
            news = response.data['hydra:member'].map((newsData: Record<string, any>) => {
                return (new News()).load(newsData);
            });
        }).finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        return news;

    },
    getLastPinnedNews: async (): Promise<News[]> => {
        let news: News[] = [];
        store.dispatch('gameConfig/setLoading', { loading: true });

        const params: any = {
            header: {
                'accept': 'application/ld+json'
            },
            params: { },
            paramsSerializer: qs.stringify
        };

        params.params['news.isPinned'] = true;
        params.params['news.isPublished'] = true;
        qs.stringify(params.params['order'] = { ['news.publicationDate']: 'ASC' });

        await ApiService.get(NEWS_ENDPOINT, { params }).then((response) => {
            news = response.data['hydra:member'].map((newsData: Record<string, any>) => {
                return (new News()).load(newsData);
            });
        }).finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        return news;
    }
};
export default NewsService;
