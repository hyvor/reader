Hyvor Reader ([reader.hyvor.com](https://reader.hyvor.com)) is a free & open-source RSS reader. It supports RSS, Atom,
and JSON feeds.

<p align="center">
  <a href="https://reader.hyvor.com">
    <img src="https://hyvor.com/img/logo.png" alt="Hyvor Reader Logo" width="130"/>
  </a>
</p>

<p align="center">
  <a href="https://reader.hyvor.com">
    Free RSS Reader
  </a>
    <span> | </span>
    <a href="https://reader.hyvor.com/docs">
    Docs
  </a>
  <span> | </span>
    <a href="https://reader.hyvor.com/learn">
    Learn RSS
  </a>
</p>

## Features

- Subscribe to RSS, Atom, and JSON feeds
- Organize feeds into groups
- Easy web-based reader

## Architecture

- **PHP + Symfony** for the API backend.
- **SvelteKit** and [**Hyvor Design System**](https://github.com/hyvor/design) for the frontend.
- **PGSQL** as the database.

## Contributing

Visit [hyvor/dev](https://github.com/hyvor/dev) to set up the HYVOR development environment. Then, run `./run reader` to start Hyvor Reader at `https://reader.hyvor.localhost`.

Directory structure:

- `/backend`: Symfony API backend
- `/frontend`: SvelteKit frontend

## License

Hyvor Reader is licensed under the [AGPL-3.0 License](https://github.com/hyvor/reader/blob/main/LICENSE). For use cases that cannot comply with AGPLv3, contact HYVOR for an [Enterprise License](https://hyvor.com/enterprise).

![HYVOR Banner](https://raw.githubusercontent.com/hyvor/relay/refs/heads/main/meta/assets/hyvor-banner.svg)

Copyright © HYVOR. HYVOR name and logo are trademarks of HYVOR, SARL.