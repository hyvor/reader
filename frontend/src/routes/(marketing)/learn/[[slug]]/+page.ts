import Index from "./pages/Index.svelte";
import { error } from '@sveltejs/kit';
import RssFormat from "./pages/RssFormat.svelte";
import AtomFormat from "./pages/AtomFormat.svelte";

export const prerender = true;

const nav = {
    index: Index,
    rss: RssFormat,
    atom: AtomFormat,
}

export async function load({ params }) {

    const slug = params.slug;
    const fileName = (slug || 'index') as keyof typeof nav;

    /* if (!nav[fileName]) {
        throw error(404, 'Not found');
    } */

    return {
        slug: params.slug,
        content: nav[fileName],
    }
}