
## Hosting




## Development

Contributions are welcome! Follow the steps below to get started.

### Prerequisites

- PHP 8.2
- [PHP Extensions required by Laravel](https://laravel.com/docs/10.x/deployment#server-requirements)
- Composer
- Node.js 18+
- PostgreSQL
- Redis

### Installation

First, install the dependencies.

```sh
cd frontend && npm install
cd backend && composer install
```

Run the following commands in two separate terminals to start the backend and the frontend.

```sh
cd frontend && npm start
cd backend && composer serve
```

- Frontend: http://localhost:7777
- Backend: http://localhost:7778

Then, visit the front-end URL (http://localhost:7777) in your browser to see the app running with hot-module reloading.