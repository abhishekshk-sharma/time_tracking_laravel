# Time Tracking Laravel Application

This is a Laravel conversion of the original PHP time tracking system. It includes employee time tracking, leave management, and admin functionality.

## Features

- **Employee Dashboard**: Time tracking with punch in/out, lunch breaks
- **Leave Management**: Apply for leave, sick leave, complaints, regularization
- **Admin Panel**: Manage employees, approve/reject applications
- **Time Calculations**: Automatic work hours calculation
- **Responsive Design**: Works on desktop and mobile devices

## Installation

1. **Clone or navigate to the project directory**
   ```bash
   cd d:\XAMPP\htdocs\time-tracking-laravel
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Create database**
   - Create a MySQL database named `time_tracking_laravel`
   - Update `.env` file with your database credentials

4. **Run migrations and seed data**
   ```bash
   php artisan migrate --seed
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Create storage link**
   ```bash
   php artisan storage:link
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

## Default Login Credentials

### Admin Account
- **Username**: admin
- **Password**: password

### Employee Accounts
- **Username**: sarah | **Password**: password
- **Username**: michael | **Password**: password
- **Username**: emily | **Password**: password

## Key Differences from Original PHP Version

1. **MVC Architecture**: Proper separation of concerns using Laravel's MVC pattern
2. **Eloquent ORM**: Database interactions using Eloquent instead of raw PDO
3. **Blade Templates**: Modern templating engine instead of mixed PHP/HTML
4. **Middleware**: Role-based access control using Laravel middleware
5. **Form Validation**: Built-in Laravel validation
6. **CSRF Protection**: Automatic CSRF token handling
7. **Route Management**: Named routes with proper organization
8. **Database Migrations**: Version-controlled database schema
9. **Seeders**: Consistent test data setup

## File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   └── ApplicationController.php
│   └── Middleware/
│       ├── AdminMiddleware.php
│       └── EmployeeMiddleware.php
├── Models/
│   ├── Employee.php
│   ├── TimeEntry.php
│   ├── Application.php
│   ├── SystemSetting.php
│   └── Department.php
database/
├── migrations/
└── seeders/
resources/
└── views/
    ├── auth/
    ├── dashboard/
    └── applications/
routes/
└── web.php
```

## API Endpoints

### Authentication
- `GET /login` - Show login form
- `POST /login` - Process login
- `POST /logout` - Logout user

### Employee Dashboard
- `GET /dashboard` - Employee dashboard
- `POST /punch-in` - Punch in
- `POST /punch-out` - Punch out
- `POST /lunch-start` - Start lunch break
- `POST /lunch-end` - End lunch break
- `POST /time-data` - Get current time data

### Applications
- `GET /applications` - Applications page
- `POST /applications` - Submit new application
- `GET /applications-history` - View application history

## Database Schema

The application uses the following main tables:
- `employees` - Employee information and authentication (with emp_id as unique identifier)
- `time_entries` - Time tracking records
- `applications` - Leave/complaint applications (with subject, description, file fields)
- `system_settings` - System configuration
- `departments` - Department information
- `leavecount` - Employee leave balance tracking
- `notification` - Application notifications
- `wfh` - Work from home requests

## Key Structure Changes from Original

- **Employee ID**: Uses `emp_id` (string) instead of auto-increment ID
- **Applications**: Added `subject`, `description`, `half_day`, `file`, `action_by` fields
- **Time Entries**: Uses `employee_id` as foreign key to `emp_id`
- **Additional Tables**: `leavecount`, `notification`, `wfh` for extended functionality

## Contributing

1. Follow Laravel coding standards
2. Use proper validation for all forms
3. Implement proper error handling
4. Add comments for complex logic
5. Test all functionality before committing

## License

This project is open-source software licensed under the MIT license.