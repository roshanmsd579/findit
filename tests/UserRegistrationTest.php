<?php

use PHPUnit\Framework\TestCase;

class UserRegistrationTest extends TestCase
{
    protected $db;

    protected function setUp(): void
    {
        // It's a good practice to use a separate test database
        // For simplicity, we'll use the main database and clean up after tests.
        // A better approach would be to use a dedicated test database and transactions.
        $this->db = new mysqli('localhost', 'root', '', 'findit_test');
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error);
        }
        // Clean up the users table before each test
        $this->db->query("DELETE FROM users");
    }

    protected function tearDown(): void
    {
        // Clean up the users table after each test
        $this->db->query("DELETE FROM users");
        $this->db->close();
    }

    public function testSuccessfulRegistration()
    {
        $_POST['username'] = 'testuser';
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'password123';
        $_POST['confirm_password'] = 'password123';
        $_POST['register'] = true;

        // To capture output
        ob_start();
        include 'register.php';
        $output = ob_get_clean();

        // Check if user is in the database
        $result = $this->db->query("SELECT * FROM users WHERE email = 'test@example.com'");
        $this->assertEquals(1, $result->num_rows);

        // Check for redirect
        // This is tricky as header() is used. We can check for the success message.
        $this->assertStringContainsString('Registration successful', $output);
    }

    public function testRegistrationWithExistingEmail()
    {
        // First, create a user
        $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
        $this->db->query("INSERT INTO users (username, email, password) VALUES ('existinguser', 'test@example.com', '$hashed_password')");

        $_POST['username'] = 'testuser';
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'password123';
        $_POST['confirm_password'] = 'password123';
        $_POST['register'] = true;

        ob_start();
        include 'register.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Email already registered', $output);
    }

    public function testRegistrationWithPasswordMismatch()
    {
        $_POST['username'] = 'testuser';
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'password123';
        $_POST['confirm_password'] = 'wrongpassword';
        $_POST['register'] = true;

        ob_start();
        include 'register.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Passwords do not match', $output);
    }

    public function testRegistrationWithInvalidEmail()
    {
        $_POST['username'] = 'testuser';
        $_POST['email'] = 'invalid-email';
        $_POST['password'] = 'password123';
        $_POST['confirm_password'] = 'password123';
        $_POST['register'] = true;

        ob_start();
        include 'register.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('Invalid email format', $output);
    }

    public function testRegistrationWithMissingFields()
    {
        $_POST['username'] = '';
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'password123';
        $_POST['confirm_password'] = 'password123';
        $_POST['register'] = true;

        ob_start();
        include 'register.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('All fields are required', $output);
    }
}
