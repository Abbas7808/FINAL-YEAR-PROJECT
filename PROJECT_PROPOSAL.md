# Med-Nova Healthcare Platform – Detailed Project Proposal

**Document Version:** 2.0  
**Date:** March 3, 2026  
**Project Type:** Multi-tenant Healthcare SaaS (Web)

---

## 1) Executive Summary

Med-Nova is a centralized healthcare operations platform for hospitals, clinics, and specialized care centers. It already provides a working core for appointment intake, role-based dashboards, hospital administration, diet-plan requests, blood donor/request workflows, and blog/news management.  

The proposed next phase is to transition Med-Nova from a “feature-rich prototype” to a production-grade SaaS by hardening data consistency, completing missing workflows (especially mental health and billing depth), improving security/compliance controls, and formalizing delivery milestones.

---

## 2) Problem Context and Opportunity

### 2.1 Current Industry Problem
- Operational data in many facilities is spread across paper logs, spreadsheets, and disconnected software.
- Appointment and triage handling is often manual and non-standardized.
- Specialized services (psychological care, diet counseling, blood request routing) are typically outside the main HIS workflow.

### 2.2 Opportunity
- A unified platform can reduce administrative delay, improve patient throughput, and increase visibility for hospital management.
- Multi-tenant architecture allows onboarding many facilities on one platform while keeping data scoped by organization.
- Integrated specialty modules create a competitive differentiator over generic clinic systems.

---

## 3) Current System Assessment (Based on Repository Review)

### 3.1 Implemented Capabilities

1. **Core platform architecture**
   - Custom PHP MVC routing (`?route=controller/method`) with session-based role access.
   - Frontend static site with Bootstrap + JavaScript and API integration.

2. **Operational modules already working**
   - Appointment booking API + receptionist token workflow.
   - Doctor dashboard + diagnosis + printable prescription.
   - Hospital admin dashboards (doctors, departments, blood donors/requests, diet plans, notifications).
   - Admin management for hospitals/clinics, regions, doctors, and content.
   - Blog and categories with admin CRUD and frontend API feed.

3. **Public patient-facing workflows**
   - Appointment intake wizard.
   - Diet plan request form.
   - Blood donor registration and blood request submission.
   - Contact message submission.

4. **Data layer**
   - MySQL schema with users, hospitals, doctors, affiliations, appointments, prescriptions, payments, reports, and content tables.
   - Auto database creation and schema bootstrap on first run.

### 3.2 Gaps and Technical Debt

1. **Schema/code drift**
   - Some logic and enum values differ across files and migration scripts (example: appointment status values and added columns).

2. **Multi-tenant rigor**
   - Tenant isolation exists conceptually but needs stricter query-level enforcement and policy standardization in all controllers.

3. **Security hardening**
   - Needs stronger credential policy, consistent password hashing paths, stricter validation/sanitization, and centralized authorization checks.

4. **Mental health module depth**
   - Frontend presence exists, but specialized secure clinical workflow is not yet complete as a distinct end-to-end module.

5. **Observability and quality gates**
   - No formal automated test suite, CI checks, or release gates to prevent regressions.

6. **Workflow completion**
   - Billing and advanced reporting are partially represented in schema but not fully delivered in user-facing workflows.

---

## 4) Project Vision and Objectives

### 4.1 Vision
Deliver a secure, scalable, modular healthcare SaaS that supports daily hospital operations and specialized care pathways in one unified platform.

### 4.2 Strategic Objectives
- Standardize end-to-end appointment, diagnosis, and follow-up flow.
- Strengthen hospital-level operational control through dashboards, notifications, and staff workflows.
- Integrate specialized modules (mental health, diet planning, blood services) into clinical pathways.
- Enforce reliable multi-tenant data boundaries and role-based access.
- Prepare platform for production deployment and phased institution onboarding.

---

## 5) Scope Definition

### 5.1 In Scope (Phase Delivery)
- Core appointment lifecycle (book, queue/token, diagnose, close).
- Role dashboards: admin, hospital admin, doctor, receptionist, patient.
- Diet request intake and hospital response management.
- Blood donor/request workflows with hospital handling.
- Content/news module for public communication.
- Tenant onboarding (hospital/clinic registration with admin account linkage).

### 5.2 Out of Scope (Current Proposal Cycle)
- Native mobile apps.
- Advanced AI diagnosis engine.
- Insurance claims integrations.
- Full HL7/FHIR interoperability package (can be a future phase).

---

## 6) Functional Requirements

### 6.1 Access and Identity
- Role-based login and redirects per user role.
- Session handling with forced access checks per controller action.
- Hospital-admin linkage to exactly one managed facility profile.

### 6.2 Appointment and Clinical Workflow
- Public and receptionist booking paths.
- Token generation per doctor/day.
- Doctor diagnosis entry and prescription generation.
- Patient appointment history in dashboard.

### 6.3 Hospital Administration
- Manage departments, doctors, schedule-affiliated resources.
- Monitor pending appointments, blood requests, and diet requests.
- Notification center for operational events.
- Staff management (receptionist lifecycle).

### 6.4 Specialty Services
- **Diet:** request intake, assignment status, and plan lifecycle.
- **Blood:** donor registry, request intake, urgency-based tracking.
- **Mental Health (target state):** isolated case-notes workflow with stricter access policy.

### 6.5 Platform and Content
- Region/hospital discovery APIs.
- Dynamic blog/news delivery.
- Contact submission channel.

---

## 7) Non-Functional Requirements

- **Security:** password hashing, CSRF strategy for form actions, input validation, least-privilege role checks.
- **Performance:** responsive list APIs and dashboard queries under realistic outpatient load.
- **Scalability:** tenant-aware indexing and query design.
- **Reliability:** transactional writes for critical operations (appointments, prescriptions, notifications).
- **Maintainability:** clear schema migration strategy and reduction of duplicate logic.
- **Auditability:** action logging for sensitive operations and admin changes.

---

## 8) Proposed Technical Architecture (Target)

1. **Presentation Layer**
   - Static frontend pages with shared JS API client.
   - Backend-rendered internal role dashboards.

2. **Application Layer**
   - MVC controllers grouped by role and domain.
   - Service-oriented refactor path for reusable business logic (appointments, notifications, tenancy checks).

3. **Data Layer**
   - MySQL with migration-controlled schema evolution.
   - Enforced foreign keys and role/tenant-aware query constraints.

4. **Integration Layer**
   - REST-like API endpoints for frontend forms.
   - Optional email notifications for hospital admins.

---

## 9) Delivery Roadmap (Recommended)

### Phase 1 (Weeks 1–3): Stabilization and Data Integrity
- Reconcile schema vs controller/model assumptions.
- Standardize enums/column contracts across all appointment and user flows.
- Add migration scripts for deterministic DB evolution.
- Fix high-impact inconsistencies in auth and patient creation paths.

### Phase 2 (Weeks 4–6): Security and Tenant Hardening
- Centralize role/permission checks.
- Apply robust input validation and output encoding patterns.
- Add tenant guardrails to all data-access queries.
- Introduce audit logging for sensitive actions.

### Phase 3 (Weeks 7–10): Feature Completion
- Finalize mental health workflow and access boundaries.
- Expand billing flow from schema-level support to operational UI/reporting.
- Improve doctor scheduling and appointment status transitions.

### Phase 4 (Weeks 11–12): QA, UAT, and Release Readiness
- Regression test checklist and smoke suite.
- UAT with at least one pilot hospital + one clinic scenario.
- Deployment runbook and rollback plan.

---

## 10) Testing and Quality Strategy

- **Unit-level validation:** critical business logic paths (token generation, role checks, status updates).
- **Integration tests:** API form submissions for appointment, diet, blood, and contact.
- **Role-based tests:** unauthorized access attempts per dashboard/controller.
- **Data integrity tests:** foreign key and transaction behavior for create/update flows.
- **UAT scenarios:** receptionist walk-in, doctor diagnosis, hospital admin request processing.

---

## 11) Risk Register and Mitigation

1. **Schema mismatch risk**  
   Mitigation: migration-first process, environment bootstrap tests.

2. **Data isolation leakage risk**  
   Mitigation: tenant-aware query review checklist + code-level guards.

3. **Authentication/authorization bypass risk**  
   Mitigation: centralized policy helper and consistent controller middleware pattern.

4. **Operational adoption risk**  
   Mitigation: role-specific onboarding guides and pilot rollout with feedback loop.

5. **Regression risk during refactor**  
   Mitigation: phased changes, smoke tests, and release rollback procedure.

---

## 12) Success Metrics (KPIs)

- Appointment booking completion rate.
- Average check-in to diagnosis cycle time.
- Blood request response time.
- Diet plan request turnaround time.
- Monthly active facilities (hospitals/clinics).
- Defect rate per release and mean time to recovery (MTTR).

---

## 13) Expected Outcomes

By completing this proposal roadmap, Med-Nova will move from a strong prototype to a deployable healthcare SaaS platform with:
- Reliable core clinical operations,
- Better tenant safety and security posture,
- Stronger specialized-care coverage,
- Production-grade release discipline.

This positions the product for pilot deployments and scalable onboarding of healthcare institutions.


