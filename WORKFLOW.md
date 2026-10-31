# Project Execution Workflow (< 1 Month / 7 Phases) — CargoTrace

This document governs how the Developer (Student) and **Agent Antigravity** collaborate to complete **CargoTrace** within $< 30$ days, covering all academic milestones (P8–P15).

---

## 1. The 6-Step Interactive Loop (Applied to Every Feature)

To ensure two-way communication and $100\%$ readiness for the **Code Ownership Interview**, every task must follow this exact loop:

1. **Clarify & Question (Agent $\rightarrow$ Student):**
   - Agent reviews the current phase goal, checks existing code, and asks $1\text{--}5$ clarifying questions if anything is ambiguous.
2. **Propose & Recommend (Agent $\leftrightarrow$ Student):**
   - Agent proposes the technical design (tables, service methods, routes, UI layout) and recommends helpful tools/improvements. Student approves or adjusts.
3. **Incremental Implementation (Agent):**
   - Agent writes clean, modular code following `ARCHITECTURE.md` and `SKILL.md`.
4. **Automated Verification (Agent & Student):**
   - Agent writes/runs PHPUnit feature tests (`php artisan test`) to prove the feature and its edge cases work.
5. **Code Ownership Briefing (Agent $\rightarrow$ Student):**
   - Agent explains *why* the code was written this way and asks 1 quick interview-style check question to make sure the Student understands the implementation.
6. **Meaningful Git Commit (Student):**
   - Agent provides a descriptive *Conventional Commit* message for the Student to commit to GitHub.

---

## 2. Master Schedule & Phase Breakdown (< 30 Days)

### Phase 0: Discovery, Repo Audit & Group Scenario Alignment (Days 1–2 | Milestone P8)
- [ ] Inspect existing repository (`inventory_system_elektronik`) and decide cleanup/reuse strategy.
- [ ] Confirm **Unique Group Scenario** parameters (Route, Delay Threshold, Primary Exception, Primary KPI).
- [ ] Finalize Entity Relationship Diagram (ERD) and Data Dictionary for the 9 mandatory tables.
- [ ] Configure `.env.example` and `config/cargotrace.php`.

### Phase 1: Database Foundations, RBAC & Master Data (Days 3–6 | Milestone P8–P9)
- [ ] Create migrations, models, and relationships for all 9 core tables (`users`, `carriers`, `locations`, `shipments`, `shipment_items`, `tracking_events`, `shipment_exceptions`, `notifications`, `audit_logs`).
- [ ] Configure Authentication, `RoleMiddleware` (`admin`, `operator`, `customer`), and Laravel Policies.
- [ ] Implement `AuditLogService` and CRUD for Master Data (`Carriers`, `Locations`, `Users`).
- [ ] Write Feature Tests for Auth & RBAC isolation.

### Phase 2: Shipment Lifecycle & Historical Tracking Engine (Days 7–11 | Milestone P9–P10)
- [ ] Build `ShipmentService` (shipment creation with items, unique number generation, state machine validation).
- [ ] Build `TrackingService` (append-only tracking events, chronological timestamp validation, current location update).
- [ ] Create Blade UI for Shipment List, Create/Edit Shipment, and Chronological Shipment Timeline (distinguishing normal vs. exception events).
- [ ] Write Feature Tests for valid/invalid status transitions and tracking timeline order.

### Phase 3: Deterministic ETA/Delay Engine & Exception Management (Days 12–15 | Milestone P11)
- [ ] Build `EtaService` (ETA vs. Expected Arrival comparison, delay duration math, configurable severity thresholds).
- [ ] Build `ExceptionService` (`OPEN` $\rightarrow$ `INVESTIGATING` $\rightarrow$ `RESOLVED` workflow, 6 mandatory exception types).
- [ ] Integrate delay badges, reason display, and Exception Management modals/forms into the Shipment UI.
- [ ] Write Unit/Feature Tests for ETA delay rules and exception lifecycle.

### Phase 4: REST API, Leaflet Map & External API Integration (Days 16–20 | Milestone P11–P12)
- [ ] Implement all mandatory REST API endpoints with Laravel Sanctum, API Resources, server-side pagination, and consistent JSON error handling.
- [ ] Integrate **Leaflet.js + OpenStreetMap** on the Shipment Detail page (Origin, Destination, Tracking markers, route polyline, popups, attribution).
- [ ] Build `ExternalIntegrationService` using Laravel HTTP Client (Geocoding API + Weather API) with caching, timeouts, and graceful error fallbacks.
- [ ] Export and verify the **Postman Collection**.

### Phase 5: Operational Dashboard, Search/Filter & Notifications (Days 21–24 | Milestone P13)
- [ ] Build dynamic Operational Dashboard powered by real database queries and **Chart.js** (Total Shipments, In Transit, Delivered, Delayed, Open Exceptions, Shipment by Status/Carrier, Delay/Exception Rate).
- [ ] Implement multi-parameter Search & Filter (shipment/reference number, status, carrier, origin, destination, date range, delay status) with sorting and server-side pagination.
- [ ] Implement In-App Notifications (and optional Email notifications) for status updates, delays, and exceptions.

### Phase 6: Security Hardening, Realistic Seeders & Full Test Suite (Days 25–27 | Milestone P14)
- [ ] Build `DatabaseSeeder` with realistic, consistent synthetic data tailored to the Group Scenario (including accounts for Admin, Operator, and Customer).
- [ ] Perform Security Audit (Backend authorization on all routes, FormRequest validation, CSRF, XSS, SQLi, Mass Assignment protection, zero secrets in Git).
- [ ] Run full automated test suite (`php artisan test`) and document test evidence.

### Phase 7: Cloud Deployment, English Documentation & Interview Prep (Days 28–30 | Milestone P15)
- [ ] Deploy live application to Cloud/VPS (with Docker / GitHub Actions CI/CD if applicable) and verify live demo accounts.
- [ ] Write comprehensive **English `README.md`** (Problem, Features, ERD, Architecture Diagram, Setup, Running Tests, API Docs, Attribution, Known Limitations).
- [ ] Conduct **Mock Code Ownership Interview** with Agent Antigravity (random file explanations + live threshold/rule modification practice).

---

## 3. Git Commit & Workflow Conventions

To satisfy the **Anti-Copy-Paste Policy** and prove organic engineering progress, commit frequently after every sub-feature using *Conventional Commits*:
- `feat(db): add migrations and eloquent relations for 9 core cargotrace tables`
- `feat(shipment): implement state transition validation in ShipmentService`
- `feat(tracking): enforce append-only chronological events in TrackingService`
- `feat(eta): add configurable delay threshold and severity calculation`
- `feat(api): add sanctum endpoints for shipments, tracking, and exceptions`
- `feat(map): integrate leaflet.js route map and open-meteo weather client`
- `test(shipment): add feature tests for rbac isolation and invalid transitions`
- `docs(readme): add english technical documentation, erd, and setup guide`