# Event Manager – Custom Drupal 10 Module

## Overview
The Event Manager module is a custom Drupal 10 module that allows administrators to configure events and users to register for those events.  
It supports event configuration, event registration with validation, email notifications, and an admin reporting interface with CSV export.

---

## Installation Steps

1. Copy the module to the following directory:

2. Enable the module:
- Go to **Admin → Extend**
- Enable **Event Manager**
- Click **Install**

3. Clear cache:
Enable:
- **View event registrations**

---

## URLs of Forms and Admin Pages

### Event Configuration Page (Admin)
Used to create and manage events.

---

### Email Configuration Page (Admin)
Used to configure admin email notifications.

---

### Admin Registration Listing & CSV Export
Used to view registrations, filter data, and export CSV.

---

## Database Tables Explanation

### 1. `event_config`
Stores event configuration details created by the admin.

| Column Name        | Description |
|--------------------|-------------|
| id                 | Primary key |
| event_name         | Name of the event |
| category           | Event category |
| event_date         | Event date |
| reg_start_date     | Registration start date |
| reg_end_date       | Registration end date |
| created            | Timestamp of creation |

---

### 2. `event_registration`
Stores user registration details.

| Column Name   | Description |
|--------------|-------------|
| id            | Primary key |
| full_name     | Participant name |
| email         | Participant email |
| college_name  | College name |
| department    | Department |
| event_id      | Foreign key referencing event_config |
| event_date    | Event date |
| created       | Registration timestamp |

---

## Validation Logic

- Duplicate registrations are prevented using:
- Email format is validated using Drupal Form API.
- All text fields are required.
- Registration is allowed **only within the configured registration start and end dates**.
- User-friendly validation messages are displayed on errors.

---

## Email Notification Logic

- Implemented using **Drupal Mail API**.
- On successful registration:
- A confirmation email is sent to the user.
- A notification email is sent to the admin (if enabled).
- Admin email and notification toggle are configurable using **Config API**.
- No hard-coded email addresses are used.
- On localhost, email delivery depends on SMTP configuration.

---

## Security & Permissions

- Admin registration listing is protected by a **custom permission**:
- Only authorized roles can access admin reports and CSV export.

---

## Features Summary

- Custom event configuration form
- AJAX-based dependent dropdowns
- Registration window enforcement
- Duplicate registration prevention
- Email notifications
- Admin reporting dashboard
- CSV export functionality
- Clean Drupal 10 coding standards

---

## Author
Developed as part of a Drupal 10 custom module assignment.
