# Emirates Park Zoo & Resort - Ticketing Platform

> A production-grade, headless ticketing system built with Laravel 11, Filament v3, and CyberSource.

## 🦒 Overview

This repository contains the backend API and Admin Panel for the Emirates Park Zoo ticketing system. It handles product management, VIP experiences, bookings, payments, and operational workflows.

## 🚀 Key Features

### 🎟️ Booking & Ticketing
- **Multi-Product Support**: Create tickets, experiences, and bundles.
- **VIP Logic**: Advanced configuration for "Breakfast with Giraffes" (Time Slots + Food Preferences).
- **Capacity Management**: Real-time inventory tracking and locks to prevent double-booking.

### 💳 Payments
- **CyberSource Integration**: Secure Acceptance Webhook implementation (HMAC-SHA256).
- **Idempotency**: Prevents duplicate processing of payments.
- **Refund Handling**: Manage refunds directly via the Admin Panel.

### 👥 CRM & Leads
- **Lead Capture**: Automatically tracks abandoned checkouts via `utm` parameters.
- **Attribution**: Know exactly which campaign (Google/Instagram) drove a booking.
- **Recovery**: Automated jobs to email users who dropped off.

### 📅 Operations
- **Daily Manifesto**: Calendar view for Ops/Kitchen to prepared for VIP guests.
- **Notifications**: Automated emails to Sales, Operations, and Kitchen based on booking type.
- **WhatsApp**: Integrated notifications for customer confirmations.

---

## 📚 Documentation

Detailed documentation is available in the repo:

- **[API Integration Guide](api_integration_guide.md)**: For Frontend/WordPress developers.
- **[Production Hardening (UAE)](production_hardening.md)**: Deployment checklist & security.
- **[Walkthrough](walkthrough.md)**: Comprehensive tour of the system features.

---

## 🛠️ Stack & Requirements

- **PHP**: 8.2+
- **Database**: PostgreSQL
- **Framework**: Laravel 11
- **Admin**: FilamentPHP v3
- **Queue**: Redis
- **Container**: Docker (Easypanel ready)

## 📦 Deployment

This project includes a `Dockerfile` ready for deployment on Easypanel or any Docker-based host.

1. **Clone & Configure**
   ```bash
   cp .env.example .env
   # Set DB_ and CYBERSOURCE_ credentials
   ```

2. **Run with Docker**
   ```bash
   docker-compose up -d --build
   ```

3. **Install Dependencies**
   ```bash
   docker-compose exec app composer install --optimize-autoloader
   docker-compose exec app php artisan migrate
   ```

## 🔒 Security

- **Login Audits**: All staff logins are logged to `login_logs`.
- **RBAC**: Strict Role-Based Access Control (Super Admin, Ops, Sales, Restaurant).
- **Validation**: All API inputs are strictly validated (especially VIP options).

---

&copy; 2026 Emirates Park Zoo & Resort. All rights reserved.
