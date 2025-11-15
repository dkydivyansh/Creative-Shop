Project E-Commerce Store: Database Structure
This document outlines the database schema for the e-commerce project. The structure is designed to handle physical goods, digital files, and various types of license keys.

1. users Table
Stores customer information and credentials.

|

| Column Name | Data Type | Notes |
| id | INT | Primary Key, Auto Increment |
| email | VARCHAR(255) | Unique Key. Used for login and communication. |
| name | VARCHAR(255) | Full name of the user. |
| phone_number | VARCHAR(20) | User's contact number. |
| country | VARCHAR(100) |  |
| pincode | VARCHAR(20) |  |
| address1 | TEXT | Primary address line. |
| address2 | TEXT | Secondary address line (optional). |
| landmark | VARCHAR(255) |  |
| city | VARCHAR(100) |  |
| state | VARCHAR(100) |  |

2. categories Table
Stores product categories to help with organization.

| Column Name | Data Type | Notes |
| id | INT | Primary Key, Auto Increment |
| name | VARCHAR(255) | Unique Key. e.g., "Software", "Apparel". |
| description | TEXT | A brief description of the category. |

3. products Table
The central catalog for all items available for sale.

| Column Name | Data Type | Notes |
| id | INT | Primary Key, Auto Increment |
| name | VARCHAR(255) | Product name. |
| description | TEXT | Detailed product description. |
| price | DECIMAL(10, 2) | The base selling price. |
| sku | VARCHAR(100) | Unique Key. Stock Keeping Unit. |
| stock | INT | Available inventory count for physical items. |
| type | ENUM(...) | 'file', 'key', 'key_lim', 'physical'. Defines product logic. |
| image | VARCHAR(255) | URL for the main thumbnail image. |
| gallery | TEXT | Comma-separated list of additional image URLs. |
| max_item_per_person | INT | Maximum quantity a single user can order. |
| specs | JSON | Product specifications in JSON format. |
| category_id | INT | Foreign Key to categories.id. |
| discount | TINYINT | Percentage discount (1-100). Default 0. |
| region | VARCHAR(255) | Availability for physical products. Default 'all'. |

4. keys_limited Table
Stores pre-stocked, limited-quantity license keys (for products of type key_lim).

| Column Name | Data Type | Notes |
| id | INT | Primary Key, Auto Increment. |
| product_sku | VARCHAR(100) | Foreign Key to products.sku. |
| key_value | VARCHAR(255) | The actual license key. |
| is_sold | BOOLEAN | 0 = Available, 1 = Sold. Default 0. |
| created_at | TIMESTAMP | When the key was added. |
| expires_at | TIMESTAMP | Optional expiration date for the key. |

5. files Table
Stores information about downloadable file products (for products of type file).

| Column Name | Data Type | Notes |
| id | INT | Primary Key, Auto Increment |
| product_sku | VARCHAR(100) | Unique Key, Foreign Key to products.sku. |
| file_url | VARCHAR(255) | Secure path to the downloadable file. |
| created_at | TIMESTAMP | When the file was uploaded. |

6. orders Table
Records high-level information for every purchase.

| Column Name | Data Type | Notes |
| id | INT | Primary Key, Auto Increment. The Order ID. |
| user_email | VARCHAR(255) | Foreign Key to users.email. |
| customer_name | VARCHAR(255) | Customer's name at the time of order. |
| shipping_address | TEXT | Full shipping address for physical goods. |
| total_amount | DECIMAL(10, 2) | The final amount paid. |
| status | ENUM(...) | 'pending', 'paid', 'delivered', 'shipped', 'cancelled'. |
| order_date | TIMESTAMP | When the order was placed. |

7. payments Table 
Logs every payment attempt associated with an order.

| Column Name | Data Type | Notes |
| id | INT | Primary Key, Auto Increment |
| order_id | INT | Foreign Key to orders.id. |
| transaction_id | VARCHAR(255) | Unique Key. The ID from the payment gateway. |
| payment_gateway | VARCHAR(50) | e.g., "Google Pay", "Razorpay". |
| amount | DECIMAL(10, 2) | The amount that was charged. |
| currency | VARCHAR(10) | e.g., "INR", "USD". |
| status | ENUM(...) | 'succeeded', 'failed', 'pending', 'refunded'. |
| gateway_response | TEXT | Optional: Stores the full gateway response for debugging. |
| created_at | TIMESTAMP | When the payment attempt was made. |

8. order_items Table
Details each specific item within an order.

| Column Name | Data Type | Notes |
| id | INT | Primary Key, Auto Increment |
| order_id | INT | Foreign Key to orders.id. |
| product_sku | VARCHAR(100) | Foreign Key to products.sku. |
| product_type | ENUM(...) | 'file', 'key', 'key_lim', 'physical'. Stored for historical accuracy. |
| price_per_item | DECIMAL(10, 2) | Price at the time of purchase. |
| quantity | INT | Quantity purchased. |
| discount | TINYINT | Discount applied to this item. |
| total | DECIMAL(10, 2) | Final price for this line item (price * quantity - discount). |
| key_id | INT | Foreign Key to keys_limited.id. Nullable. Used only for key_lim products. |

9. user_cart Table
Temporarily holds items a user intends to purchase.

| Column Name | Data Type | Notes |
| id | INT | Primary Key, Auto Increment |
| user_email | VARCHAR(255) | Foreign Key to users.email. |
| product_sku | VARCHAR(100) | Foreign Key to products.sku. |
| quantity | INT |  |