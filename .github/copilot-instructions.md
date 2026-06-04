# Copilot Instructions for Med-Nova Healthcare Platform

## Project Overview

**Med-Nova** is a multi-tenant, SaaS-based healthcare platform for hospitals, clinics, and specialized centers. At a product level it delivers:
- **Hospital & clinic management**: Full appointment flow (booking, rescheduling, cancellations), outpatient and inpatient management, staff roles, billing support, and operational reporting for general hospitals and clinics.
- **Psychological & mental health centers**: A dedicated, logically separate workflow for psychological centers with their own schedules, case notes, and role-based access to protect sensitive records.
- **Diet & nutrition plans**: Tools for creating and managing personalized diet plans that can be attached to patient profiles and clinical encounters.
- **Blood donation & requests**: Management of donor registrations, donation history, blood stock, and incoming blood requests from hospitals/clinics.

From a technical perspective, the current implementation includes:
- **Frontend**: Static HTML pages with Bootstrap 5, custom CSS, and basic JavaScript
- **Backend**: PHP MVC architecture with MySQL database and RESTful API endpoints
- **Database**: Normalized schema supporting multi-hospital, multi-user role-based access

The project is located at `c:\xampp\htdocs\FYP\` with two main directories: `frontend/` (HTML/JS/Bootstrap) and `backend/` (PHP/MySQL).

## Architecture & Data Flow

### Backend MVC Pattern (PHP)
The backend uses a **custom MVC router** (not a framework):
- **Router** (`backend/core/Router.php`): Parses URL query string `?route=controller/method` → instantiates controller → calls method
- **Controller** (`backend/core/Controller.php`): Base class with `$db` connection, `view()`, `redirect()` methods
- **Database** (`backend/config/Database.php`): PDO MySQL connection with auto-create schema feature

**Route format**: `?route=auth/login` → `AuthController->login()`

### Key Controllers
- **ApiController**: Handles JSON API endpoints (GET/POST with CORS). Uses `jsonResponse()` for all responses.
- **AuthController**: Session-based login, role-based redirects to dashboards
- **HospitalAdminController**, **DoctorController**, **PatientController**: Role-specific views
- **ApiController.regions()**, **.hospitals()**: Query endpoints with optional filters (region_id, slug, id, search, type)

### Frontend (Static HTML)
- **Pages**: Multiple HTML files (`index.html`, `about.html`, `services.html`, `doctors.html`, `appointment.html`, etc.)
- **Styling**: Bootstrap 5 CDN + custom `frontend/css/style.css`
- **JavaScript**: Basic JS in `frontend/js/` (`app.js`, `chatbot.js`, `prediction.js`)
- **Forms**: Currently static HTML forms (need backend integration)

## Critical Developer Workflows

### Starting Development

**Frontend** (HTML/JS/Bootstrap):
- Place `frontend/` in web server root (e.g., `c:\xampp\htdocs\frontend\`)
- Access at `http://localhost/frontend/index.html`
- No build process required - static files

**Backend** (PHP/MySQL):
- Place `backend/` at `c:\xampp\htdocs\backend\`
- Access at `http://localhost/backend/?route=auth/login`
- Database auto-creates on first connection if `fyp_mediqu` doesn't exist
- Uses local XAMPP MySQL (root user, no password by default)

### Database Management

**Schema file**: `backend/schema.sql` contains complete DDL
- **Key tables**: `users`, `patients_details`, `hospitals`, `doctors_details`, `appointments`, `departments`, `regions`
- **Roles**: admin, doctor, receptionist, patient (enum in `users.role`)
- **On first API call**: Database auto-initializes via `Database->createDatabase()` if missing

### Testing Forms

**Current state**: HTML forms in `frontend/appointment.html`, `frontend/contact.html` are static (no backend submission)
- **To enable backend**: Update form `action` attributes to point to PHP endpoints
- **Expected payload** (Appointment form): `{ name, phone, email, healthType, doctor, date }`
- **API endpoint**: `POST /api/appointments` (per BACKEND_INTEGRATION.md spec)

## Project-Specific Patterns & Conventions

### PHP Backend Patterns
1. **Error handling**: Controllers catch exceptions, return `jsonResponse()` with status codes
2. **Database queries**: Use PDO prepared statements with named placeholders (`:param`)
3. **Response format**: `{ "message": "...", "error": "...", "data": {...} }`
4. **CORS**: ApiController automatically sets headers; handles OPTIONS requests

### Frontend (Static HTML)
- **Pages**: Multiple HTML files (`index.html`, `about.html`, `services.html`, `doctors.html`, `appointment.html`, etc.)
- **Styling**: Bootstrap 5 CDN + custom `frontend/css/style.css`
- **JavaScript**: Basic JS in `frontend/js/` (`app.js`, `chatbot.js`, `prediction.js`)
- **Forms**: Currently static HTML forms (need backend integration)
- **Navigation**: Standard HTML links between pages

### Naming Conventions
- **Controllers**: PascalCase + "Controller" suffix (e.g., `AuthController`, `DoctorController`)
- **Models**: PascalCase (e.g., `User.php`, `Appointment.php`)
- **Routes**: lowercase with underscores if multi-word (e.g., `?route=hospital_admin/dashboard`)
- **HTML files**: lowercase with underscores (e.g., `appointment.html`, `contact.html`)
- **CSS classes**: kebab-case (e.g., `.hero-section`, `.doctor-card`)
- **JavaScript files**: lowercase (e.g., `app.js`, `chatbot.js`)

## Integration Points & Dependencies

### Frontend-to-Backend Communication
- **CORS-enabled**: All ApiController methods accept cross-origin requests
- **Content-Type**: Application/JSON for API responses
- **Query parameters**: Supported in ApiController (e.g., `?route=api/hospitals&region_id=1&search=cardio`)
- **Session-based**: Traditional form-based pages use `$_SESSION` (AuthController); API endpoints are stateless

### External Dependencies
- **Frontend**: Bootstrap 5 (CDN), FontAwesome 6, Google Fonts (Poppins), jQuery (if needed)
- **Backend**: PHP 7.4+ with PDO MySQL extension, XAMPP environment
- **Database**: MySQL 5.7+ (auto-created on first connection)

### Hospital Multi-Tenancy Concept
- Hospitals can register, have a `slug` for identification, user_id links to admin
- API endpoints filter by `slug` or `region_id` to serve hospital-specific data
- Each hospital can have multiple doctors and appointments

## Common Debugging & Maintenance Tasks

### Database Issues
- Auto-repair via `backend/schema.sql` re-import on connection failure (DB doesn't exist)
- Check table structure: `backend/debug_db.php`, `backend/check_schema.php`
- View debug output: `backend/dbcheck_out.txt` after running checks

### API Troubleshooting
- ApiController returns `{ "error": "message" }` on exceptions with HTTP status codes
- Enable PHP error logging: `ini_set('display_errors', 1)` in `backend/index.php` (already enabled)
- Check CORS headers: Browser DevTools → Network tab → Response headers

### Frontend Issues
- Port conflicts: `PORT=3001 npm start` to use alternate port
- Missing assets: Verify paths use `/assets/` prefix (not relative `assets/`)
- Module errors: `rm -rf node_modules package-lock.json && npm install`

## When Adding New Features

1. **Backend API endpoint**: Extend ApiController or create new Controller, follow JSON response pattern
2. **Frontend page**: Add new HTML file to `frontend/`, link from navigation
3. **Database changes**: Update `backend/schema.sql` (runs on init), ensure Foreign Keys are defined
4. **Forms**: Add validation, use `fetch()` to call API, handle both `response.ok` and error states
5. **Role-based access**: Check `$_SESSION['role']` in controller, or add frontend route guards if needed

## File Reference Guide

| Path | Purpose |
|------|---------|
| `backend/index.php` | Entry point, initializes Router |
| `backend/config/Database.php` | PDO connection, auto-schema init |
| `backend/controllers/ApiController.php` | JSON API, CORS handling |
| `backend/schema.sql` | Database DDL |
| `frontend/index.html` | Main homepage |
| `frontend/css/style.css` | Custom styling |
| `frontend/js/app.js` | Main JavaScript functionality |
| `Med-Nova/BACKEND_INTEGRATION.md` | API endpoint specifications |
| `Med-Nova/package.json` | React app dependencies (separate project) |
