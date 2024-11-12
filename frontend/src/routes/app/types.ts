
export interface Feed {
    id: number;
    created_at: number;
    url: string;
    title: string;
    description: string;
    subscribers: number;
}

export interface FeedItem {
    // TODO: Complete
    id: number;
    title: string;
    url: string;
    [key: string]: unknown;
}