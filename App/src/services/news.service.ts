import ApiService from "@/services/api.service";
import urlJoin from "url-join";
import store from "@/store";
import { News } from "@/entities/News";

// @ts-ignore
const NEWS_ENDPOINT = urlJoin(process.env.VUE_APP_API_URL, "news");

const NewsService = {
    createNews: async(news: News): Promise<News | null> => {
        store.dispatch('gameConfig/setLoading', { loading: true });

        const newsRecord: Record<string, any> = news.jsonEncode();

        const newsData = await ApiService.post(NEWS_ENDPOINT, newsRecord)
            .finally(() => (store.dispatch('gameConfig/setLoading', { loading: false })));

        let createdNews = null;
        if (newsData.data) {
            createdNews = (new News()).load(newsData.data);
        }

        return createdNews;

    },
    loadNews: async(newsId: number): Promise<News | null> => {
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
    }
};
export default NewsService;
