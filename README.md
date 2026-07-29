# 🌾 Okene Centralized Market Price Information System (CMPIS)

![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-Database-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=flat-square&logo=tailwind-css&logoColor=white)
![Chart.js](https://img.shields.io/badge/Chart.js-Data_Viz-FF6384?style=flat-square&logo=chartdotjs&logoColor=white)

A dynamic, role-based Information Management System designed to eliminate market information asymmetry for agricultural workers in Okene Local Government Area. This platform empowers local farmers and traders with real-time commodity pricing, historical trend analytics, and direct peer-to-peer communication.

## 🚀 Key Features

* **Strict Role-Based Access Control (RBAC):** Distinct dashboards and permissions for **Farmers** (Data Consumers), **Traders** (Data Contributors), and **Administrators** (System Controllers).
* **Real-Time Data Visualization:** Integrates Chart.js to map historical price fluctuations based on specific crops and market locations, aiding predictive decision-making.
* **Peer-to-Peer Messaging System:** A custom-built, in-app chat engine featuring instant search, unread message indicators, and active conversation tracking.
* **Trust & Identity Module:** Public trader profiles featuring verified user roles, profile pictures, automated trust ratings (out of 5.0), and direct phone/WhatsApp links.
* **Dynamic Database Management:** Sequential data rendering and dynamic category/crop registration ensuring the UI remains pristine regardless of backend data deletion.
* **Automated PDF Reporting:** Dedicated print-styled CSS algorithms that strip the UI to generate clean, tabular system activity reports.

## 🛠️ Technology Stack

* **Backend:** PHP 8.2 (Vanilla)
* **Database:** MySQL / MariaDB (Relational schema with enforced foreign key constraints)
* **Frontend:** HTML5, Tailwind CSS (via CDN)
* **Scripting & Analytics:** Vanilla JavaScript, Chart.js API

## 📋 Installation & Local Setup

To run this project locally, you will need a local server environment like **XAMPP** or **WAMP**.

1. **Clone the Repository**
   ```bash
   git clone [https://github.com/yourusername/cmpis.git](https://github.com/yourusername/cmpis.git)

2. **Move to Server Directory**

Move the cloned cmpis folder into your local server's root directory (e.g., C:\xampp\htdocs\cmpis).

3. **Database Setup**

Open phpMyAdmin (http://localhost/phpmyadmin).

Create a new database named okene_cmpis.

Import the provided SQL database structure (if you exported it) or run the initialization scripts to build the users, commodities, markets, categories, prices, and messages tables.

4. **Directory Permissions**

Ensure the uploads/ directory has write permissions so users can upload profile pictures successfully.

5. **Launch the Application**

Open your browser and navigate to: http://localhost/cmpis/login.php

🔐 Default Demo Credentials

To explore the different RBAC environments, use the following credentials:

Role Username Password

Administrator admin password123

Trader trader password123

Farmer farmer password123

(Note: In a production environment, all passwords are cryptographically hashed using PHP's password_hash() bcrypt algorithm).

🧠 System Architecture

This application utilizes a custom Three-Tier Architecture:

1. Presentation Layer: Fully responsive Tailwind CSS interfaces that dynamically adapt based on $_SESSION['role'].

2. Application Layer: Procedural PHP handling business logic, file uploading securely with MIME-type verification, and dynamic SQL query construction.

3. Data Layer: A highly normalized MySQL database utilizing ON DELETE CASCADE relationships to prevent orphaned records.

👨‍💻 Author

Gabriel

Full-Stack Software Engineer & Cybersecurity Enthusiast

Passionate about building secure, scalable, and impactful digital infrastructure.

This project was originally conceptualized as an academic solution and has been scaled into a portfolio SaaS demonstration.
