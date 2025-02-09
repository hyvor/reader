import { writable } from "svelte/store";
import type { Feed } from "./types";

export const feeds = writable<Feed[]>([]);