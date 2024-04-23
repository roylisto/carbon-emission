**Carbon Emission Calculation**

Welcome to Carbon Emission Calculation! This README will guide you through setting up the project either via Docker for a quick start or manually by installing dependencies.

### Prerequisites

-   PHP 8.2
-   Composer

### Steps

1. Clone this repository to your local machine.

    ```bash
    git clone <repository_url>
    ```

2. Navigate to the project directory.

    ```bash
    cd <project_directory>
    ```

3. Install PHP dependencies using Composer.

    ```bash
    composer install
    ```

4. Copy `.env.example` to `.env` and configure the database and squake settings

5. Generate Laravel application key.

    ```bash
    php artisan key:generate
    ```

6. Run `php artisan serve`
