# Event Manager – Custom Drupal 10 Module

## Overview
The Event Manager module is a custom Drupal 10 module that allows administrators to configure events and users to register for those events.  
It supports event configuration, event registration with validation, email notifications, and an admin reporting interface with CSV export.

---

## Installation Steps

1. Copy the module to the Drupal custom modules directory:
/modules/custom/event_manager

2. Enable the module:
- Go to **Admin → Extend**
- Enable **Event Manager**
- Click **Install**

3. Assign permissions:
- Go to **Admin → People → Permissions**
- Under **Event Manager**, enable:
  - **View event registrations**
- Click **Save permissions**

4. Clear cache:
- Go to **Admin → Configuration → Development → Performance**
- Click **Clear all caches**

---

## URLs of Forms and Admin Pages

### Admin Pages

#### Event Configuration Page
Used to create and manage events.

http://localhost/drupal/drupal-10.0.0/admin/event-config

#### Event Manager Settings Page
Used to configure admin email notifications.

http://localhost/drupal/drupal-10.0.0/admin/config/event-manager/settings

#### Event Registration Listing & CSV Export
Used to view registrations, filter data, and export CSV.

http://localhost/drupal/drupal-10.0.0/admin/event-registrations

---

### User Page

#### Event Registration Form
Used by users to register for available events.

http://localhost/drupal/drupal-10.0.0/event/register

---

## Database Tables Explanation

### 1. `event_config`
Stores event configuration details created by the administrator.

| Column Name       | Description |
|-------------------|-------------|
| id                | Primary key |
| event_name        | Name of the event |
| category          | Event category |
| event_date        | Event date |
| reg_start_date    | Registration start date |
| reg_end_date      | Registration end date |
| created           | Timestamp of creation |

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
| event_id      | Foreign key referencing `event_config.id` |
| event_date    | Event date |
| created       | Registration timestamp |

---

## Validation Logic

- Duplicate registrations are prevented using **email + event date** validation.
- Email format is validated using Drupal Form API.
- All form fields are mandatory.
- Registration is allowed **only within the configured registration start and end dates**.
- Clear, user-friendly validation messages are shown on errors.

---

## Email Notification Logic

- Implemented using **Drupal Mail API**.
- On successful registration:
  - A confirmation email is sent to the user.
  - A notification email is sent to the admin (if enabled).
- Admin email address and notification toggle are configurable using **Config API**.
- No hard-coded email addresses are used.
- Email delivery on localhost depends on SMTP/mail configuration.

---

## Security & Permissions

- Admin registration listing is protected by a **custom permission**:
  - **View event registrations**
- Only authorized roles can:
  - View registrations
  - Export CSV files

---

## Features Summary

- Custom event configuration form
- AJAX-based dependent dropdowns (Category → Date → Event)
- Registration window enforcement
- Duplicate registration prevention
- Email notifications
- Admin reporting dashboard
- CSV export functionality
- Drupal 10 best practices followed

---

## Author
Developed as part of a **Drupal 10 Custom Module Assignment**.

