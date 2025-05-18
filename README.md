This repository contains a PHP-based Blood Donation Management System developed as the final group project for a Master’s-level Fundamentals of Database Systems course. The application provides end-to-end management of donors, appointments, campaigns, blood inventories, transfusion requests and multi-role user dashboards (Donor, Staff, Hospital, Admin).

## Features

- **User Management \& Authentication**
Role-based access control for Donor, Staff, Hospital and Admin users with secure login and registration.
- **Donor Registration \& Profiles**
Donors create and update profiles, view appointment history and upcoming bookings.
- **Appointment Scheduling \& Tracking**
Book, cancel or complete donation appointments; real-time status updates and reminders.
- **Campaign Management**
Admin can create, list and delete blood donation campaigns; donors can register for available campaigns.
- **Blood Inventory Control**
Staff dashboard to add, view and monitor blood units by group, storage/expiration dates and minimum thresholds.
- **Transfusion Requests**
Hospitals submit and track blood transfusion requests; Admin/staff approve and fulfill requests.
- **Patient Records**
Admin adds patient profiles with medical details and transfusion needs.
- **Donation Logging \& Testing**
Record completed donations, log health conditions and blood test results.
- **Automated Statistics**
Daily aggregation of key metrics (inventory levels, usage, shortages, campaign performance) via stored procedures and scheduled event.


## Technologies

- PHP 7.x
- MySQL / MariaDB
- Apache (XAMPP/LAMP)
- HTML5, CSS3, JavaScript
- SQL (schema, stored procedures, event scheduler)


## Repository Structure

```
blood-donation-management/
├── app/
│   ├── backend/
│   │   ├── auth.php
│   │   ├── db_connect.php
│   │   ├── functions.php
│   │   ├── create_admin.php
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── admin_dashboard.php
│   │   ├── donor_dashboard.php
│   │   ├── hospital_dashboard.php
│   │   ├── donor_campaigns.php
│   │   ├── manage_campaigns.php
│   │   ├── cancel_appointment.php
│   │   ├── process_appointment.php
│   │   ├── add_donor.php
│   │   ├── add_patient.php
│   │   ├── add_donation.php
│   │   ├── add_blood_test.php
│   │   └── all other php files
│   └── public/
│       ├── index.html
│       ├── add_donor.html
│       └── schedule_appointment.html
├── blood_donation.sql
└── style.css

```


## Installation \& Setup

1. Clone the repository

```bash
git clone https://github.com/yourusername/blood-donation-management.git
cd blood-donation-management
```

2. Copy project into your web server’s document root (e.g., `htdocs` for XAMPP)
3. Create a new MySQL database (e.g., `blood_donation`)
4. Import **blood_donation.sql** via phpMyAdmin or CLI:

```bash
mysql -u root -p blood_donation < blood_donation.sql
```

5. Update database credentials in **backend/config.php**
6. Start Apache \& MySQL, then browse to

```
http://localhost/blood-donation-management/public/index.html
```


## Database Setup

- **Schema**: 15 tables (`user`, `role`, `user_role`, `donor`, `appointment`, `campaign`, `campaign_donor`, `donation`, `blood_test`, `blood_inventory`, `patient`, `hospital`, `staff`, `transfusion_request`, `statistics`)
- **Stored Procedures**:
    - `schedule_appointment`
    - `update_blood_inventory`
    - `update_statistics`
- **Event Scheduler**:
    - `daily_statistics_update` (aggregates and refreshes statistics each day)


## Usage

- **Register** as Donor, Staff or Hospital via the unified registration form
- **Login** to access your role’s dashboard
- **Admin** (default `admin@test.com` / password set in SQL file) can manage all entities, campaigns and view system-wide stats
- **Donor** can schedule appointments, view history, join campaigns
- **Staff** can update blood inventory and run tests
- **Hospital** can add patients and request blood units


## Team Members

- Vijayalakshmi Talla – 11711703
- Aamani Chamarthi – 11703676
- Vamshi Kalyan Yerramilli – 11655488
- Abhiram Yashwanth Pusarla – 11712109
- Amani Peddolla – 11743144
- Harshith Kumar Pappula – 11646762


