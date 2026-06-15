Name: MBonyishema olivier
regnumber: 22744/2023

# neresStore - Electronics E-Commerce Platform

A modern e-commerce web application for selling electronics in Rwanda. Built with **PHP** and **MySQL** for the EWA408510 E-Commerce and Web Application final examination at UNILAK.

## Live Demo

- **Deployment URL:** https://orchid-dragonfly-827212.hostingersite.com/
- **GitHub Repository:** https://github.com/mbonyishemaolivier-eng/electronic

## Features

- Responsive, mobile-friendly UI
- Product listing with category filtering and search
- Product details page with quantity selector
- Shopping cart (add, remove, update quantities)
- Checkout with customer details and order summary
- Order confirmation page
- **Admin panel** with login, product CRUD, image upload
- **Statistics dashboard** (orders, revenue, top products)
- **Activity log** tracking store and admin actions
- **AI Shopping Assistant** chatbot for product help and recommendations
- **AI Product Recommendations** based on browsing and purchase patterns
- **AI Description Generator** for admin product creation
- MySQL database for products, customers, and orders
- Docker containerization
- CI/CD pipeline with GitHub Actions

## Tech Stack

| Component | Technology |
|-----------|------------|
| Backend | PHP 8.2 |
| Database | MySQL 8.0 |
| Frontend | HTML5, CSS3, JavaScript |
| Container | Docker + Docker Compose |
| CI/CD | GitHub Actions |

## Admin Panel

**URL:** `http://localhost:8080/admin/login.php`

| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `admin123` |

### Admin Features

- **Dashboard** — Statistics: products, orders, revenue, top sellers, activity chart
- **Products** — Create, edit, delete products with image upload
- **Activity Log** — View all store activities (orders, cart actions, product views, admin actions)

> Change the default admin password after first login for security.

---

### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installed

### Run the Application

```bash
# Clone the repository
git clone <your-repo-url>
cd neresStore

# Start all services
docker compose up -d

# Wait ~30 seconds for MySQL to initialize, then open:
# http://localhost:8080
```

### Stop the Application

```bash
docker compose down
```

### Rebuild after changes

```bash
docker compose up -d --build
```

## Manual Setup (without Docker)

### Requirements

- PHP 8.0+ with PDO MySQL extension
- MySQL 8.0+
- Apache or Nginx web server

### Steps

1. Import the database:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

2. Configure database connection in `config/database.php` or set environment variables:
   ```
   DB_HOST=localhost
   DB_NAME=neresstore
   DB_USER=root
   DB_PASS=your_password
   ```

3. Point your web server document root to the project folder.

4. Open `http://localhost` in your browser.

## Project Structure

```
neresStore/
├── assets/
│   ├── css/style.css       # Main stylesheet
│   ├── js/main.js          # Client-side JavaScript
│   └── images/             # Product images & icons
├── config/
│   ├── app.php             # App configuration
│   └── database.php        # Database connection
├── database/
│   └── schema.sql          # Database schema & seed data
├── includes/
│   ├── init.php            # Session & bootstrap
│   ├── header.php          # Site header
│   ├── footer.php          # Site footer
│   ├── functions.php       # Helper functions
│   └── cart.php            # Cart session logic
├── .github/workflows/
│   └── ci-cd.yml           # CI/CD pipeline
├── index.php               # Homepage
├── products.php            # Product listing
├── product.php             # Product details
├── cart.php                # Shopping cart
├── cart-action.php         # Cart operations handler
├── checkout.php            # Checkout form
├── order-success.php       # Order confirmation
├── health.php              # Health check endpoint
├── Dockerfile
├── docker-compose.yml
└── README.md
```
![Homepage](./shema/exams/homena.png)
![Homepage](./shema/exams/homepr.png)
![product](./shema/exams/product.png)
![Homepage](./shema/exams/produ.png)
![AI](./shema/exams/ai_inter.png)
![analyse](./shema/exams/analys.png)
![admin](./shema/exams/adminpro.png)



## CI/CD Pipeline

The GitHub Actions workflow (`.github/workflows/ci-cd.yml`) automatically:

1. Validates PHP syntax on all files
2. Builds the Docker image
3. Starts the application with Docker Compose
4. Runs health checks against the live app
5. Reports deploy readiness on push to `main`

## Database Tables

- `categories` - Product categories
- `products` - Electronics inventory
- `customers` - Customer information
- `orders` - Order records
- `order_items` - Individual order line items

## Health Check

```bash
curl http://localhost:8080/health.php
```

Expected response:
```json
{"status":"ok","app":"neresStore","database":"connected","timestamp":"..."}
```

## Author

UNILAK - Faculty of Computing and Information Sciences  
Course: EWA408510 – E-Commerce and Web Application  
Academic Year: 2025-2026

## License

Academic project for educational purposes.
