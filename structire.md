Project E-Commerce Store: PHP File Structure
This document outlines the file and folder structure for the PHP application. The project is organized using a Model-View-Controller (MVC) architecture to ensure a clean separation of concerns.

1. Root Directory Structure
The main project folder will contain the following directories and core files:

/controllers/ - Handles user input and business logic.

/models/ - Manages data and database interactions.

/views/ - Contains all HTML and presentation templates.

/public/ - The web server's document root, containing assets like CSS, JS, and images.

config.php - Stores all configuration constants.

db.php - Manages the database connection.

index.php - The single entry point and router for the application.

2. Controllers (/controllers/)
Controllers act as the "traffic cops" of the application. They receive requests from the router (index.php), interact with the necessary models to get data, and then pass that data to the appropriate view to be rendered.

HomeController.php: Manages the home page.

ProductController.php: Handles logic for displaying single products and category pages.

UserController.php: Manages the user profile page.

OrderController.php: Handles displaying order history, order details, and generating invoices.

CartController.php: Manages adding, updating, and viewing the user's cart.

CheckoutController.php: Handles the checkout process and payment logic.

AuthController.php: Manages the redirects to the external authentication server and handles the callback.

StaticPageController.php: Renders static pages like 'About Us', 'Privacy Policy', etc.

3. Views (/views/)
Views are responsible for the presentation layer (the HTML that the user sees). They should contain minimal PHP, primarily for displaying data passed to them by a controller.

/layouts/: Reusable template files.

header.php (contains <head>, navigation)

footer.php

/products/: Views related to products.

home.php (the main home page view)

category.php (displays products in a category)

single_product.php (the product detail page)

/user/: Views for authenticated users.

profile.php

order_history.php

order_details.php

invoice.php

/cart/:

cart.php (the main cart view)

checkout.php

/static/:

about.php

privacy_policy.php

terms.php

donate.php

404.php (the "Not Found" error page)

4. Models (/models/)
Models are the classes that interact directly with your database. Each model will correspond to a database table and will contain the methods for querying and manipulating data in that table (e.g., fetching products, creating orders).

Product.php: Fetches product data from the products table.

Category.php: Fetches data from the categories table.

Order.php: Manages creating and retrieving data from orders and order_items.

User.php: While authentication is external, this model could handle fetching user-related data from your local users table.

Cart.php: Manages all database operations for the user_cart table.

Payment.php: Manages creating and retrieving records from the payments table.

5. Visual File Tree
Here is a visual representation of the complete project structure:

/
├── controllers/
│   ├── AuthController.php
│   ├── CartController.php
│   ├── CheckoutController.php
│   ├── HomeController.php
│   ├── OrderController.php
│   ├── ProductController.php
│   ├── StaticPageController.php
│   └── UserController.php
│
├── models/
│   ├── Cart.php
│   ├── Category.php
│   ├── Order.php
│   ├── Payment.php
│   ├── Product.php
│   └── User.php
│
├── views/
│   ├── cart/
│   │   ├── cart.php
│   │   └── checkout.php
│   ├── layouts/
│   │   ├── footer.php
│   │   └── header.php
│   ├── products/
│   │   ├── category.php
│   │   ├── home.php
│   │   └── single_product.php
│   ├── static/
│   │   ├── about.php
│   │   ├── donate.php
│   │   ├── privacy_policy.php
│   │   └── terms.php
│   └── user/
│       ├── invoice.php
│       ├── order_details.php
│       ├── order_history.php
│       └── profile.php
│
├── public/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
│
├── config.php
├── db.php
└── index.php









The Core Concept: Reserve First, Pay Later