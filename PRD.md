# Product Requirements Document (PRD) — CargoTrace

**Project Name:** CargoTrace — Global Shipment Visibility & Tracking Platform  
**Course:** Web Programming 2 (Final Project / UAS)  
**Target Timeline:** $< 1$ Month ($< 30$ Days)  
**Project Goal:** Build a production-grade software engineering portfolio project (not just a basic CRUD website) that is fully tested, deployed online, documented in English, and ready for a live demo and code ownership interview.

---

## 1. Problem Statement & Solution

### 1.1 Problems Addressed
- Shipment information is fragmented and difficult to monitor across multiple carriers and routes.
- Customers lack real-time visibility into their current shipment status, ETA, and historical journey timeline.
- Delays and operational exceptions are not centrally detected or prioritized.
- Logistics operators need an actionable dashboard to prioritize follow-ups and resolve exceptions efficiently.

### 1.2 CargoTrace Solution
- Centralized management for shipments, items, carriers, locations, and historical tracking events.
- Interactive journey timeline and map visualization (Origin, Destination, and Tracking Points).
- Deterministic, rule-based delay detection and severity calculation.
- Structured exception lifecycle management and real-time operational metrics dashboard.

---

## 2. Scope & System Boundaries

### 2.1 In-Scope (Mandatory MVP)
- Shipment lifecycle management with strict backend state transition rules.
- Append-only historical tracking timeline and interactive map visualization (Leaflet.js + OpenStreetMap).
- Deterministic ETA vs. Expected Arrival comparison, delay calculation, and exception management.
- Server-side Role-Based Access Control (RBAC) for 3 distinct roles.
- RESTful JSON API with Laravel Sanctum authentication, server-side pagination, and filtering.
- External API integration (Geocoding and/or Weather API) with timeout and error handling.
- Operational Dashboard (Chart.js), In-App Notifications, and Audit Logging.
- Automated Feature/API Testing (PHPUnit/Pest) and Live Cloud/VPS Deployment.

### 2.2 Out-of-Scope
- Direct integration with real-world port authorities, customs systems, or live commercial carriers.
- Real financial payments or commercial billing transactions.
- Real-time GPS hardware telemetry from physical IoT devices.
- Complex Machine Learning / AI predictive delay models.
- Microservices architecture (must use a clean Monolithic Laravel Service Layer architecture).

> **Note on Demo Data:** Synthetic demonstration data is permitted and expected, provided it is realistic, consistent, and well-documented via Laravel Seeders.

---

## 3. User Roles & Permissions Matrix

All routes, Blade views, and API endpoints **must** enforce authorization on the backend server (never rely solely on hiding buttons in the UI).

| Capability / Feature | Administrator | Logistics Operator | Customer |
| :--- | :---: | :---: | :---: |
| Manage Users, Roles & System Config (Delay Thresholds) | Yes | No | No |
| Manage Master Data (`Carriers`, `Locations`) | Yes | View Only | No |
| View All Shipments & Operational Dashboard | Yes | Yes | No (Own Only) |
| Create & Update Shipments | Yes | Yes | No |
| Add Historical Tracking Events | Yes | Yes | No |
| Create, Investigate & Resolve Exceptions | Yes | Yes | View Relevant Only |
| View Own Shipments, ETA, Timeline & Route Map | Yes | Yes | Yes (Strictly Own) |
| View In-App Notifications | Yes | Yes | Yes (Strictly Own) |
| View System Audit Logs (`audit_logs`) | Yes | No | No |

---

## 4. Functional Requirements & Business Rules

### FR-01: Shipment Lifecycle & State Machine
- **Minimum Shipment Data:** `shipment_number` (unique), `customer_id`, `carrier_id`, `origin_location_id`, `destination_location_id`, `cargo_type`, `weight`, `volume`, `expected_departure`, `expected_arrival`, `estimated_arrival` (ETA), `current_status`.
- **Valid Status Flow:**
  $$\text{CREATED} \rightarrow \text{BOOKED} \rightarrow \text{PICKED\_UP} \rightarrow \text{IN\_TRANSIT} \rightarrow \text{DELIVERED}$$
  *(Optional controlled cancellation/hold states may be discussed and added if needed, e.g., `CANCELLED` from `CREATED`/`BOOKED`).*
- **Mandatory Rules:**
  - Status transitions must be validated in `ShipmentService` on the backend.
  - Terminal statuses such as `DELIVERED` cannot revert to earlier statuses without a special administrative override procedure recorded in `audit_logs`.

### FR-02: Historical Tracking & Timeline
- **Append-Only Rule:** Tracking events are historical records. Adding a new status/event **must never overwrite** previous events.
- **Event Attributes:** `shipment_id`, `event_type`/`status`, `occurred_at` (timestamp), `location_id`, `latitude`, `longitude`, `description`, `created_by` (user ID).
- **Validation:**
  - Chronological validation: a new event's `occurred_at` timestamp cannot be earlier than the previous valid event for that shipment.
  - Current location of a shipment is derived from the most recent valid tracking event.
- **Timeline UI:**
  - Ordered chronologically.
  - Visually distinguishes normal progression events from exception events.

### FR-03: Deterministic ETA, Delay Engine & Exception Management
- **ETA & Delay Calculation:**
  - Compare `estimated_arrival` (ETA) against `expected_arrival`.
  - If $\text{ETA} > \text{Expected Arrival}$, mark shipment as `is_delayed = true` and compute `delay_duration_hours`.
  - **Default Severity Rule (Configurable per Group Scenario):**
    - $0 \text{ to } 6 \text{ hours}: \text{LOW}$
    - $> 6 \text{ to } 24 \text{ hours}: \text{MEDIUM}$
    - $> 24 \text{ hours}: \text{HIGH}$
  - Display the exact delay duration, threshold rule applied, and reason on the UI.
- **Exception Management:**
  - **Exception Types:** `CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`, `DAMAGED`, `MISROUTED`, `DOCUMENTATION_ISSUE`.
  - **Exception Statuses:** `OPEN` $\rightarrow$ `INVESTIGATING` $\rightarrow$ `RESOLVED`.
  - **Severity Levels:** `LOW`, `MEDIUM`, `HIGH`, `CRITICAL`.
  - Must record `detected_at`, `resolved_at`, resolution notes, and the actor who resolved it.

### FR-04: Interactive Map & External API Integration
- **Map Visualization (Leaflet.js + OpenStreetMap):**
  - Display markers for Origin, Destination, and intermediate Tracking Points.
  - Draw route lines (polylines) connecting the journey points.
  - Interactive popups showing status, timestamp, location name, and weather/context info.
  - Must display proper OpenStreetMap attribution and comply with tile usage policies.
- **External API Integration (Laravel HTTP Client):**
  - Integrate at least 1 (recommended 2) external services:
    1. **Geocoding API** (e.g., OpenStreetMap Nominatim / OpenCage) to resolve city/port names into `latitude` and `longitude`.
    2. **Weather API** (e.g., Open-Meteo / OpenWeatherMap) to display current weather at the shipment's latest location for operational context (never claim weather data is an AI delay prediction).
  - Must handle connection timeouts, HTTP errors, and empty responses gracefully with fallback values.
  - Store all API keys in `.env` (never commit secrets to GitHub).

### FR-05: Operational Dashboard, Search, Filter & Notifications
- **Real-Time Database Dashboard (No Static Numbers):**
  - KPI Cards: Total Shipments, In Transit, Delivered, Delayed, Open Exceptions, Delay Rate ($\%$), Exception Rate ($\%$), plus Group Scenario KPI.
  - Charts (Chart.js): Shipments by Status, Shipments by Carrier, Delay/Exception trends.
- **Search, Multi-Filter & Pagination:**
  - Search by Shipment Number / Reference Number.
  - Filter by Status, Carrier, Origin, Destination, Date Range, and Delay Status.
  - Server-side sorting and pagination.
- **Notifications & Audit Log:**
  - In-app notifications (unread badge + dropdown/list) triggered on status changes, delays, and new/resolved exceptions.
  - Email notifications (optional advanced feature).
  - `audit_logs` capturing Actor (`user_id`), Action, Entity Type & ID, Old/New payloads, and Timestamp for critical mutations.

### FR-06: REST API & Quality Contract
- **Authentication:** Laravel Sanctum (`POST /api/register`, `POST /api/login`, `POST /api/logout`, `GET /api/user`).
- **Shipments & Tracking:**
  - `GET /api/shipments`, `POST /api/shipments`, `GET /api/shipments/{id}`, `PUT/PATCH /api/shipments/{id}`, `DELETE /api/shipments/{id}`
  - `GET /api/shipments/{id}/tracking`, `POST /api/shipments/{id}/tracking`
- **Related Resources:**
  - `GET /api/carriers`, `POST /api/carriers`
  - `GET /api/locations`, `POST /api/locations`
  - `GET /api/shipments/{id}/exceptions`, `POST /api/shipments/{id}/exceptions`, `PATCH /api/exceptions/{id}`
- **Quality Contract:** Consistent JSON structure, proper HTTP status codes (`200`, `201`, `401`, `403`, `404`, `422`), no stack trace leaks on error, accompanied by an exported Postman Collection.

---

## 5. Unique Group Scenario Configurability

Because the lecturer assigns unique parameters per group and may ask for live modifications during the Code Ownership Interview, the following parameters **must be configurable** (via `.env` defaults and an Admin Settings table/page):
- **Primary Route Focus** (e.g., Southeast Asia $\rightarrow$ Europe, Asia $\rightarrow$ North America, Southeast Asia $\rightarrow$ Australia).
- **Delay Threshold Hours** (e.g., $12$ hours, $18$ hours, or $24$ hours) and severity brackets.
- **Primary Exception Focus** & **Primary KPI Metric** (e.g., On-Time Delivery Rate, Average Delay Duration, Exception Resolution Time).

---

## 6. Assessment Rubric & Definition of Done

| Assessment Category | Weight | Target Deliverable |
| :--- | :---: | :--- |
| Functionality & Business Rules | $25\%$ | Lifecycle, tracking, ETA/delay, and exceptions work flawlessly. |
| Database Design & Laravel Architecture | $15\%$ | 9 normalized tables, ERD, Service Layer separation. |
| REST API & External/Map Integration | $15\%$ | Sanctum API, Leaflet map, HTTP Client with error handling. |
| Security & Authorization | $10\%$ | Backend RBAC policies, validation, CSRF/XSS/SQLi protection. |
| Testing & Reliability | $10\%$ | Passing PHPUnit/Pest feature & API test suite. |
| UI/UX & Dashboard | $8\%$ | Responsive Tailwind UI, dynamic Chart.js operational dashboard. |
| Deployment & GitHub Workflow | $7\%$ | Live URL, meaningful Git commit history, no leaked secrets. |
| Documentation in English | $5\%$ | Comprehensive English `README.md`, ERD, Architecture & API docs. |
| Code Ownership Interview | $5\%$ | Ability to explain any file/function and perform live code tweaks. |