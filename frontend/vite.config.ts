import { sveltekit } from '@sveltejs/kit/vite';
import { defineConfig } from 'vitest/config';

export default defineConfig({
	plugins: [sveltekit()],
	test: {
		include: ['src/**/*.{test,spec}.{js,ts}']
	},
	server: {
		port: 13456,
		strictPort: true,
	},
	build: {
		rollupOptions: {
			output: {
				manualChunks: () => {
					// to reduce the number of chunks
					// this will still create other chunks
					// currently, there's no way to just have app.js
					// https://github.com/sveltejs/kit/issues/3882#issuecomment-2294459036
					return 'app';
				}
			}
		}
	}
});
