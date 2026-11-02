# Bakery Web App

A Laravel 12 web application for a bakery: a public website with online ordering
and an admin panel to run the business day to day.

## Features

**Public site**
- Homepage with slider, news, photo gallery and contact page
- Menu organised by category, with availability and low-stock indicators
- Cart and ordering: dine-in, takeaway or delivery, with live order tracking
- Installable as a PWA, with browser notifications on order status changes

**Admin panel**
- Products and categories, with CABYS codes and visibility/availability switches
- Stock control with a movement log (sales, purchases, adjustments, cancellations)
- Orders screen with auto-refresh, status workflow and automatic invoicing on delivery
- Invoices with PDF output (letter and receipt ticket), voiding with stock return
- Suppliers and purchases that update stock and cost
- Content management: slider, news, gallery, contact details and logo
- Light and dark mode

## Tech stack
Laravel 12 · PHP · MySQL · Blade · Bootstrap 5 · Vanilla JavaScript · Dompdf

## Notes
- Prices include VAT (13% by default).
- Order creation, stock movements and invoicing run inside database transactions.
- Validation is applied in three layers: browser, Laravel and MySQL `CHECK` constraints.

## Setup
1. Clone the repository and run `composer install`.
2. Copy `.env.example` to `.env` and set the database and mail credentials.
3. Create the database and run `database/esquema.sql` (and `database/restricciones.sql`).
4. Run `php artisan key:generate` and `php artisan storage:link`.
5. Start the app with `php artisan serve`.
