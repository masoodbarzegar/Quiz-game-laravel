# 🧠 Quiz Game App (Laravel 12 + React)

A full-stack quiz game application built with **Laravel 12** and **React** using **Vite**. The app allows users to register, take time-limited quizzes, and view their results. It also provides an admin panel with role-based access control for managing questions and reviewing quiz results.

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
| 1     | 10                   | 2                          |
| 2     | 5                    | 4                          |
| 3     | 5                    | 6                          |
| 4     | 3                    | 10                         |

**Maximum Score:** 100 points

---

## 🔐 User Roles and Access

### 👤 Client (Quiz Participant)
- Registers and logs in through `/api/client/*`
- Can take quizzes
- Can view their own quiz history and results

### 🛠 Admin Panel Users (via `users` table)

| Role       | Description                                |
|------------|--------------------------------------------|
| General    | Can create, update, and delete questions   |
| Corrector  | Can review and approve submitted questions |
| Manager    | Can view and filter quiz/test results      |

All admin users log in via `/api/admin/login` and access the React-based admin dashboard.

---

## 🧰 Tech Stack
| Layer        | Technology               |
|--------------|---------------------------|
| Backend      | Laravel 12                |
| Frontend     | React (Vite, in `resources/js`) |
| Auth (Client)| Laravel Sanctum + `clients` table |
| Auth (Admin) | Laravel Sanctum + `users` table   |
| Styling      | Tailwind CSS (optional)  |
| DB           | MySQL / PostgreSQL       |
| API          | RESTful via `api.php`    |

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
---

