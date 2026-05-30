# Symfony GitHub PHP Projects Dashboard

A Symfony application that fetches, stores, and displays the most-starred public PHP projects from GitHub, presented in a modern dark terminal-inspired UI.

## Features

- **Dashboard** — Browse the top PHP repositories sorted by star count
- **Detail View** — Click any project to see description, stars, creation date, last push date, and repository link
- **Pagination** — Navigate through projects 10 per page
- **Live Refresh** — Fetch fresh data from GitHub API with an animated loading indicator
- **Dark Terminal UI** — JetBrains Mono + DM Sans, amber accents, dot-grid background

## Tech Stack

- Symfony 7.4
- PHP 8.3
- MariaDB 11.5
- Docker & Docker Compose
- Hotwire Turbo (SPA-like navigation)
- Stimulus (client-side loading state)
- Pagerfanta (pagination)

## Prerequisites

- Docker Desktop (or Docker + Docker Compose)

## Setup

### 1. Configure ports (optional)

By default, the web server runs on port `8080` and the database on port `8306`. To override:

```sh
cp compose.override.yaml.dist compose.override.yaml
```

Edit `compose.override.yaml` to change ports.

### 2. Start Docker containers

```sh
docker compose up -d
```

### 3. Install Composer dependencies

```sh
docker compose exec web composer install
```

### 4. Run database migrations

```sh
docker compose exec web bin/console doctrine:migrations:migrate
```

### 5. Access the app

Open **http://127.0.0.1:8080** in your browser.

## Usage

### Viewing Projects

The homepage displays the most-starred PHP projects. Each row shows the repository name and star count.

### Pagination

Use **Previous** / **Next** or numbered page links to navigate. 10 projects per page.

### Project Details

Click any project to open its detail page showing:
- Description
- Star count
- Creation date
- Last push date
- Direct link to GitHub

### Refreshing Data from GitHub

Click the **Refresh from GitHub** button to fetch the latest top PHP repositories from the GitHub API. The button shows a spinning indicator and "Fetching..." label while the request is in progress. The project list updates automatically when the sync completes.

> **Note:** The GitHub Search API allows 60 unauthenticated requests per hour. If you hit the rate limit, add a `GITHUB_TOKEN` to your `.env.local` file.

### Database container

This project uses the MariaDB Docker image for the database, which should already be configured properly in the Symfony application in the `.env` file.

The official MariaDB container does not include the `mysql` command line client. If you want to connect to the database from your host machine, you will need a MySQL client installed locally. The root user account is configured to work without a password.

Host: `127.0.0.1`  
Port: `8306` (or the port you specified in `composer.override.yaml`)  
Username: `root`  
Database: `app`

For example, using the `mysql` command line client, you would connect with:

```sh
mysql -uroot -h127.0.0.1 -P8306 app
```

## Database Connection

| Setting  | Value              |
|----------|--------------------|
| Host     | `127.0.0.1`        |
| Port     | `8306`             |
| User     | `root`             |
| Password | *(none)*           |
| Database | `app`              |

Connect from your host machine:

```sh
mysql -uroot -h127.0.0.1 -P8306 app
```

## Common Commands

```sh
# Clear Symfony cache
docker compose exec web bin/console cache:clear

# Run PHPUnit tests
docker compose exec web vendor/bin/phpunit

# Open a shell inside the web container
docker compose exec web bash

# Run a single test
docker compose exec web vendor/bin/phpunit --filter TestClassName
```

## Project Structure

```
src/
├── Controller/
│   ├── DefaultController.php          # Redirects / to /github-projects
│   └── GithubPhpProjectController.php # Index, detail, and refresh routes
├── Entity/
│   └── GithubPhpProject.php           # Doctrine entity for github_php_projects
├── Repository/
│   └── GithubPhpProjectRepository.php # Database queries
└── Service/
    └── GithubApiService.php           # GitHub API client and sync logic

templates/github_php_project/
├── index.html.twig                    # Project list with pagination
├── detail.html.twig                   # Single project detail view
└── refresh.stream.html.twig           # Turbo Stream response for refresh

assets/
├── controllers/
│   └── refresh_controller.js          # Stimulus controller for loading state
└── styles/
    └── app.css                        # Dark terminal theme
```

## Cleanup

```sh
# Stop containers
docker compose stop

# Stop and remove containers
docker compose down

# Stop, remove containers, and delete images and volumes
docker compose down --volumes --rmi all
```
## Running PHPUnit Tests

To run all the tests:

```sh
docker compose exec web vendor/bin/phpunit
```
For a single test file:

```sh
docker compose exec web vendor/bin/phpunit tests/Controller/GithubPhpProjectControllerTest.php
```
For a single test method:

```sh
docker compose exec web vendor/bin/phpunit --filter testIndexPageIsSuccessful
```
