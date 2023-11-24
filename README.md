# Layer Architecture POC

This is a proof of concept for a layered architecture using Docker Compose.

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --pull --no-cache` to build fresh images
3. Run `docker compose up` (the logs will be displayed in the current shell)
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Docs

1. [Build options](docs/build.md)
2. [Deploying in production](docs/production.md)
3. [Using a Makefile](docs/makefile.md)

## Services

In my service folder, I have 3 services, each one represent a layer of my architecture.
