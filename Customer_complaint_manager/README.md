# Customer_complaint_manager

## Purpose

Customer_complaint_manager is a PHP and MySQL application for submitting, tracking, assigning, and resolving customer complaints. It uses a Model-View-Controller (MVC) structure to separate database operations, request handling, and page display.

## Requirements

- PHP with PDO and the MySQL PDO driver.
- MySQL or MariaDB.
- Apache configured for HTTPS. The application redirects HTTP requests to HTTPS and uses secure session cookies.

XAMPP can provide the local web server, PHP, and database environment.

## Installation and Database Setup

1. Place the project in your web server's document root under `Customer_complaint_manager` (for XAMPP, use its `htdocs` directory).
2. Start Apache and MySQL and configure HTTPS for your application host.
3. For a new installation, import `database/database.sql`. It creates and selects `Customer_complaint_management` and inserts product and complaint-category records.
4. With `Customer_complaint_management` selected, import `database/security.sql`, followed by `database/temporary-password.sql`.
5. Configure the connection settings in `models/Database.php` for your database host, port, database name, username, and password. Use credentials appropriate to your environment.
6. Check `APP_URL` and `APP_COOKIE_PATH` in both `index.php` and `test-database.php`. Update them if your host or installation directory differs from the default.

The temporary-password migration adds `must_change_password` to customers and employees. Run it only once; repeating it produces duplicate-column errors. Back up an existing database before applying schema changes, and do not reimport the base seed data into an existing installation.

The supplied database script does not create an initial administrator account. Provision the initial administrator in the database with a PHP `password_hash()` value in `password_hash`, rather than a plaintext password. Keep real credentials out of GitHub.

## Running the Application

1. Start Apache and MySQL.
2. Open `https://localhost/Customer_complaint_manager/index.php` for the default local installation, or the application URL configured for your server.
3. Create a customer account or log in with an existing account.

A local XAMPP certificate may cause a browser certificate warning. HTTPS certificate trust is configured separately from the application.

## Logging In

Select the correct account type and enter the appropriate login information.

| Account type | Login information |
| --- | --- |
| Customer | Registered email address and password |
| Technician | Employee User ID and password |
| Administrator | Employee User ID and password |

Employees use the value stored in the database's `user_id` field. The numeric `customer_id` and `employee_id` fields are internal identifiers and are not login credentials.

## Customer Features

Customers can:

- Register an account.
- View and update their profile.
- Submit a complaint by selecting a product or service, selecting a category, and describing the issue.
- View their own complaints and complaint statuses.
- Read technician notes and resolution information.

Customers cannot access other customers' complaints or technician and administrator management pages.

## Technician Features

Technicians can:

- View open complaints assigned to them.
- Review complaint details and existing notes.
- Add technician notes or analysis.
- Update an assigned complaint's status.
- Close a complaint by providing a resolution date and resolution notes.

Technicians cannot update complaints assigned to another technician or complaints that are already closed.

## Administrator Features

Administrators can:

- View and edit customer information.
- Create and edit employee accounts.
- Assign employee roles as Technician or Administrator.
- View open and unassigned complaints.
- Assign or reassign open complaints to technicians.
- View technician workloads based on assigned open complaints.
- Generate temporary passwords for customer and employee accounts.
- Run the database connection test at `test-database.php`.

Existing employee User IDs cannot be changed through the employee edit form.

All account types can change their password and sign out.

## Password Rules

New passwords must:

- Be 8–72 bytes long (equivalent to 8–72 characters for ASCII passwords).
- Include an uppercase letter (`A–Z`).
- Include a lowercase letter (`a–z`).
- Include a number (`0–9`).
- Include a special character.
- Contain no null bytes.

The current validation measures length in bytes. Multibyte characters can reach the byte limit before reaching 72 characters. Password confirmation must match where requested. A password change must use a password different from the current or temporary password.

Passwords are stored as hashes rather than readable text.

## Changing a Password

Signed-in users select **Change Password**, enter their current password, and enter and confirm a new password.

After a successful change, the previous password no longer works. Other existing sessions are invalidated when they next access the application.

## Forgotten Passwords and Temporary Passwords

Password recovery is handled by an administrator. The application does not send password-reset emails or use personal security questions.

1. The user contacts an administrator.
2. The administrator verifies the user's identity.
3. The administrator opens **Manage Customers** or **Manage Employees** and selects **Reset Password** beside the account.
4. The administrator enters their own password to confirm the reset.
5. The application generates a temporary password and displays it once.
6. The administrator gives the temporary password directly to the user through a trusted communication method.
7. The user logs in using their normal email address or employee User ID and the temporary password.
8. The user must choose a new password before accessing their dashboard.

Only the temporary password's hash is saved in the database. The previous password stops working immediately. If the temporary password is lost, the administrator can generate another one.

New employee accounts also require a password change at first login because their initial passwords are assigned by an administrator.

Administrators use **Change Password** for their own accounts. An administrator who cannot log in needs another administrator to reset the account, or recovery by an authorized database operator if no other administrator is available.

## Sessions and Access

The application uses HTTPS, sessions, role permissions, complaint ownership checks, and form security tokens (CSRF protection). Sessions expire after 30 minutes without activity. Repeated failed login attempts and failed administrator password-reset confirmations are temporarily limited.

## Manual Testing

- Register a customer and confirm login works with the saved email address.
- Submit a complaint and verify that it appears in the customer's complaint list.
- As an administrator, assign the complaint to a technician; verify that technician can view and update it.
- Close a complaint with a resolution date and notes, then confirm the customer can see the resolution.
- Reset a test customer's password as an administrator. Confirm the old password fails, the temporary password works, and the customer must change it before continuing.
- Confirm the new password works and the temporary password no longer works after the change.
- Create an employee and confirm a password change is required at first login.
- Confirm customers cannot access another customer's complaints, technicians cannot update another technician's assignments, and neither role can access administrator actions.
- Change a password and confirm another existing session is rejected on its next request.
- As an administrator, run the database connection test.

For troubleshooting, inspect the PHP and Apache error logs configured for your server. Their locations depend on your installation.

## Project Structure

- `index.php`: HTTPS handling, session setup, and application entry point.
- `controllers/PageController.php`: Routing and request handling.
- `models/`: Account, complaint, and database logic.
- `views/`: Page templates and forms.
- `validation.php` and `helpers.php`: Input validation and shared rendering helpers.
- `database/`: Base schema, seed data, and schema migrations.
- `test-database.php`: Database check restricted to authenticated administrators.
