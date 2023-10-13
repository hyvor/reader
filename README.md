Hyvor Reader is a self-hosted RSS reader. The hosted version is available at [reader.hyvor.com](https://reader.hyvor.com).

## Authentication

Hyvor Reader does not have a built-in authentication system. Instead, it uses an OpenID Connect (OIDC) provider to authenticate users. Our hosted version (reader.hyvor.com) uses hyvor.com login for this case. However, hyvor.com OIDC is not open for public. So, you need to use your own OIDC provider for [hosting](#hosting) and [development](#development).

Here are some OIDC providers you can use, if your organization does not already have one:

* [Auth0](https://auth0.com) (free plan)
* [Google Identity Platform](https://cloud.google.com/identity-platform) (free plan)
* [FusionAuth](https://fusionauth.io/) (self-hosted)
* [Keycloak](https://www.keycloak.org/) (self-hosted)

Then, add the OIDC provider's configuration to the `.env` file.

```env
OIDC_ISSUER=https://your-oidc-provider.com
OIDC_CLIENT_ID=your-client-id
OIDC_CLIENT_SECRET=your-client-secret
```

## Hosting

To be written.

## Development

Hyvor Reader is built with [Laravel](https://laravel.com) for the backend (API only, no views) and [Svelte](https://svelte.dev) for the frontend. If you like to contribute, you can follow the steps below to set up the development environment.

> **Note**: If you just want to contribute to the documentation or landing pages, you can simply set up the frontend only.

### Prerequisites

- PHP 8.2
- [PHP Extensions required by Laravel](https://laravel.com/docs/10.x/deployment#server-requirements)
- Composer
- Node.js 18+
- PostgreSQL
- Redis
- An OpenID Connect provider

### Environment

Copy the `.env.example` file to `.env` and fill in the required environment variables.

```sh
cp .env.example .env
```



### Installation

First, install the dependencies.

```sh
cd frontend && npm install
cd backend && composer install
```

Start the frontend Vite dev server (http://localhost:7777):

```sh
cd frontend && npm start
```

Start the backend Laravel server (http://localhost:7778) in a separate terminal:

```sh
cd backend && composer serve
```

Then, visit the front-end URL (http://localhost:7777) in your browser.