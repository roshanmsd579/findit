FINDIT - UNIVERSITY LOST & FOUND PORTAL
Setup Instructions:

1. Install XAMPP and start Apache + MySQL
2. Copy the entire findit folder to: C:/xampp/htdocs/findit/
3. Open browser: http://localhost/phpmyadmin
4. Click New and create database findit_db
5. Open findit_db and go to SQL tab
6. Paste full contents of database/setup.sql and run
7. Paste full contents of database/seed.sql and run
8. Ensure folder exists: C:/xampp/htdocs/findit/uploads/
9. Open: http://localhost/findit/

LOGIN CREDENTIALS:
  Student : aryan@university.edu / password123
  Faculty : sunita@university.edu / password123
  Admin   : admin@university.edu / password123

CHANGE UNIVERSITY NAME:
  Edit includes/config.php and update UNIVERSITY and UNI_EMAIL constants
