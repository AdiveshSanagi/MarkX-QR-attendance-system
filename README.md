# 📌 **QR-Based Attendance System – PHP | MySQL | QR Code | XAMPP**
# version 0.1 - v0.1

A smart and efficient QR-based attendance tracking system built using **PHP, MySQL, JavaScript, and QR Code technology**.
This system allows **teachers** to generate QR codes for classes and **students** to scan and mark attendance instantly.

## 🚀 **Features**

### 🔐 Authentication

* Student login & signup
* Teacher login & signup
* Secure session handling
### 🧑‍🏫 Teacher Module

* Teacher dashboard
* Generate QR code for each class/session
* View student attendance
* Manage class timetable
* Save and update subject schedules
### 🧑‍🎓 Student Module

* Student login
* Scan QR code using camera
* View personal attendance report
* Access personal timetable
* Profile details
### 🖥️ Admin / System Features

* Database integration with MySQL
* Secure QR code generation
* Prevents duplicate attendance entries
* Clean UI for scanning & logging

---
## 🛠️ **Tech Stack**

| Component | Technology                               |
| --------- | ---------------------------------------- |
| Frontend  | HTML5, CSS3, JavaScript                  |
| Backend   | PHP (Core PHP)                           |
| Database  | MySQL / phpMyAdmin                       |
| Server    | XAMPP / Apache                           |
| QR Code   | PHP QR Code Library / JavaScript Scanner |
| Tools     | Git, GitHub, VS Code                     |

---
## 📂 **Project Folder Structure**

```
QR/
 ├── auth_login.php
 ├── curriculum_auth.php
 ├── curriculum_portal.php
 ├── curriculum_timetable.php
 ├── database.sql
 ├── db.php
 ├── generate_qr.php
 ├── get_attendance.php
 ├── get_my_attendance.php
 ├── index.php
 ├── login.php
 ├── logout.php
 ├── personal_timetable.php
 ├── qr_scan.php
 ├── register.php
 ├── scan.php
 ├── script.js
 ├── student_login.php
 ├── student_profile.php
 ├── student_signup.php
 ├── style.css
 ├── teacher_auth.php
 ├── teacher_dashboard.php
 ├── teacher_login.php
 ├── teacher_register.php
 ├── teacher_signup.php
 └── timetable_save.php
```
---
## ⚙️ **How to Run Locally**

### 1️⃣ Install Requirements

* Install **XAMPP**
* Start **Apache** & **MySQL**
### 2️⃣ Clone Repository

```
git clone https://github.com/AdiveshSanagi/MarkX-QR-attendance-system.git
```
### 3️⃣ Setup Database

* Open **phpMyAdmin**
* Create a new database (example: `attendance_db`)
* Import the file:

```
database.sql
```
### 4️⃣ Move Project to XAMPP

Place the project folder inside:

```
C:\xampp\htdocs\
```
### 5️⃣ Run the application

Open browser:

```
http://localhost/QR/
```
---

## 📸 **Screenshots (Add your images)**

> You can upload images in `assets/` folder and add them like:
> `![Login Page](assets/login.png)`

* Login Page
* Dashboard
* QR Scanner
* Attendance Report
* Timetable
---
## 🧪 **Future Enhancements**

* Admin Panel
* Email notifications
* Biometric / RFID integration
* Mobile App version
* Advanced analytics dashboard

---
## 🤝 **Contributing**
Pull requests are welcome!
For major changes, please open an issue to discuss what you want to modify.
---
## 📄 **License**

This project is open-source and available under the **MIT License**.
---
## 👨‍💻 **Author**

**Adivesh Sanagi**
GitHub: [https://github.com/AdiveshSanagi](https://github.com/AdiveshSanagi)
UI/UX Designer | Full-Stack Developer
