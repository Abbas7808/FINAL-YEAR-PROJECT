# 🏥 Med Nova: Advanced Digital Healthcare Platform
> **Empowering Proactive Healthcare through AI Integration & Centralized Operations**

---

## 🌟 Executive Overview
Med Nova is a comprehensive **Healthcare-as-a-Service (SaaS)** ecosystem designed to bridge the gap between fragmented medical services and patient needs. Built as a Final Year Project (FYP), it focuses on digitizing hospital workflows while introducing proactive "Smart Health" modules like AI-driven scanning and intelligent emergency response.

### 🚩 Problem Statement
Traditional healthcare systems in developing regions (like Kohat, KPK) suffer from:
- **Fragmented Data**: Patient records spread across disconnected clinics.
- **Reactive Care**: Patients only seek help after symptoms become severe.
- **Emergency Delays**: Lack of centralized SOS and triage systems.
- **Specialized Gaps**: Difficulties in managing niche fields like Blood Donation and Psychology.

**Med Nova solves this by providing a unified, high-performance platform for both providers and patients.**

---

## 🛠️ Technical Engineering Stack

### **Architecture: PHP MVC (Model-View-Controller)**
The backend is structured using a custom MVC architecture to ensure security, scalability, and code maintainability—vital for professional-grade healthcare software.

| Layer | Technology | Role |
| :--- | :--- | :--- |
| **Frontend** | HTML5, JavaScript (ES6+), CSS3 | Core structure, dynamic UI logic, and client-side interactions. |
| **Styling** | **Bootstrap 5 & Tailwind CSS** | A hybrid system using Bootstrap for grid stability and Tailwind for premium custom aesthetics. |
| **Backend** | **Vanilla PHP 7.4+** | High-performance routing, controller logic, and model-based data handling. |
| **Database** | **MySQL 8.0** | Relational data management for patients, doctors, and records. |
| **Deployment** | XAMPP / Apache | Local development and staging environment. |

---

## 📂 Core Product Modules

### 1. 🏥 Project Operations (Hospital Admin)
- **Centralized Dashboard**: Real-time stats for hospitals and clinics.
- **Smart Booking**: A seamless Search → Doctor Profile → Appointment workflow.

### 2. 🧠 Intelligence Services (AI Scan & Diet)
- **AI Health Scanner**: A rule-based risk assessment tool providing instant health scoring.
- **Smart Diet Planner**: Dynamic nutrition generation based on clinical patient profiles.

### 3. 🚨 Emergency Infrastructure (SOS & Triage)
- **Satellite SOS Hub**: High-stakes interface for satellite-linked emergency alerts.
- **Fast Triage AI**: Hospital-grade prioritization algorithm for emergency patient routing.

### 4. 🩸 Specialized Care (Blood & Psychology)
- **Blood Donation Registry**: Network-wide registry for blood availability and donor tracking.
- **Psychology Portal**: Specialized workflow for therapeutic counseling and mental health tracking.

---

## 🎓 FYP Defense Guidance (Viva Voce Prep)

### **Key Presentation Tips**
1.  **Start with the "Why"**: Don't just show the code; explain the healthcare problem you are solving in Kohat/Pakistan.
2.  **Highlight the Hybrid UI**: Mention that the UI is "Premium Floating" (Glassmorphism), moving away from generic, dated medical portals.
3.  **Stress the MVC**: Examiners love structured code. Point out the `controllers/` and `models/` folders to show architectural competence.

### **Anticipated Viva Questions & Technical Answers**

#### **Q1: Why did you choose a custom PHP MVC instead of a CMS like WordPress?**
> **Answer:** CMS platforms are generic. Healthcare requires custom business logic (RBAC), specific relational schemas (MySQL), and high performance which only a custom MVC architecture can provide. It also demonstrates my ability to build systems from the ground up.

#### **Q2: How does the AI Health Scanner actually work?**
> **Answer:** It uses a rule-based inference engine. By processing user-selected "Aspects" (Symptoms/Factors), it calculates a weighted risk score, which is then rendered through a dynamic frontend dashboard to simulate real-time analysis.

#### **Q3: What is the significance of the Hybrid CSS (Bootstrap + Tailwind) approach?**
> **Answer:** Bootstrap provides the "Reliability" (Grid, Modals, Forms) that medical apps need, while Tailwind allows for "Luxury Aesthetics" (Custom Gradients, Glassmorphism, Animations) that set Med Nova apart as a modern platform.

#### **Q4: How do you ensure data integrity across your modules?**
> **Answer:** Through relational database constraints in MySQL and server-side validation in PHP Controllers. We ensure that an appointment cannot exist without a valid Doctor ID and Patient ID.

---

## 🚀 Setup & Installation

1.  **Environment**: Install **XAMPP** with PHP 7.4 or higher.
2.  **Directory**: Place the `FinalFYP` folder inside `C:\xampp\htdocs\`.
3.  **Database**: Access PhPMyAdmin and create a database named `med_nova` (or let the system auto-setup).
4.  **Backend Setup**: Navigate to `http://localhost/FinalFYP/backend/` to run initial migrations.
5.  **View Site**: Open `http://localhost/FinalFYP/frontend/index.html`.

---
**Med Nova** – *Where Technology Meets Empathy.*
*Developed as a Final Year Project for Kohat University of Science & Technology.*
