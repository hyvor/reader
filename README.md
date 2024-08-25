Hyvor Reader ([reader.hyvor.com](https://reader.hyvor.com)) is a free & open-source RSS reader. It supports RSS, Atom,
and JSON feeds. While open-source, it is not meant to be self-hosted as it depends on the HYVOR internal services for
authentication. Developers can run the project locally with mocked authentication.

## Features

- Subscribe to RSS, Atom, and JSON feeds
- Organize feeds into groups
- Easy web-based reader

## Development

Hyvor Reader is built with [Laravel](https://laravel.com) for the backend (API only, no views)
and [Svelte](https://svelte.dev) for the frontend. If you like to contribute, follow the steps below to set up
the development environment.

> **Note**: You only need to set up the front-end to work on landing pages and documentation pages.

### Prerequisites

For backend:

- PHP 8.3
- [PHP Extensions required by Laravel](https://laravel.com/docs/10.x/deployment#server-requirements)
- Composer
- PostgreSQL

For frontend:

- Bun.js

### Environment

Copy the `.env.example` file to `.env` and fill in the required environment variables.

```sh
cp .env.example .env
```

### Installation

First, install the dependencies.

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