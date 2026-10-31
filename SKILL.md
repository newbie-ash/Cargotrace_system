---
name: cargotrace-engineering-standards
description: Domain rules, architecture patterns, and testing standards for the CargoTrace Shipment Visibility Platform. Apply whenever writing or reviewing Models, Services, Controllers, Blade views, Leaflet maps, or Tests.
---

# CargoTrace Domain & Engineering Skill

When developing or refactoring any feature in **CargoTrace**, strictly enforce the following patterns and rules.

## 1. Service Layer & Controller Separation

- **NEVER** place state transition checks, ETA math, or exception resolution logic inside a Controller.
- Controllers must only:
  1. Authorize the request (`$this->authorize(...)`).
  2. Validate input via a dedicated `FormRequest`.
  3. Call a method on a Service class (`ShipmentService`, `TrackingService`, `EtaService`, `ExceptionService`).
  4. Return a Blade view, redirect, or API JSON response.

## 2. Shipment State Machine Pattern

Always validate status transitions deterministically in `ShipmentService`:

```php
public const ALLOWED_TRANSITIONS = [
    'CREATED'    => ['BOOKED', 'CANCELLED'],
    'BOOKED'     => ['PICKED_UP', 'CANCELLED'],
    'PICKED_UP'  => ['IN_TRANSIT'],
    'IN_TRANSIT' => ['DELIVERED'],
    'DELIVERED'  => [], // Terminal state
    'CANCELLED'  => [], // Terminal state
];

public function canTransition(string $from, string $to): bool
{
    return in_array($to, self::ALLOWED_TRANSITIONS[$from] ?? [], true);
}
```
- If a transition is invalid, throw a `DomainException` or `ValidationException` with a clear message explaining why the transition from `$from` to `$to` is prohibited.

## 3. Append-Only Tracking Events

- `TrackingEvent` records are immutable history. Never call `update()` on an existing `TrackingEvent` to represent a new status.
- Before creating a `TrackingEvent`, verify chronological order:
  - Query the latest event for the shipment (`$shipment->trackingEvents()->latest('occurred_at')->first()`).
  - If `$newOccurredAt->lt($latestEvent->occurred_at)`, reject with a `422` validation error.

## 4. Configurable ETA & Delay Calculation

- Never hardcode magic numbers for delay thresholds inside `EtaService` methods.
- Read thresholds from configuration or database settings so they can be changed in 10 seconds during the lecturer's Code Ownership Interview:

```php
// config/cargotrace.php
return [
    'delay_thresholds' => [
        'low_max_hours'    => (int) env('CARGOTRACE_DELAY_LOW_MAX', 6),
        'medium_max_hours' => (int) env('CARGOTRACE_DELAY_MEDIUM_MAX', 24),
        'group_threshold'  => (int) env('CARGOTRACE_GROUP_DELAY_THRESHOLD', 12),
    ],
];
```

## 5. External API Resilience & Leaflet Map Rules

- **HTTP Client:** Always wrap external geocoding/weather calls in `try / catch` with `Http::timeout(5)->retry(2, 100)`.
- If the external API fails or returns empty data, log a warning and return a graceful fallback array—never let a third-party outage break the shipment detail page.
- **Leaflet.js:**
  - Always include the required OpenStreetMap copyright attribution in `L.tileLayer`.
  - Render markers for Origin, Destination, and valid Tracking Events with coordinates, and fit map bounds automatically (`map.fitBounds(polyline.getBounds())`).

## 6. REST API Response Consistency

- Use Laravel API Resources (`ShipmentResource`, `TrackingEventResource`, `ExceptionResource`).
- Ensure unauthorized access returns `401` (unauthenticated) or `403` (forbidden, e.g., Customer accessing another Customer's shipment), missing records return `404`, and invalid inputs return `422`.
- Never expose stack traces when `Accept: application/json` is requested.

## 7. Mandatory Automated Test Coverage

Every new module must include PHPUnit/Pest tests covering:
1. Authentication (valid login, invalid login, unauthenticated redirect/401).
2. Shipment CRUD + Customer isolation (Customer A cannot view/API-fetch Customer B's shipment $\rightarrow$ `403`).
3. Tracking event creation + rejection of invalid status transitions (e.g., `DELIVERED` $\rightarrow$ `BOOKED` $\rightarrow$ `422`).
4. Deterministic ETA/delay severity calculation under different threshold configurations.