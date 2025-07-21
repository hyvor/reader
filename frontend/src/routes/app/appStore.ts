import { writable } from "svelte/store";
import type { Collection, Publication, Item } from "./types";

export const collections = writable<Collection[]>([]);
export const selectedCollection = writable<Collection | null>(null);
export const publications = writable<Publication[]>([]);
export const selectedPublication = writable<Publication | null>(null);
export const items = writable<Item[]>([]);
export const loadingInit = writable<boolean>(true);
export const loadingPublications = writable<boolean>(true);
export const loadingItems = writable<boolean>(true);
