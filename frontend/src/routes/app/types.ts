
export interface Collection {
    id: number;
    uuid: string;
    name: string;
    slug: string;
    is_public: boolean;
    is_owner: boolean;
}

export interface Publication {
    id: number;
    uuid: string;
    title: string;
    url: string;
    description: string;
    subscribers: number;
    created_at: number;
    updated_at: number;
}

export interface Item {
    id: number;
    uuid: string;
    title: string;
    url: string;
    content_html?: string;
    content_text?: string;
    summary?: string;
    image?: string;
    published_at?: number;
    updated_at?: number;
    authors: string[];
    tags: string[];
    language?: string;
    publication_id: number;
    publication_uuid: string;
    publication_title: string;
    reading_time?: number;
    word_count?: number;
}

