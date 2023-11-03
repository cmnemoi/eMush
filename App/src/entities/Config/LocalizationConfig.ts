export class LocalizationConfig {
    public name: string | null;
    public timeZone: string|null;
    public language: string|null;

    constructor() {
        this.name = null;
        this.timeZone = null;
        this.language = null;
    }
    load(object:any) : LocalizationConfig {
        if (typeof object !== "undefined") {
            this.name = object.name;
            this.timeZone = object.timeZone;
            this.language = object.language;
        }
        return this;
    }
    jsonEncode() : object {
        return {
            'name': this.name,
            'timeZone': this.timeZone,
            'language': this.language
        };
    }
    decode(jsonString : string): LocalizationConfig {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.name = object.name;
            this.timeZone = object.timeZone;
            this.language = object.language;
        }

        return this;
    }
}
