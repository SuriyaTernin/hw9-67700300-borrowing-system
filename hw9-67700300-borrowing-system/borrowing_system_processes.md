# Borrowing System Processes

This document outlines the key processes and steps in the Equipment Borrowing System.

## Overview
The system is a web-based application for managing equipment borrowing and returning. It supports two types of users: regular users and administrators. The system uses PHP with PDO for database interactions and MySQL as the database backend.

## Database Schema
- **users**: Stores user information (username, password, role)
- **categories**: Equipment categories
- **equipment**: Equipment details (name, description, category, quantity, image)
- **borrowings**: Borrowing records (user_id, equipment_id, quantity, dates, status)

## User Processes

### 1. Login
- User enters username and password on login.php
- System verifies credentials using password_verify
- Redirects to user_dashboard.php for users or admins.php for admins

### 2. View Available Equipment
- User accesses user_dashboard.php
- System fetches equipment with quantity > 0, joined with categories
- Displays list of available equipment with categories

### 3. Borrow Equipment
- User selects equipment items and specifies quantities
- Specifies borrow date (cannot be in the past)
- System validates:
  - Items selected
  - Date not past
  - Sufficient quantity in stock
- If valid:
  - Inserts record into borrowings table
  - Decreases equipment quantity
- Uses transactions for data integrity

### 4. View Borrowing History
- User views their borrowing records
- Shows borrowed and returned items with dates and equipment details

### 5. Return Equipment
- User clicks return on a borrowed item
- System updates borrowing status to 'returned'
- Sets return_date to current date
- Increases equipment quantity back
- Uses transactions for data integrity

## Admin Processes

### 1. Login
- Same as user login process

### 2. User Management (admins.php)
- View all users with roles
- Add new users
- Edit user details (username, role)
- Delete users

### 3. Category Management (categorie.php)
- View all categories
- Add new categories
- Edit category names
- Delete categories

### 4. Equipment Management (equipment.php)
- View all equipment with categories and quantities
- Add new equipment (name, description, category, quantity, image)
- Edit equipment details
- Delete equipment

### 5. Borrowing Overview (borrowing_dashboard.php)
- View all borrowing records across all users
- Monitor current borrowings and returns

### 6. Reports (report_borrowing.php)
- Generate reports on borrowing activities
- Analyze usage statistics

### 7. Calendar View (calendar.php)
- View borrowing schedule on a calendar interface
- Plan and visualize borrowing periods

## Security Features
- Session-based authentication
- Role-based access control (user vs admin)
- Password hashing with bcrypt
- Input validation and sanitization
- Transactional operations to prevent data inconsistencies

## Additional Features
- Multi-language support (Thai interface)
- Responsive design with Tailwind CSS
- Alert notifications using jQuery Confirm
- Image uploads for equipment
- AJAX for dynamic content loading
