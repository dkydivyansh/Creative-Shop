Project E-Commerce Store: Server Routes & Functions
This document outlines the primary server routes and the logic required to handle them. The routes are categorized by their function, such as handling products, user accounts, and the checkout process.

Authentication Model: User authentication and session management are handled by an external, dedicated authentication server. This server is responsible for registration, login, and user data management. The e-commerce server will protect routes by validating access tokens (e.g., JWT) provided by the authentication server.

1. Product & Catalog Routes
These routes are for browsing and viewing products. They are publicly accessible.

|

| Path | Method | Description & Functions |
| / | GET | Home Page. Fetches a curated list of products (e.g., featured, new arrivals) from the products table to display to the user. |
| /category/<category_name> | GET | Category Page. Fetches all products belonging to a specific category. Requires joining products and categories tables. Implements pagination for large categories. |
| /<product_sku> | GET | Product Detail Page. Fetches all details for a single product using its unique SKU. Displays name, description, price, images, gallery, and specifications (specs). The "Add to Cart" button will be prominent here. |

2. Authentication Handling
These routes describe how the e-commerce server interacts with the external authentication service.

| Path | Method | Description & Functions |
| /register | GET | Registration Redirect. This route's sole purpose is to redirect the user to the registration page on the external authentication server. |
| /login | GET | Login Redirect. This route immediately redirects the user to the login page on the external authentication server. |
| /auth/callback | GET | Authentication Callback. This is the redirect URI for the external auth server. After a user logs in or registers, they are redirected here with a token. This route's function is to receive the token, validate it, and establish the user's authenticated state on the e-commerce site (e.g., by setting a secure cookie). |
| /auth/logout | GET | Logout Redirect. This route clears the local session/cookie and then redirects the user to the external auth server's logout endpoint to ensure a full logout. |

3. User Account Routes (Requires Valid Token)
These routes are accessible only to authenticated users. Your server must validate the user's access token with the authentication server before rendering these pages.

| Path | Method | Description & Functions |
| /profile | GET, POST | User Profile Page. GET: Fetches user data from the external auth server API. POST: Sends update requests to the external auth server API. |
| /orders | GET | Order History Page. Fetches all orders from the local orders table associated with the validated user's email/ID. Displays a list of orders with their ID, date, total amount, and status. |
| /orders/<orderid> | GET | Specific Order Details Page. Fetches detailed information for a single order. This is where users access their digital goods. Functions: Get License Key: For key_lim or key products, this button retrieves the associated key.Download File: For file products, this button generates a secure, temporary download link. |
| /order/<orderid>/invoice | GET | Invoice Page. Generates a printable HTML or PDF invoice for a specific order. |

4. Cart & Checkout Routes
These routes manage the shopping cart and the payment process.

| Path | Method | Description & Functions |
| /cart | GET | Cart Page. Displays all items from the user_cart table associated with the validated user. Allows users to update quantities or remove items. |
| /checkout | GET, POST | Checkout Page. GET: Displays the final order summary and payment options. POST: Initiates the payment process. On success, it creates a new entry in orders, copies cart items to order_items, clears the user_cart, and updates stock. |

5. Informational & Static Routes
These routes provide general information about the store.

| Path | Method | Description & Functions |
| /privacy-policy | GET | Displays the website's privacy policy. |
| /terms-and-conditions | GET | Displays the terms and conditions of service. |
| /about | GET | Displays information about the company or project. |
| /donate | GET, POST | Donation Page. GET: Shows a page where users can donate. POST: Processes the donation payment. |

6. Suggestions for Additional Routes
Here are a few important routes and functionalities to consider:

| Path / Feature | Description |
| /api/payment-callback | (Crucial) An API endpoint (webhook) for your payment gateway to send asynchronous notifications about payment status (e.g., success, failure). |
| /search | A page to display search results when a user looks for products. |
| /contact | A page with a contact form for customer inquiries. |
| API for Cart Actions | Instead of full page reloads, consider API endpoints like /api/cart/add and /api/cart/update that can be called with JavaScript for a smoother user experience. |
| API Token Refresh | A mechanism to silently refresh access tokens in the background to keep the user logged in without interruption. This logic would likely be handled via JavaScript. |