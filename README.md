# 🧠 Quiz Game App (Laravel 12 + React)

A full-stack quiz game application built with **Laravel 12** and **React** using **Vite** and **Inertia.js**. The app allows users to register, take time-limited quizzes, and view their results. It also provides an admin panel with role-based access control for managing questions and reviewing quiz results.

**Note:** This project is a modernized version, migrated from an earlier implementation in CodeIgniter.

---

## 🚀 Installation

This section will guide you through setting up the project on your local machine.

### Using GitHub Actions (Recommended for Quick Setup)

You can use a GitHub Actions workflow to generate an automated setup script tailored for your operating system. This script will handle cloning the repository, setting up environment variables, starting Docker containers, installing dependencies, and running migrations.

1.  **Navigate to the Actions Tab:**
    * Go to your project's GitHub page.
    * Click on the **Actions** tab.

2.  **Select "Generate Setup Script" Workflow:**
    * In the left sidebar, find and click on the "Generate Setup Script" workflow.

3.  **Run Workflow:**
    * Click the **"Run workflow"** dropdown button on the right.
    * Select your **OS Type** (linux, mac, or windows) from the dropdown.
    * Click the **"Run workflow"** button.

4.  **Download the Artifact:**
    * Once the workflow completes (it will show a green checkmark), click on the specific workflow run.
    * Scroll down to the "Artifacts" section.
    * Download the `setup-script` artifact. This will typically be a `.zip` file containing either `setup.sh` (for Linux/macOS) or `setup.ps1` (for Windows).

5.  **Execute the Script:**
    * **For Linux/macOS:**
        * Unzip the downloaded file.
        * Open your terminal, navigate to the directory where you unzipped the file, and make the script executable:
            ```bash
            chmod +x setup.sh
            ```
        * Run the script:
            ```bash
            ./setup.sh
            ```
    * **For Windows:**
        * Unzip the downloaded file.
        * Open PowerShell as Administrator, navigate to the directory where you unzipped the file, and run the script:
            ```powershell
            .\setup.ps1
            ```

The script will guide you through the setup process. Once complete, you can access the application.

* Frontend (Client & Admin): `http://localhost`

### Using Docker (Manual - Alternative to GitHub Actions)

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/masoodbarzegar/Quiz-game-laravel](https://github.com/masoodbarzegar/Quiz-game-laravel)
    cd Quiz-game-laravel
    ```

2.  **Environment Configuration:**
    * Copy the Docker environment example file:
        ```bash
        cp .env.docker .env
        ```
    * Review and update the `.env` file with your specific configurations (e.g., database credentials, app URL). The Docker setup expects the database connection to be `mysql` with host `mysql`, database name `quiz`, username `quizuser`, and password `quizpass`. Adjust if necessary.

3.  **Build and Run Docker Containers:**
    ```bash
    docker compose up -d --build
    ```

4.  **Install Dependencies & Setup Application:**
    * Access the application container. Note that the container name might vary (e.g., `quiz-game-laravel-app-1` or similar, depending on your Docker Compose version). You can find it by running `docker ps`. For this guide, we'll assume a common pattern:
        ```bash
        docker exec -it quiz-game-laravel-app-1 bash
        ```
        (Replace `quiz-game-laravel-app-1` with your actual app container name if different.)

    * Inside the container, run:
        ```bash
        composer install
        npm install
        npm run build
        php artisan key:generate
        php artisan migrate --seed
        php artisan storage:link
        ```
    * **Fix Permissions (Crucial for Docker setups):**
        ```bash
        chmod -R 775 /var/www/html/bootstrap/cache
        chmod -R 775 /var/www/html/storage
        chown -R www-data:www-data /var/www/html/storage
        chown -R www-data:www-data /var/www/html/bootstrap/cache
        mkdir -p /var/www/html/storage/framework/{cache,views,sessions}
        ```
    * Exit the container:
        ```bash
        exit
        ```

5.  **Access the Application:**
    * Frontend (Client & Admin): `http://localhost`

### Manual Installation (Without Docker)

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/masoodbarzegar/Quiz-game-laravel](https://github.com/masoodbarzegar/Quiz-game-laravel)
    cd Quiz-game-laravel/src
    ```

2.  **Install PHP Dependencies:**
    ```bash
    composer install
    ```

3.  **Environment Configuration:**
    * Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    * Generate an application key:
        ```bash
        php artisan key:generate
        ```
    * Configure your `.env` file with database credentials and other settings (e.g., `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `APP_URL`). Ensure your database details match your local MySQL setup.

4.  **Database Setup:**
    * Ensure you have a compatible database server (MySQL) running.
    * Create a database for the application (e.g., `quiz`).
    * Run database migrations (and seeders if available):
        ```bash
        php artisan migrate --seed
        ```
    * Create the storage symlink:
        ```bash
        php artisan storage:link
        ```

5.  **Install Frontend Dependencies & Build:**
    ```bash
    npm install
    npm run build
    ```

6.  **Serve the Application:**
    * Use Laravel's built-in server (for development):
        ```bash
        php artisan serve
        ```
    * Or configure a web server like Nginx or Apache to point to the `public` directory.

7.  **Access the Application:**
    * If using `php artisan serve`, typically: `http://localhost:8000` (or whatever port `serve` reports).
    * If using a web server, configured URL (e.g., `http://localhost`).

---
## 🌟 Migration from CodeIgniter

This project represents a significant architectural shift and modernization from its original CodeIgniter implementation. The original CodeIgniter 3 project can be found here: [Quiz-game (CodeIgniter 3)](https://github.com/masoodbarzegar/Quiz-game).

Key changes and benefits of migrating to Laravel 12 include:

* **Modern Framework & Architecture:** Transitioned from CodeIgniter's more flexible (and sometimes less structured) approach to Laravel's robust, opinionated, and feature-rich MVC architecture. This brings better organization, maintainability, and scalability.
* **Eloquent ORM:** Data management is now handled by Laravel's powerful Eloquent ORM, providing an expressive and intuitive way to interact with the database, replacing CodeIgniter's Query Builder or simpler model interactions.
* **Advanced Routing System:** Leverages Laravel's sophisticated routing system, with client-facing routes in `routes/web.php` and admin panel routes neatly organized in `routes/admin.php`. This offers more flexibility and cleaner route definitions than CodeIgniter's traditional `routes.php`.
* **React with Inertia.js & Vite:** The frontend is built as a modern Single Page Application (SPA) using React, seamlessly integrated with the Laravel backend via Inertia.js. Vite is used for fast and efficient asset bundling. This is a major upgrade from potentially older frontend practices in the CodeIgniter version.
* **Robust Authentication & Authorization:**
    * **Laravel Sanctum:** Used for API token authentication, providing a secure way to manage sessions for both client and admin users.
    * **Custom Auth Guards:** Separate authentication guards (client and admin) are likely implemented (as seen in route definitions like `auth:client` and `auth:admin`) to manage distinct user types.
    * **Role-Based Access Control (RBAC):** The admin panel features RBAC (e.g., `admin.role:manager` middleware) for granular permission control, a feature often built manually in CodeIgniter.
* **Middleware:** Laravel's middleware system is used extensively for request filtering and handling, such as authentication checks and role enforcement, offering a more elegant solution than CodeIgniter's hooks or custom library implementations.
* **Service Container & Dependency Injection:** Benefits from Laravel's powerful service container for managing class dependencies and enabling dependency injection, leading to more decoupled and testable code.
* **Artisan Console:** Development and maintenance are aided by Laravel's artisan command-line tool, offering a wide range of helpful commands for tasks like migrations, seeding, route listing, and more.
* **Clear Separation of Concerns:** The project demonstrates a clear separation between Client and Admin functionalities, with dedicated controllers (e.g., `App\Http\Controllers\Client\*`, `App\Http\Controllers\Admin\*`), models (`Client.php`, `User.php`), and route groups.
* **Tailwind CSS:** Styling is handled by Tailwind CSS, a utility-first CSS framework, configured via `tailwind.config.js`.

These changes result in a more modern, secure, maintainable, and developer-friendly application.

---

## 🧪 Testing

* **Backend:** The application includes unit and feature tests written with PHPUnit to ensure the reliability of the Laravel backend.
* **Frontend:** Tests for the React frontend components and user interactions will be added in a subsequent phase.

---

## 🎯 Features

### ✅ General Functionality
- Time-based quiz: 5 minutes per test
- 20 randomized questions per test with difficulty levels
- Automatic score calculation based on difficulty level
- User registration/login system (for clients)
- Admin panel (React) with role-based access:
  - General (create/edit/delete questions)
  - Corrector (approve questions)
  - Manager (view and filter test results)
- Separate handling of **clients** and **admin users**
- React frontend for both client and admin views

---

## 🧩 Quiz Details
Each test consists of **20 questions**, selected randomly based on difficulty:
| Level | Number of Questions | Points per Correct Answer |
|-------|----------------------|----------------------------|
| 1     | 10                   | 3                          |
| 2     | 6                    | 5                          |
| 3     | 5                    | 8                          |

**Maximum Score:** 100 points

---

## 🔐 User Roles and Access

### 👤 Client (Quiz Participant)
- Registers and logs in through `/api/client/*`
- Can take quizzes
- Can view their own quiz history and results

### 🛠 Admin Panel Users (via users table)

| Role       | Description                                |
|------------|--------------------------------------------|
| General    | Can create, update, and delete questions   |
| Corrector  | Can review and approve submitted questions |
| Manager    | Can view and filter quiz/test results      |

All admin users log in via `/api/admin/login` and access the React-based admin dashboard.

---

## 🧰 Tech Stack
| Layer        | Technology                                                   |
|--------------|--------------------------------------------------------------|
| Backend      | Laravel 12                                                   |
| Frontend     | React (Vite, in `resources/js`)                              |
| Auth (Client)| Laravel Sanctum + `clients` table (via `auth:client` guard)  |
| Auth (Admin) | Laravel Sanctum + `users` table (via `auth:admin` guard)     |
| Styling      | Tailwind CSS                                                 |
| DB           | MySQL / PostgreSQL                                           |
| API          | Routes in `web.php` & `admin.php` (using Inertia.js for SPA) |

---

## 🗂 Project Structure
<pre> <code>
QuizGame/
├── .dockerignore               # Ignore files for Docker context
├── .env.docker                 # Optional: env overrides for Docker
├── .gitignore                  # Git ignore file
├── docker/
│   ├── mysql/
│   │   ├── data
│   ├── nginx/
│   │   └── default.conf        # Nginx config
│   └── Dockerfile              # PHP & Laravel image build
├── docker-compose.yml          # Docker services
├── README.md
└── src/                        # Laravel 12 app root
</code> </pre>

📌 License
This project is open-sourced under the MIT license.

🙌 Contributions
PRs and suggestions are welcome! Please fork the repo and create a pull request.