# FindIt - University Lost and Found Portal

FindIt is a full-featured campus lost-and-found web application built with PHP, MySQL, Bootstrap 5, and vanilla JavaScript. It supports report creation, claim verification, real-time style chat polling, admin moderation, profile and review flows, and dispute handling.

## Tech Stack

- PHP 8+
- MySQL (MariaDB via XAMPP)
- Bootstrap 5
- Vanilla JavaScript
- Leaflet + OpenStreetMap (location pinning)

## Project Structure

```text
findit/
|- api/                # JSON API endpoints
|- assets/
|  |- css/             # UI styles
|  \- js/              # Frontend behavior
|- database/
|  |- setup.sql        # Schema
|  \- seed.sql         # Demo data
|- includes/           # Shared config, DB, header/footer/navbar
|- uploads/            # User-uploaded images
|- *.php               # Application pages
```

## Local Setup (XAMPP)

1. Start XAMPP and run Apache + MySQL.
2. Open phpMyAdmin: [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
3. Create a database named `findit_db`.
4. Import schema from `database/setup.sql`.
5. Import sample data from `database/seed.sql`.
6. Place this project at `C:/xampp/htdocs/findit/`.
7. Open the app: [http://localhost/findit/](http://localhost/findit/)

## Demo Credentials

All seeded accounts use the password below:

- Password: `password123`

Recommended accounts:

- Student: `aryan@university.edu`
- Faculty: `sunita@university.edu`
- Admin: `admin@university.edu`

## Core Features

- User authentication and role-based access (student/faculty/staff/security/admin)
- Lost and found report posting with category, date/time, and optional media
- Campus location capture with Leaflet map pinning
- Claim workflow with secret-answer verification and handover code flow
- Chat between reporter and claimant with unread handling
- Notifications for claim, chat, dispute, and resolution events
- Profile pages with rating and review support
- Admin panel for report moderation, claims, disputes, and activity
- Light/dark theme toggle with persistent preference

## API Endpoints

Main API endpoints are available under `api/`:

- `submit-claim.php`
- `verify-code.php`
- `confirm-handover.php`
- `send-message.php`
- `get-messages.php`
- `mark-notifications.php`
- `rate-user.php`
- `raise-dispute.php`

## Notes

- Default configuration is in `includes/config.php`.
- Ensure `uploads/` is writable by the web server.
- For production deployment, update DB credentials, harden sessions/cookies, and disable debug error display.

## License

This project is provided for educational and portfolio use.
