# neresStore - Project Report

**Course:** EWA408510 – E-Commerce and Web Application  
**Institution:** University of Lay Adventists of Kigali (UNILAK)  
**Faculty:** Computing and Information Sciences  
**Academic Year:** 2025-2026  
**Project Type:** Final Examination (Project-Based)

---

## 1. Introduction

**neresStore** is a modern e-commerce web application designed for a local electronics business in Rwanda. The platform enables customers to browse electronic products, manage a shopping cart, place orders, and receive order confirmations online. The store sells smartphones, laptops, audio equipment, accessories, and TVs/monitors at competitive prices in Rwandan Francs (RWF).

## 2. Problem Statement

Local electronics retailers in Rwanda face challenges reaching customers beyond physical store locations. Many potential buyers cannot visit stores in person, and businesses lack an online presence to showcase inventory, process orders, and manage customer data efficiently. There is a need for a reliable, user-friendly e-commerce platform tailored to the Rwandan market.

## 3. Objectives

- Design and develop a responsive e-commerce website for an electronics store
- Implement product browsing with categories and search functionality
- Build a shopping cart system with quantity management
- Create a complete checkout and order confirmation workflow
- Integrate a MySQL database for persistent data storage
- Containerize the application using Docker
- Implement CI/CD automation with GitHub Actions
- Deploy the application for online accessibility

## 4. System Features

| Feature | Description |
|---------|-------------|
| Homepage | Hero section, category navigation, featured products |
| Product Listing | Filter by category, search by name/description |
| Product Details | Full description, price, stock status, add to cart |
| Shopping Cart | Add/remove items, update quantities, calculate totals |
| Checkout | Customer form (name, email, phone, address, city) |
| Order Confirmation | Order number, item summary, delivery information |
| Responsive Design | Mobile-friendly layout across all pages |

## 5. Technologies Used

- **PHP 8.2** – Server-side logic and page rendering
- **MySQL 8.0** – Relational database for products, customers, orders
- **HTML5 / CSS3** – Semantic markup and modern responsive styling
- **JavaScript** – Client-side interactivity (cart quantities, mobile nav)
- **Docker** – Application containerization
- **Docker Compose** – Multi-service orchestration (web + database)
- **GitHub Actions** – Continuous Integration and Deployment pipeline
- **Apache** – Web server (via PHP-Apache Docker image)

## 6. System Architecture

```
┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│   Browser   │────▶│  Apache/PHP  │────▶│   MySQL     │
│  (Client)   │◀────│  (Web Server)│◀────│  (Database) │
└─────────────┘     └──────────────┘     └─────────────┘
                           │
                    ┌──────┴──────┐
                    │   Docker    │
                    │  Container  │
                    └─────────────┘
```

**Database Schema:**
- `categories` → `products` (one-to-many)
- `customers` → `orders` (one-to-many)
- `orders` → `order_items` (one-to-many)
- `products` → `order_items` (one-to-many)

**Session Management:** Shopping cart data is stored in PHP sessions until checkout, when orders are persisted to the database.

## 7. Screenshots

> *Add screenshots of the following pages when submitting:*
> 1. Homepage
> 2. Products listing page
> 3. Product details page
> 4. Shopping cart
> 5. Checkout page
> 6. Order confirmation page
> 7. Mobile responsive view

## 8. GitHub Repository Link

**Repository:** `[Add your GitHub URL here]`

## 9. Deployment Link

**Live URL:** `[Add your deployment URL here]`  
**Local Docker:** `http://localhost:8080`

## 10. CI/CD Description

The CI/CD pipeline is defined in `.github/workflows/ci-cd.yml` and triggers on every push to `main`:

1. **Test Job:**
   - Checks out source code
   - Validates PHP syntax on all `.php` files
   - Builds the Docker image
   - Starts services with `docker compose up`
   - Waits for MySQL to be healthy
   - Runs health check against `/health.php`
   - Tears down containers

2. **Deploy Job** (runs only on `main` branch after tests pass):
   - Outputs deployment readiness notification
   - Can be extended to deploy to cloud hosting (Railway, Render, etc.)

## 11. Docker Setup

**Dockerfile:** Uses `php:8.2-apache` base image with PDO MySQL extensions.

**docker-compose.yml:** Defines two services:
- `web` – PHP application on port 8080
- `db` – MySQL 8.0 with automatic schema initialization

**Evidence of execution:**
```bash
docker compose up -d
curl http://localhost:8080/health.php
# {"status":"ok","app":"neresStore","database":"connected",...}
```

## 12. Challenges Encountered

- **Database initialization timing:** MySQL takes time to start; solved with Docker health checks and `depends_on` conditions.
- **Session cart persistence:** Ensured cart data survives page navigation using PHP sessions.
- **Stock management:** Implemented stock validation during cart updates and order creation with database transactions.
- **Responsive design:** Created a mobile-first CSS layout with collapsible navigation for small screens.

## 13. Future Work

- Payment gateway integration (MTN Mobile Money, Airtel Money)
- Admin dashboard for product and order management
- User authentication and order history
- Email notifications for order confirmations
- Product image uploads
- AI-powered product recommendations
- Analytics dashboard for sales tracking

## 14. Conclusion

The neresStore e-commerce platform successfully meets all project requirements including responsive UI, product management, shopping cart, checkout process, database integration, Docker containerization, and CI/CD automation. The application provides a solid foundation for a local electronics business to expand online sales across Rwanda.

---

*Submitted for EWA408510 Final Examination – UNILAK, 2025-2026*
