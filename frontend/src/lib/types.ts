export interface Feed {
    name: string,
    icon_url: string
}

export interface FeedEntry {
    url: string,
    title: string,
    description: string,
    thumbnail_url: string,
    published_at: Date
}