**Carbon Emission Calculator**

Welcome to the Carbon Emission Calculator! This guide will assist you in setting up the project.
To see the system design of this project please refer to [System Design](https://docs.google.com/document/d/1gRlurBHwjXJwUHPvo-N-MfguS7DkSUS-27PVdm621ow/edit?usp=sharing)

### Prerequisites

-   PHP 8.2
-   Composer

### Steps

1. Clone this repository to your local machine.

    ```bash
    git clone https://github.com/roylisto/carbon-emission.git
    ```

2. Navigate to the project directory.

    ```bash
    cd carbon-emission
    ```

3. Install PHP dependencies using Composer.

    ```bash
    composer install
    ```

4. Copy `.env.example` to `.env` and configure the `database` and `squake` settings.

5. Generate the Laravel application key.

    ```bash
    php artisan key:generate
    ```

6. Run database migration

    ```bash
    php artisan migrate
    ```

7. Run the server.

    ```bash
    php artisan serve
    ```

### Running Integration Test

Run the following command:

```bash
php artisan test
```

### Postman

[Download Postman Collection](./emission.postman_collection.json)

### How to Use it

1. Register a user by sending a POST request to `/api/register` endpoint, with an example payload:

    ```json
    {
        "name": "test",
        "email": "test@test.com",
        "password": "123456",
        "c_password": "123456"
    }
    ```

2. Login using email and password via the `/api/login` endpoint, with an example payload:

    ```json
    {
        "email": "test@test.com",
        "password": "123456"
    }
    ```

3. Use the token received from the login response as a `Bearer` token in the `Authorization` header to access Flight, Train, and Hotel endpoints. For example:

    ```bash
    curl --location 'http://localhost:8000/api/flight' \
    --header 'Accept: application/json' \
    --header 'Authorization: Bearer YOUR_TOKEN_HERE' \
    --header 'Content-Type: application/json' \
    --data '[
        {
            "origin": "CGK",
            "destination": "PLM",
            "external_reference": "test",
            "number_of_travelers": 1,
            "methodology": "ICAO"
        }
    ]'
    ```

4. For more examples, refer to the Postman collection.
