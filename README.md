# ITCShop - Backend API

This is the **Laravel RESTful API** for ITCShop, a full-stack e-commerce platform built as a Software Engineering project at the **Institute of Technology of Cambodia (ITC)**.

This API supports both customer and admin apps, handling product data, authentication, orders, and platform management. It is dockerized with Nginx, PHP, and MySQL for local development.

---

## ✨ Features

- Role-based authentication (admin & customer)
- Secure RESTful endpoints for customer & admin apps
- OTP verification via email (Mail)
- Cart, wishlist, checkout, coupon, and payment integration
- Admin control for products, categories, orders, coupons, and customers
- Product flag automation (new, flash sale, best selling)
- Docker setup with Nginx, PHP, and MySQL

---

## 📦 Tech Stack

- Laravel 11  
- MySQL  
- Laravel Sanctum (API Auth)  
- Eloquent API Resources  
- Laravel Scheduler  
- Mail (for OTP)  
- Docker (with app, database, nginx)

---

## 🧩 API Overview

### 🛍️ CUSTOMER API

| Controller                 | Purpose                                                                 |
|---------------------------|-------------------------------------------------------------------------|
| AuthController            | Register, Login, Logout                                                 |
| ForgotPasswordController  | Send OTP, verify OTP, reset password                                    |
| ProfileController         | Update name, email, and address                                         |
| CartController            | Add, view, update, and remove cart items                                |
| WishlistController        | Add, view, and remove wishlist items                                    |
| CouponController          | Apply coupon to cart                                                    |
| CheckoutController        | Place order, store billing info, handle payment                         |
| OrderController           | View past orders and order confirmation                                 |
| ProductViewController     | View all products, search, filter by category, flash sale, etc.         |
| PaymentMethodController   | Add/view/delete Stripe cards (via token only, no full card stored)      |

---

### 🧑‍💼 ADMIN API

| Controller           | Purpose                                                                  |
|----------------------|--------------------------------------------------------------------------|
| AuthController       | Admin login and logout                                                   |
| DashboardController  | Show analytics, order stats, and total revenue                           |
| CategoryController   | CRUD for product categories                                              |
| ProductController    | CRUD for products                                                        |
| OrderController      | View all orders and update order status (processing, shipped, etc.)      |
| UserController       | Manage admin/staff accounts                                              |
| CustomerController   | View and manage customer accounts                                        |
| CouponController     | Create, edit, and delete discount coupons                                |

---

## ⚙️ Project Setup with Docker

### 🐳 Start Docker containers

```bash
docker-compose up -d
```

This will start:

- `app` → Laravel (PHP)  
- `mysql` → MySQL database  
- `nginx` → Web server (serves Laravel via port 80)

---

### 📂 Environment Config

Make sure your `.env` contains:

```ini
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=itcshop
DB_USERNAME=root
DB_PASSWORD=secret
```

---

### ⚙️ Run Artisan commands inside the container

```bash
docker exec -it itcshop-app bash
php artisan migrate --seed
```

---

## 🔗 Related Repositories

- [Customer Frontend](https://github.com/Masterly0607/itcshop-customer)  
- [Admin Panel](https://github.com/Masterly0607/itcshop-admin)

---

## 📘 Project Info

This backend powers both the customer and admin interfaces of ITCShop. It’s designed using modular Laravel architecture with secure authentication, scheduling, validations, and scalable structure. Built as part of Year 4 final project at ITC.

---

## 📫 Contact

**Sok Masterly**  
📧 masterlysok@gmail.com  
🌐 [github.com/Masterly0607](https://github.com/Masterly0607)
