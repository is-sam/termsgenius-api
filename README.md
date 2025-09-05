# TermsGenius API

TermsGenius API is the backend service that powers the TermsGenius platform. It allows users to register, manage projects that contain text documents, and ask questions about those documents through an OpenAI powered chat.

## Tech Stack

- **Language:** PHP 8.1
- **Framework:** [Symfony 6.4](https://symfony.com/)
- **Database:** MariaDB via Doctrine ORM
- **Authentication:** JWT (Lexik JWT Authentication Bundle)
- **AI Integration:** OpenAI Chat Completions API
- **Containerisation:** Docker & Docker Compose

## Prerequisites

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)
- (Optional) [Composer](https://getcomposer.org/) if running locally without Docker

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/your-org/termsgenius-api.git
   cd termsgenius-api
   ```

2. **Configure environment variables**
   ```bash
   cp .env.local.dist .env.local
   # edit .env.local and set DB, JWT_PASSPHRASE, OPENAI_API_KEY and OPENAI_PROMPT values
   ```

3. **Start the containers**
   ```bash
   docker-compose up -d
   ```

4. **Install PHP dependencies**
   ```bash
   docker-compose exec php composer install
   ```

5. **Generate JWT keys**
   ```bash
   docker-compose exec php php bin/console lexik:jwt:generate-keypair
   ```

6. **Run database migrations**
   ```bash
   docker-compose exec php php bin/console doctrine:migrations:migrate
   ```

The API will be available at [http://localhost:8080](http://localhost:8080).

## Development

To run commands inside the PHP container:
```bash
docker-compose exec php bash
```

## License

This project is proprietary.

