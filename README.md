# Anonymous Confession Board

A simple, modern web application that allows users to submit anonymous confessions. Built with HTML, CSS, JavaScript, PHP, and MySQL.

## Features

- Anonymous confession submission
- Real-time confession feed with animations
- Responsive design for mobile and desktop
- Admin dashboard for content moderation
- Secure database storage
- Input validation and XSS protection

## Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

## Installation

1. Clone or download this repository to your web server directory

2. Create a MySQL database and import the schema:
   ```sql
   mysql -u your_username -p < database/setup.sql
   ```

3. Configure the database connection:
   - Open `backend/config.php`
   - Update the following variables with your database credentials:
     ```php
     $db_host = 'localhost';
     $db_name = 'anonymous_confessions';
     $db_user = 'your_username';
     $db_pass = 'your_password';
     ```

4. Set up admin credentials:
   - Open `admin/index.php`
   - Modify the following constants with secure credentials:
     ```php
     define('ADMIN_USERNAME', 'admin');
     define('ADMIN_PASSWORD', 'admin123');
     ```

5. Set proper permissions:
   ```bash
   chmod 755 -R /path/to/project
   chmod 644 backend/config.php
   ```

## Security Considerations

1. Change default admin credentials immediately
2. Use strong passwords
3. Enable HTTPS on your server
4. Keep PHP and MySQL updated
5. Consider implementing rate limiting
6. Regular security audits

## Project Structure

```
/
├── index.html           # Main application page
├── css/
│   └── style.css       # Main stylesheet
├── js/
│   └── main.js         # Frontend JavaScript
├── backend/
│   ├── config.php      # Database configuration
│   ├── submit_confession.php
│   └── get_confessions.php
├── admin/
│   ├── index.php       # Admin dashboard
│   ├── login_form.php  # Admin login template
│   ├── logout.php      # Admin logout handler
│   └── admin.css       # Admin styles
└── database/
    └── setup.sql       # Database schema
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed.
