# Laravel Sail Application

This project is a Laravel application using Dockerized development with Laravel Sail.

---

## Requirements

Before getting started, ensure you have the following installed:

1. [Docker](https://www.docker.com/)
2. [Docker Compose](https://docs.docker.com/compose/install/) (if not included with Docker)
3. [Git](https://git-scm.com/)

---

## Installation

Follow these steps to set up the application:

### 1. Clone the Repository

```bash
git clone https://github.com/<your-username>/<your-repo-name>.git
cd <your-repo-name>
```

### 2. Set Up Environment File

Copy the example `.env` file and configure it as needed:

```bash
cp .env.example .env
```

Ensure your `.env` file has the correct configuration for the local environment.

### 3. Install Laravel Sail

Run the following command to install Laravel Sail if not already installed:

```bash
docker run --rm   -v $(pwd):/app   -w /app   laravelsail/php82-composer:latest composer require laravel/sail --dev
```

### 4. Install Dependencies (Without Local Composer)

If you don’t have Composer installed on your local machine, you can use a Docker container to run `composer install`:

```bash
docker run --rm   -v $(pwd):/app   -w /app   laravelsail/php82-composer:latest composer install
```

This will use a lightweight Docker image with Composer to install the necessary dependencies in your project.

### 5. Add Sail Alias (Optional)

For convenience, add the following alias to your shell configuration (e.g., `.bashrc`, `.zshrc`):

```bash
alias sail='sh $([ -f sail ] && echo sail || echo vendor/bin/sail)'
```

Then reload your shell:

```bash
source ~/.bashrc
```

Or, for Zsh:

```bash
source ~/.zshrc
```

From now on, you can use `sail` instead of `./vendor/bin/sail`.

### 6. Start the Docker Containers

Run the Sail up command to start the application:

```bash
sail up -d
```

This will build and start all the necessary Docker containers.

### 7. Run Migrations

Apply the database migrations:

```bash
sail artisan migrate
```

### 8. Generate the Application Key

Generate the application key required for Laravel to function properly:

```bash
sail artisan key:generate
```

### 9. Install Frontend Dependencies

Install Node.js dependencies for the frontend:

```bash
sail npm install
```

### 10. Run Development Server

Start the frontend development server with:

```bash
sail npm run dev
```

---

## Access the Application

Once the containers are running, you can access the application in your browser at:

```
http://localhost
```

---

## Useful Commands

### Stop the Docker Containers

To stop the running containers:

```bash
sail down
```

### Access the Laravel Tinker Shell

```bash
sail artisan tinker
```

### Clear Cache

```bash
sail artisan cache:clear
```

---

## Troubleshooting

- **Error: Permission denied**  
  Ensure you have proper file permissions. Run:

    ```bash
    sudo chmod -R 777 storage bootstrap/cache
    ```

- **Containers not starting properly**  
  Ensure Docker is running and check the logs with:
    ```bash
    sail logs
    ```

---

## Contributing

Feel free to fork this repository and submit a pull request. Contributions are always welcome!

---

## License

This project is open-source and licensed under the [MIT license](LICENSE).
