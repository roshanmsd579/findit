# FindIt Lost & Found Portal

## Setup on XAMPP

1. Start XAMPP and run Apache + MySQL.
2. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Click New and create a database named `findit_db`.
4. Open the `findit_db` database and go to the SQL tab.
5. Paste and run the content from `database/setup.sql`.
6. Paste and run the content from `database/seed.sql`.
7. Ensure this project folder is placed at `C:/xampp/htdocs/findit/`.
8. Open [http://localhost/findit/](http://localhost/findit/) in your browser.
9. User login: `rahul@example.com` / `password123`.
10. Admin login: `admin@findit.com` / `password123`.

## New Features Included

- MySQL-backed reports, users, comments, notifications, matches, and chat messages
- Camera capture support in report creation (mobile camera + laptop webcam)
- Polling-based chat system with unread badge
- Dark and light theme toggle with persisted preference
