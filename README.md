Hyvor Reader ([reader.hyvor.com](https://reader.hyvor.com)) is a free & open-source RSS reader. It supports RSS, Atom,
and JSON feeds. While open-source, it is not meant to be self-hosted as it depends on the HYVOR internal services for
authentication. Developers can run the project locally with mocked authentication.

## Features

- Subscribe to RSS, Atom, and JSON feeds
- Organize feeds into groups
- Easy web-based reader

## Development

Hyvor Reader is built with [Laravel](https://laravel.com) for the backend (API only, no views)
and [SvelteKit](https://kit.svelte.dev/) + [Hyvor Design System](https://github.com/hyvor/design)
for the frontend. If you
wish to contribute, follow the steps below to
set up
the development environment.

> **Note**: You only need to set up the front-end to work on landing pages and documentation pages.

### Prerequisites

- Caddy
- PHP 8.3
- [PHP Extensions required by Laravel](https://laravel.com/docs/10.x/deployment#server-requirements)
- Composer
- PostgreSQL
- Bun.js

### Environment

Copy the `.env.example` file to `.env` and fill in the required environment variables.

```sh
cp .env.example .env
```

### Installation

(Run all the following commands in the project root directory)

Install the dependencies.

```sh
cd frontend && bun install
cd backend && composer install
```

Run the migrations and seed the database.

```sh
cd backend && php artisan migrate --seed
```

Start the development servers.

```
meta/dev/dev
```

Then, visit `http://localhost:13458` in your browser.

>
> `meta/dev/dev` runs the following processes:
>
>- Frontend server (`bun run dev`) at `http://localhost:13456`
>- Backend server (`php artisan serve`) at `http://localhost:13457`
>- Caddy server proxy at `http://localhost:13458`