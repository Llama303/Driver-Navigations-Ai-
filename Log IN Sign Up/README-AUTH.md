# Drive-NAV auth: database setup

This folder has everything for **sign up**, **log in**, and **forgot password** using a SQLite database.

## 1. Run the app with PHP

You need a web server with PHP (and SQLite support, which is usually built in). For example:

- **XAMPP / WAMP / MAMP**: put the project in `htdocs` and open  
  `http://localhost/Driver-Navigations-Ai-/Log%20IN%20Sign%20Up/install.php`
- **PHP built-in server** (from project root):  
  `php -S localhost:8000`  
  Then open: `http://localhost:8000/Log%20IN%20Sign%20Up/install.php`

## 2. One-time setup: create the database

1. In the browser, go to: **`install.php`**  
   (same folder as `LogInPage.html`, URL like above).
2. You should see “Setup completed successfully” and the `users` and `password_reset_tokens` tables will be created.
3. **Then delete or rename `install.php`** so others cannot run it again.

The database file is created at:  
**`Log IN Sign Up/data/drive_nav.db`**

## 3. What works after setup

| Action | What happens |
|--------|----------------|
| **Sign Up** | New user is stored in `users` (username, email, hashed password). Redirects to Log In with a success message. |
| **Log In** | Checks username/password against the database. On success, sets session and redirects to `../index.html`. |
| **Forgot password** | User enters email → a reset token is stored (valid 1 hour). Success page shows a reset link (until you add email sending). |
| **Reset password** | User opens the reset link → **ResetPassword.html?token=...** → enters new password → password is updated and token is removed. |

## 4. Password reset link (no email yet)

Right now the reset **link is shown on the success page** after “Forgot password” (so you can test without configuring email).  

To send the link by email instead:

1. In **ForgotPassword.PHP**, uncomment the `mail()` block and set your mail config.
2. Optionally remove the `&reset_link=...` from the redirect so the link is only sent by email.

## 5. Security notes

- Passwords are hashed with `password_hash()` (bcrypt).
- Reset tokens expire after 1 hour and are deleted after use.
- Keep **`data/`** out of public URLs (e.g. don’t serve `drive_nav.db` directly). Your server may already block listing of that folder.
- After setup, remove or protect **install.php** so the database cannot be recreated by others.

## Files involved

| File | Purpose |
|------|--------|
| **config.php** | DB path and `getDb()` (SQLite). |
| **install.php** | One-time: creates DB and tables. Delete after use. |
| **Login.PHP** | Log in against DB, sets session. |
| **SignUp.PHP** | Create account, redirect to login. |
| **ForgotPassword.PHP** | Create reset token, redirect with success (+ optional link). |
| **ResetPassword.PHP** | Check token, set new password, redirect to login. |
| **data/drive_nav.db** | SQLite database (created by install.php). |
