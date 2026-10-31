# System Architecture & Technical Blueprint — CargoTrace

This document defines the architectural standards, service layer boundaries, database schema, and integration patterns for **CargoTrace**.

---

## 1. Technology Stack

- **Application Backend:** PHP 8.3+, Laravel 12+
- **Database:** MySQL 8+ (InnoDB, strict foreign key constraints, composite indexes)
- **Frontend UI:** Laravel Blade Templates, Tailwind CSS, Vanilla JavaScript + Fetch API
- **Maps & Visualization:** Leaflet.js + OpenStreetMap (Map), Chart.js (Operational Dashboard)
- **API & Auth:** RESTful JSON API, Laravel Sanctum (Token Authentication)
- **External Integration:** Laravel HTTP Client (`Illuminate\Support\Facades\Http`)
- **Testing & Quality:** PHPUnit / Pest, Postman Collection, GitHub Actions CI (optional/advanced)

---

## 2. Layered Architecture Guideline

CargoTrace strictly separates HTTP transport concerns from business rules using a **Service Layer Pattern**:

$$\text{Browser / API Client} \rightarrow \text{Routes + Middleware} \rightarrow \text{Controllers + FormRequests} \rightarrow \text{Service Layer} \rightarrow \text{Eloquent Models / External APIs}$$

### 2.1 Layer Responsibilities
1. **Routes & Middleware (`routes/web.php`, `routes/api.php`):**
   - Route definition, authentication (`auth`, `auth:sanctum`), and role-based access middleware (`role:admin,operator,customer`).
2. **FormRequests & Policies (`app/Http/Requests/`, `app/Policies/`):**
   - Input validation (`422 Unprocessable Entity` on failure) and resource-level authorization (`ShipmentPolicy`, `ExceptionPolicy`) ensuring Customers can only access their own shipments (`403 Forbidden` otherwise).
3. **Controllers (`app/Http/Controllers/`, `app/Http/Controllers/Api/`):**
   - Thin orchestrators: receive validated data, invoke the appropriate Service class, and return a Blade view, redirect, or standardized JSON Resource.
4. **Service Layer (`app/Services/`):**
   - Contains all business rules, state machine validations, deterministic calculations, database transactions (`DB::transaction`), notification triggers, and audit logging.
5. **Models / Persistence (`app/Models/`):**
   - Eloquent definitions, relationships, scopes, and type casting.

---

## 3. Core Service Layer Design

### 3.1 `ShipmentService`
- Generates unique shipment numbers (e.g., `CT-2026-XXXXXX`).
- Handles shipment creation and item attachment inside `DB::transaction()`.
- Enforces the **Shipment State Machine**:
  - Allowed transitions:
    - `CREATED` $\rightarrow$ `BOOKED`, `CANCELLED`
    - `BOOKED` $\rightarrow$ `PICKED_UP`, `CANCELLED`
    - `PICKED_UP` $\rightarrow$ `IN_TRANSIT`
    - `IN_TRANSIT` $\rightarrow$ `DELIVERED`
    - `DELIVERED` $\rightarrow$ *(Terminal State — no further transitions allowed without Admin override)*
- Automatically triggers `TrackingService` and `AuditLogService` when status changes.

### 3.2 `TrackingService`
- Appends new `TrackingEvent` records (strictly **append-only**, never overwriting past events).
- Validates chronological integrity: new event `occurred_at` must be $\ge$ the latest existing event's `occurred_at` for that shipment.
- Updates the parent shipment's `current_status` and current coordinates (`current_lat`, `current_lng`) if the event represents a forward status progression.
- Dispatches in-app notifications to the shipment's Customer.

### 3.3 `EtaService`
- Performs deterministic comparison between `estimated_arrival` (ETA) and `expected_arrival`.
- Calculates delay duration in hours:
  $$\text{Delay Hours} = \max\left(0, \frac{\text{ETA} - \text{Expected Arrival}}{3600}\right)$$
- Evaluates configurable delay thresholds (loaded from `config('cargotrace.delay')` or database settings for the Group Scenario):
  - If $\text{Delay Hours} \le 0$: `is_delayed = false`, `delay_severity = null`
  - Bracket 1 (e.g., $> 0 \text{ to } T_1 \text{ hours}$): `LOW`
  - Bracket 2 (e.g., $> T_1 \text{ to } T_2 \text{ hours}$): `MEDIUM`
  - Bracket 3 (e.g., $> T_2 \text{ hours}$): `HIGH`

### 3.4 `ExceptionService`
- Manages `ShipmentException` lifecycle (`OPEN` $\rightarrow$ `INVESTIGATING` $\rightarrow$ `RESOLVED`).
- Validates allowed `exception_type`: `CUSTOMS_HOLD`, `WEATHER_DELAY`, `PORT_CONGESTION`, `DAMAGED`, `MISROUTED`, `DOCUMENTATION_ISSUE`.
- Automatically records an associated exception event on the shipment timeline and notifies the Customer and Operators.
- Computes resolution duration ($\text{resolved\_at} - \text{detected\_at}$) for KPI reporting.

### 3.5 `ExternalIntegrationService` (Geocoding & Weather)
- Uses `Http::timeout(5)->retry(2, 200)` to call external APIs:
  - **Geocoding (e.g., Nominatim / OpenCage):** Resolves location city/port/country into `latitude` and `longitude`.
  - **Weather (e.g., Open-Meteo):** Fetches current weather conditions at a location's coordinates.
- Caches responses (`Cache::remember`) to respect rate limits and improve page speed.
- Returns a structured DTO/array with `available = false` and a safe fallback message if the external service times out or fails, ensuring the application never crashes.

### 3.6 `AuditLogService`
- Records critical state changes (`shipment.created`, `shipment.status_changed`, `exception.opened`, `exception.resolved`, `config.updated`) with `user_id`, `ip_address`, `old_values` (JSON), and `new_values` (JSON).

---

## 4. Database Schema & Entity Relationships (Minimum 9 Core Tables)

1. **`users`**
   - `id`, `name`, `email` (unique), `password`, `role` (`admin`, `operator`, `customer`), `phone`, `company_name`, `timestamps`
2. **`carriers`**
   - `id`, `code` (unique, e.g., `MAERSK`, `DHL`), `name`, `transport_mode` (`SEA`, `AIR`, `LAND`, `MULTIMODAL`), `contact_email`, `contact_phone`, `is_active`, `timestamps`
3. **`locations`**
   - `id`, `code` (unique UN/LOCODE or custom code), `name`, `city`, `country`, `type` (`PORT`, `AIRPORT`, `WAREHOUSE`, `HUB`), `latitude` (`decimal(10,7)`), `longitude` (`decimal(10,7)`), `timestamps`
4. **`shipments`**
   - `id`, `shipment_number` (unique, indexed), `reference_number` (indexed), `customer_id` (FK $\rightarrow$ `users`), `carrier_id` (FK $\rightarrow$ `carriers`), `origin_location_id` (FK $\rightarrow$ `locations`), `destination_location_id` (FK $\rightarrow$ `locations`), `created_by` (FK $\rightarrow$ `users`), `cargo_type`, `total_weight_kg`, `total_volume_cbm`, `expected_departure`, `expected_arrival`, `estimated_arrival`, `actual_arrival` (nullable), `current_status` (indexed), `is_delayed` (boolean, indexed), `delay_duration_hours` (decimal), `delay_severity` (`LOW`, `MEDIUM`, `HIGH`, nullable), `delay_reason` (nullable), `notes`, `timestamps`
5. **`shipment_items`**
   - `id`, `shipment_id` (FK $\rightarrow$ `shipments` cascade delete), `sku`, `description`, `quantity`, `weight_kg`, `volume_cbm`, `declared_value`, `timestamps`
6. **`tracking_events`**
   - `id`, `shipment_id` (FK $\rightarrow$ `shipments` cascade delete), `location_id` (FK $\rightarrow$ `locations`, nullable), `created_by` (FK $\rightarrow$ `users`), `status`, `event_type` (`NORMAL`, `EXCEPTION`, `SYSTEM`), `latitude` (`decimal(10,7)`, nullable), `longitude` (`decimal(10,7)`, nullable), `description`, `weather_snapshot` (JSON, nullable), `occurred_at` (indexed), `timestamps`
7. **`shipment_exceptions`**
   - `id`, `shipment_id` (FK $\rightarrow$ `shipments` cascade delete), `tracking_event_id` (FK $\rightarrow$ `tracking_events`, nullable), `reported_by` (FK $\rightarrow$ `users`), `resolved_by` (FK $\rightarrow$ `users`, nullable), `exception_type` (indexed), `severity` (`LOW`, `MEDIUM`, `HIGH`, `CRITICAL`), `status` (`OPEN`, `INVESTIGATING`, `RESOLVED`, indexed), `description`, `resolution_notes` (nullable), `detected_at`, `resolved_at` (nullable), `timestamps`
8. **`notifications`**
   - Standard Laravel notifications table (`id` UUID, `type`, `notifiable_type`, `notifiable_id`, `data` JSON, `read_at`, `timestamps`) or custom structure linked to `user_id` and `shipment_id`.
9. **`audit_logs`**
   - `id`, `user_id` (FK $\rightarrow$ `users`, nullable), `action` (indexed), `auditable_type`, `auditable_id`, `old_values` (JSON, nullable), `new_values` (JSON, nullable), `ip_address`, `user_agent`, `created_at`

*(Optional 10th helper table: `system_settings` to dynamically store Group Scenario parameters like `delay_threshold_low`, `delay_threshold_high`, and `primary_route` so they can be changed live during the lecturer's interview).*

### 4.1 Cardinality Summary
- `User` (Customer) $1 \leftrightarrow N$ `Shipment` (`hasMany` / `belongsTo`)
- `Carrier` $1 \leftrightarrow N$ `Shipment` (`hasMany` / `belongsTo`)
- `Location` $1 \leftrightarrow N$ `Shipment` (as Origin and as Destination)
- `Shipment` $1 \leftrightarrow N$ `ShipmentItem` (`hasMany` / `belongsTo`)
- `Shipment` $1 \leftrightarrow N$ `TrackingEvent` (`hasMany` ordered by `occurred_at` / `belongsTo`)
- `Shipment` $1 \leftrightarrow N$ `ShipmentException` (`hasMany` / `belongsTo`)
- `User` $1 \leftrightarrow N$ `AuditLog` (`hasMany` / `belongsTo`)