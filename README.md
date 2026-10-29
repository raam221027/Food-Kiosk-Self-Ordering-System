# Food Kiosk Self-Ordering System

A self-ordering food kiosk built with Laravel. Customers place their own orders at the kiosk, the **cashier** takes payment and prints a receipt, the **kitchen** sees the order queue, and the **admin** manages the menu, staff and sales reports.

## Live Demo

**https://&lt;your-app&gt;.up.railway.app** <!-- replace with the Railway URL after deploying -->

### Demo Accounts

Log in at **`/login`** with any of these accounts:

| Role | Email | Password | Lands on |
|---|---|---|---|
| Admin | `admin@demo.com` | `demo1234` | Admin dashboard (has access to all areas) |
| Cashier | `cashier@demo.com` | `demo1234` | Cashier dashboard |
| Kitchen | `kitchen@demo.com` | `demo1234` | Kitchen dashboard |
| Customer | *no login needed* | | Kiosk at **`/landing-page`** |

> This is a shared demo, so other visitors may have changed the data, and it may be reset from time to time.

### Try it in 2 minutes

1. **Customer:** open `/landing-page`, choose **Dine In** or **Take Out**, add items from the menu (or build one with **Make Your Own Salad**) and place the order.
2. **Cashier:** log in as `cashier@demo.com`, find the new order, take the payment and view the receipt.
3. **Kitchen:** log in as `kitchen@demo.com` and mark the order as done.
4. **Admin:** log in as `admin@demo.com` to manage products and staff and to view the daily sales and monthly product-sold reports.

## Features

**Customer kiosk**
- Menu browsing and cart
- "Make Your Own Salad" custom orders
- Dine In / Take Out order types

**Cashier**
- Pending order list and payment with change calculation
- Order receipts
- Daily sales report

**Kitchen**
- Order queue for orders that have been paid
- Mark orders as done

**Admin**
- Product and custom-product management (with photo upload, enable/disable)
- Staff accounts (Cashier / Kitchen) with enable/disable
- Daily sales and monthly product-sold reports with charts

## Tech Stack

- **Backend:** Laravel 10, PHP 8.1+
- **Database:** MySQL / MariaDB
- **Frontend:** Blade templates, Chart.js, ApexCharts
- **Auth & roles:** Laravel Fortify / Jetstream, role-based access via custom middleware, spatie/laravel-permission

## Security

- Each role can only open its own pages, and the Admin role can open all of them.
- Uploaded photos are stored with the extension detected from the file's contents, so a script can't be uploaded disguised as an image.
- Public registration is disabled; the Admin creates staff accounts.
- Debug mode is off in production, and login attempts are rate-limited.

## Run Locally

Requirements: PHP 8.1+, Composer, MySQL/MariaDB (e.g. XAMPP).

```bash
git clone https://github.com/raam221027/Food-Kiosk-Self-Ordering-System.git
cd Food-Kiosk-Self-Ordering-System
composer install
cp .env.example .env
php artisan key:generate
```

1. Create a database (e.g. `food_kiosk`) and import `food_kiosk.sql` into it.
2. Set `DB_DATABASE`, `DB_USERNAME` and `DB_PASSWORD` in `.env`. For local development you can also set `APP_ENV=local` and `APP_DEBUG=true`.
3. Create the demo accounts and start the server:

```bash
php artisan db:seed --class=DemoUserSeeder
php artisan storage:link
php artisan serve
```

Then open http://127.0.0.1:8000/landing-page.

Set `DEMO_PASSWORD` in `.env` before seeding if you want a different demo password.
