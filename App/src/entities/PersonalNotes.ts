export class PersonalNotesTab {
    public id: number | null;
    public index: number;
    public icon: string | null;
    public content: string;

    constructor(id: number | null, index: number, icon: string | null, content: string) {
        this.id = id;
        this.index = index;
        this.icon = icon;
        this.content = content;
    }

    public static load(object: any): PersonalNotesTab {
        return new PersonalNotesTab(object.id, object.index, object.icon, object.content);
    }
}

export class PersonalNotes {
    public hasAccess: boolean = false;
    public tabs: PersonalNotesTab[] = [];

    constructor(hasAccess: boolean, tabs: PersonalNotesTab[]) {
        this.hasAccess = hasAccess;
        this.tabs = tabs;
    }

    public static load(object: any): PersonalNotes {
        const tabs = object.tabs
            .map((tab: any) => PersonalNotesTab.load(tab))
            .sort((a: PersonalNotesTab, b: PersonalNotesTab) => a.index - b.index);
        tabs.forEach((tab: PersonalNotesTab, i: number) => tab.index = i);
        return new PersonalNotes(object.hasAccess, tabs);
    }
}
