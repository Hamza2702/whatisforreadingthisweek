<img src="public/images/Logo.png" alt="Bookworms" width="250" height="250"/>

# Bookworms
<p>Between the books</p>
<p>'What is for reading this week'</p>

## How to use

- To run this locally you must have:
- [Node.js](https://nodejs.org/) - Used for running JavaScript on the server and managing frontend dependencies.
- [PHP](https://www.php.net/) - Used for the backend of the application.
- [Composer](https://getcomposer.org/) - Dependency manager for PHP, used to install and manage libraries.
- [HERD](https://herd.laravel.com/windows) - Local development environment for PHP and Laravel projects.
- Choose between [PostgreSQL](https://www.postgresql.org/) or [MySQL](https://www.mysql.com/) - Used for the database management system.
  
	### Steps

1. Clone the repository:

    ```bash
    git clone https://github.com/Hamza2702/whatisforreadingthisweek.git
    ```

2. Install dependencies:

    ```bash
    npm install
    composer install
    ```

3. Set up environment file and generate `APP_KEY`:

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. Build assets and start the application:

    ```bash
    # Open the first terminal and type:
    npm run dev    # Runs the debug server for the website

    # Open the second terminal and type:
    php artisan migrate:fresh --seed
    php artisan storage:link
    php artisan serve
    ```
