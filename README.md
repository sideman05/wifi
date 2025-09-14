# WiFi Billing System

A PHP-based WiFi Billing System that allows managing users, plans, and payments with **clean URLs** and a **password recovery system**. Built to work on **LAMP stack** locally.

---

## Features

- **User Management**
  - Admin can create, edit, and delete users.
  - Users can log in with username/email.
- **Plans Management**
  - Create different WiFi subscription plans with price and speed.
- **Payment Integration**
  - Supports MPESA payments (via API integration).
- **Password Recovery**
  - Users can reset forgotten passwords using a secure token.
- **Clean URLs**
  - URLs like `/plans`, `/dashboard`, `/login` without `.php`.
- **Loading Spinner**
  - Page loading indication for better UX.
- **Secure**
  - Sensitive files like `config.php` and `db.php` are protected.
  
---

## Requirements

- PHP >= 7.4
- MySQL / MariaDB
- Apache with `mod_rewrite` enabled (included in LAMP/XAMPP)
- Composer (optional, for mail libraries)

---

## Installation

1. **Clone the project**
```bash
git clone https://github.com/yourusername/wifi-billing.git
cd wifi-billing
