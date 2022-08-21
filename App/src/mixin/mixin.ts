import { formatText } from '@/utils/formatText';

export const mixin = {
    methods: {
        formatContent(value: string): string {
            return !value ? '' : formatText(value.toString());
        }
    }
};