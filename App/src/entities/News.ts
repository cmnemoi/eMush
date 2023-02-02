export class News {
    public iri: string|null;
    public id: number|null;
    public createdAt: Date|null;
    public updatedAt: Date|null;
    public frenchTitle: string|null;
    public englishTitle: string|null;
    public spanishTitle: string|null;
    public frenchContent: string|null;
    public englishContent: string|null;
    public spanishContent: string|null;

    constructor() {
        this.iri = null;
        this.id = null;
        this.createdAt = null;
        this.updatedAt = null;
        this.frenchTitle = null;
        this.englishTitle = null;
        this.spanishTitle = null;
        this.frenchContent = null;
        this.englishContent = null;
        this.spanishContent = null;
    }
    load(object: any): News {
        if (typeof object !== "undefined") {
            this.iri = object['@id'];
            this.id = object.id;
            this.createdAt = new Date(object.createdAt);
            this.updatedAt = new Date(object.updatedAt);
            console.log('object updatedAt: ' + object.updatedAt);
            console.log('Date updatedAt: ' + this.updatedAt);
            this.frenchTitle = object.frenchTitle;
            this.englishTitle = object.englishTitle;
            this.spanishTitle = object.spanishTitle;
            this.frenchContent = object.frenchContent;
            this.englishContent = object.englishContent;
        }
        
        return this;
    }
    jsonEncode(): object {
        const data : any = {
            'id': this.id,
            'createdAt': this.createdAt,
            'updatedAt': this.updatedAt,
            'frenchTitle': this.frenchTitle,
            'englishTitle': this.englishTitle,
            'spanishTitle': this.spanishTitle,
            'frenchContent': this.frenchContent,
            'englishContent': this.englishContent,
            'spanishContent': this.spanishContent,
        };

        return data;
    }
    decode(jsonString : string): News {
        if (jsonString) {
            const object = JSON.parse(jsonString);
            this.load(object);
        }

        return this;
    }


}