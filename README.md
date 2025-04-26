# KnowWay Learning Platform

KnowWay is a modern e-learning platform that allows users to browse, enroll in, and complete various courses. The platform features role-based access control with separate interfaces for learners and administrators.

## Features

### Learner Features

- Modern, responsive dashboard interface
- Course enrollment and progress tracking
- Personalized learning recommendations
- Profile management and settings

### Admin Features

- Course management (add, edit, delete courses)
- User management (view users, change roles, delete users)
- Dashboard with statistics and analytics
- Settings management

## System Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/LAMP stack (for local development)

## Installation

1. Clone the repository to your web server's document root (e.g., htdocs for XAMPP)

   ```
   git clone https://github.com/yourusername/know-way.git
   ```

2. Import the database schema:
   - Navigate to phpMyAdmin
   - Create a new database called 'know-way'
   - Import the SQL file from `config/db/know-way.sql`
3. Update database configuration if needed in `config/db.php`

4. Access the application in your browser:
   ```
   http://localhost/know-way/
   ```

## User Roles

- **Learner**: Default role for registered users. Can browse courses, enroll, track progress.
- **Admin**: Administrative role with access to the admin panel. Can manage courses, users, and settings.

## Security

- Passwords are securely hashed using PHP's password_hash() function
- Role-based access control prevents unauthorized access to admin areas
- Input validation and sanitization to prevent SQL injection and XSS attacks

## Directory Structure

- `config/` - Database configuration and SQL schema
- `controller/` - PHP controllers for handling form submissions and actions
- `view/` - User interface files (HTML, PHP views)
- `view/uploads/` - Uploaded files (course images, etc.)

## Development

To modify the users' table structure (if needed), run the SQL in `config/db/update_users_table.sql`.

## License

[Your License Here]
