# Inventory Management System

This project is a web-based Inventory 
Management System built using PHP and MySQL. It allows managers to track stock return requests and manage inventory tasks and returns efficiently.

## 📂 Project Files

- `return_component.php` – Handles returning of approved inventory items.
- `taskStatus.php` – Displays assigned tasks along with status for each employee.
- `db_connection.php` – Connects the project to the MySQL database.
- `emp.css`, `forms.css` – Provides styling for the UI components.

## ✅ Features

### 1. Return Component (`return_component.php`)
- Shows a table of inventory items with status `Approved`.
- Allows marking each item as `Returned`.
- On return:
  - Updates `request_issue` table (`status` → `Returned`)
  - Inserts return date into `issue_component`
  - Updates `stock` table to increment the `available` quantity

### 2. Task Status View (`taskStatus.php`)
- Displays all tasks assigned to employees.
- Shows details such as:
  - Employee ID and name
  - Task name and type
  - Task location
  - Assignment and deadline dates
  - Current status
- Session-based access for logged-in users.

## 🛠 Technologies Used

- **Backend**: PHP
- **Frontend**: HTML, CSS
- **Database**: MySQL

## 🔐 Authentication

- Session-based login protection
- Redirects to `index.php` if session not set
- Requires session variables:
  - `$_SESSION['Login']`
  - `$_SESSION['user_id']`
  - `$_SESSION['name']`

## 🗃️ Database Schema Overview

You need the following tables in your MySQL database:

### `request_issue`
| Column       | Type        | Description                     |
|--------------|-------------|---------------------------------|
| r_id         | INT         | Request ID                      |
| stock_id     | INT         | Related stock item              |
| quantity     | INT         | Quantity requested              |
| status       | VARCHAR     | Status (`Approved`, `Returned`) |
| emp_id       | INT         | Employee who requested          |
| request_date | DATE        | Request date                    |

### `issue_component`
| Column       | Type    | Description            |
|--------------|---------|------------------------|
| r_id         | INT     | Request ID (foreign key)|
| return_date  | DATE    | Date of return         |

### `stock`
| Column      | Type    | Description          |
|-------------|---------|----------------------|
| stock_id    | INT     | Stock item ID        |
| item_name   | VARCHAR | Name of the item     |
| available   | INT     | Available quantity   |

### `employee`
| Column      | Type    | Description         |
|-------------|---------|---------------------|
| emp_id      | INT     | Employee ID         |
| name        | VARCHAR | Employee name       |

### `assign_task`
| Column       | Type    | Description                     |
|--------------|---------|---------------------------------|
| assign_id    | INT     | Assignment ID                   |
| task_id      | INT     | Linked task                     |
| emp_id       | INT     | Assigned employee               |
| location_id  | INT     | Location of the task            |
| assign_date  | DATE    | Date of assignment              |
| deadline     | DATE    | Task deadline                   |
| status       | VARCHAR | Task status                     |

### `task`
| Column      | Type    | Description        |
|-------------|---------|--------------------|
| task_id     | INT     | Task ID            |
| task_name   | VARCHAR | Task name          |
| task_type   | VARCHAR | Task type (e.g., cleaning, maintenance) |

### `Location`
| Column        | Type    | Description     |
|---------------|---------|-----------------|
| location_id   | INT     | Location ID     |
| location_name | VARCHAR | Location name   |

## 🚀 Getting Started

1. Clone the repository or copy the files to your web server directory.
2. Set up your MySQL database and import/create all necessary tables.
3. Update `db_connection.php` with your MySQL credentials.
4. Start your web server (e.g., XAMPP, WAMP) and visit the app:
   - `http://localhost/stock-management/return_component.php`
   - `http://localhost/stock-management/taskStatus.php`

## 📌 Notes

- The return feature automatically updates the stock quantity.
- Task listing currently does not support editing or updating statuses from the interface.
- Make sure all database tables are created properly with relationships (foreign keys) for full functionality.

## 📃 License

This project is intended for educational and internal use only.
