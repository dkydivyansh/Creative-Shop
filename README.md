# Creative Shop

> [dkydivyansh.com/portfolio/creative-shop](https://dkydivyansh.com/portfolio/creative-shop/)

An e-commerce platform designed for selling digital products — license keys, downloadable files, and physical goods.

## Tech Stack

- **Backend:** PHP, MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Libraries:** React, Lenis, Tailwind CSS, Lit-HTML, Lit-Elements

## Features

- 3D product rendering on the homepage
- High-contrast dark UI (white, black, dark purple)
- Dynamic page rendering via PHP controllers and templates
- Cart management, product listings, payment validation, and tax calculation
- Database-backed session management for multi-server deployment
- User authentication via [Dkydivyansh.com SSO](https://sso.dkydivyansh.com) (OAuth2 Authorization Code Grant)
- Centralized mailing server for transactional emails and notifications

## Setup

1. Import `datrabase.sql` into your MySQL server
2. Copy `config.php` and fill in your credentials (SSO, database, Razorpay, etc.)
3. Point your web server to the project root with `index.php` as the entry point
4. Register your callback URL (`https://yourdomain.com/auth/callback`) in the SSO dashboard
