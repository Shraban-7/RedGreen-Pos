# AGENTS.md

This file gives coding agents (Kilo CLI, etc.) the conventions of this repo.
Read this file fully before writing or editing any code. When in doubt,
**open and read the existing similar file first** (an existing repository,
service, controller, store, or page) and copy its patterns instead of
inventing new ones.

---

## 1. Project shape

This is a monorepo with two independent apps:

```
/                     <- Laravel 12 API app (root)
  app/
    Models/
    Repositories/<Model>/<Model>RepositoryInterface.php
    Repositories/<Model>/<Model>Repository.php
    Services/<Model>Service.php
    Http/Controllers/...
    Enums/
    utils/helpers.php
  routes/api.php
  database/migrations, factories, seeders

/frontend             <- Vue 3 SPA (pure API consumer, NO Inertia)
  src/
    layouts/
    pages/
    components/
    stores/           <- Pinia
    router/
    services/ (or api/)  <- axios instance
```

- Backend is a **pure JSON API**. No Blade views for app pages, no Inertia.
- Frontend is a **separate SPA** talking to the API over axios + Bearer
  tokens (Sanctum). Never assume shared session/CSRF flows unless you
  find evidence of it in `bootstrap`/`config/sanctum.php`.
- Multiple auth guards exist: `admin`, `seller`, `employee` (see
  `app/utils/helpers.php` — `admin()`, `seller()`, `employee()` helpers).
  Confirm which guard a POS feature belongs to before writing routes —
  look at existing route groups in `routes/api.php` first.

---

## 2. Backend architecture — Repository + Service pattern

Every domain module follows this exact 3-layer pattern. **Do not skip
layers or put query logic in controllers or business logic in
repositories.**

```
Controller  -> validates request, calls Service, returns response via helpers
Service     -> business logic, calculations, file handling, calls Repository
Repository  -> Eloquent queries only, implements an Interface
```

### Repository

```php
namespace App\Repositories\{Model};

interface {Model}RepositoryInterface
{
    public function all();
    public function paginate($limit = 20);
    public function find($id);
    public function findBySlug($slug); // only if model has a slug
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}
```

```php
namespace App\Repositories\{Model};

use App\Models\{Model};

class {Model}Repository implements {Model}RepositoryInterface
{
    public function all() { return {Model}::with([...relations])->get(); }
    public function paginate($limit = 20) { return {Model}::latest()->paginate($limit); }
    public function find($id) { return {Model}::findOrFail($id); }
    public function create(array $data) { return {Model}::create($data); }
    public function update($id, array $data)
    {
        $record = $this->find($id);
        $record->update($data);
        return $record;
    }
    public function delete($id)
    {
        $record = $this->find($id);
        return $record->delete();
    }
}
```

Interfaces are bound to implementations in a service provider (find it —
likely `RepositoryServiceProvider` — before adding a new repository, and
register the new binding there, don't hand-resolve with `app()->make()`
in controllers).

### Service

- Holds all business logic: SKU/code generation, slug generation, discount
  / VAT math, stock deduction, file uploads.
- Constructor-injects the Repository **interface**, never the concrete
  class.
- Always sets `user_id` (or the relevant actor id) from `auth()->id()` —
  follow the exact pattern in `ProductService::store()`.
- Use the existing helpers instead of writing new ones:
  - `str_slug($table, $column, $title, $separator = '-', $ignoreId = null)`
  - `upload_file($file, $directory, $disk = 'public')` /
    `delete_file($file)` / `storage_url($file)`
  - `calculate_discount_amount($price, $type, $value)` /
    `calculate_discounted_price($price, $type, $value)`
  - `calculate_vat($vatPercentage, $price)`
  - `money($amount, $showCurrency = true)` for formatted display amounts
  - Never re-implement discount, VAT, slug, or file-upload logic inline.

### Controller

- Thin. Validates via `validateRequest()` / Form Requests (check which
  pattern existing controllers use before picking one), calls the
  Service, returns via the standard response helpers below.
- Never query Eloquent models directly in a controller.

---

## 3. API response contract

**Always** use these existing helpers from `app/utils/helpers.php` — do
not hand-roll `response()->json()` calls:

| Helper | Use for |
|---|---|
| `apiResponse($data, $message = null, $statusCode = 200)` | success + raw data (array/object) |
| `apiResourceResponse($resourceCollection, $message = null, $extraData = [], $statusCode = 200)` | success + an API Resource / paginated collection |
| `successResponse($message, $statusCode = 200)` | success, no payload (e.g. delete) |
| `errorResponse($message, $statusCode = 400)` | business/logic errors |
| `sendValidationError($errors)` | manual validator failures (422) |

Response envelope shape is always:
```json
{ "status": true|false, "message": "...", "data": {...}, "extraData": {...} }
```
Match this exactly for every new endpoint — the frontend axios wrapper
assumes this shape.

---

## 4. Frontend conventions (Vue 3 SPA)

Stack: Vue 3 (`<script setup>`), Vue Router 4, Pinia 3, PrimeVue 4 +
`@primeuix/themes`, Tailwind 4 + `tailwindcss-primeui`, axios, FontAwesome,
Quill (rich text where needed).

### HTTP client — exact pattern, do not deviate

- Axios instance lives at `src/axios/axios.js`:
  - `baseURL: 'http://pos.test/api'`, `timeout: 15000`,
    `headers: { Accept: 'application/json' }`.
  - Request interceptor reads the token from `localStorage.getItem('token')`
    and sets `Authorization: Bearer {token}`.
  - Request interceptor strips `Content-Type` when `config.data instanceof
    FormData` (so the browser sets the multipart boundary) — do this for
    any endpoint that uploads files (e.g. product thumbnail, receipt
    logo).
  - Response interceptor: on `401`, clears `token` from `localStorage`
    and hard-redirects to `/login`. Don't add per-component 401 handling
    — it's already global.
  - This instance is registered as a global property (`$api`) via a Vue
    plugin at app bootstrap (see `main.js`) — that's why the composable
    below can pull it off `getCurrentInstance().proxy.$api`.
- **Always** call the API through the `useApi()` composable
  (`src/composables/useApi.js`), never `import axios from 'axios'`
  directly in a component/store:

  ```javascript
  import { useApi } from '@/composables/useApi';

  const api = useApi();
  const { data } = await api.get('/products');
  ```

  In Pinia stores (which run outside component `setup()`, so
  `getCurrentInstance()` isn't available there), import the axios
  instance directly instead: `import api from '@/axios/axios'`. Match
  whichever of the two an existing store already uses before adding a
  new one.

- API responses follow `{status, message, data}` (see section 3) —
  unwrap `data` in the store action, surface `message` on failure via
  the app's existing toast/notification pattern (check PrimeVue
  `Toast`/`useToast()` usage in an existing page first).
- On `422` validation errors, the backend returns
  `{status: false, message: <first error>}` via `sendValidationError()`
  — there is no field-by-field error map by default; don't assume
  Laravel's raw `errors: {field: [...]}` shape unless you confirm a
  controller returns it that way.

### Stores, pages, routing

- **Before writing a new store/page**, open one existing file in
  `src/stores` and one in `src/pages` and mirror: naming, loading/error
  state shape, and PrimeVue component choices (DataTable, InputText,
  Select/Dropdown, Dialog, Toast, etc. — don't introduce a different UI
  kit).
- Pinia stores use the setup-store or options style **already used in
  the codebase** — check before adding a new one; be consistent, don't
  mix styles across stores.
- Routes are registered in `src/router/index.js` with per-guard route
  meta (e.g. `meta: { requiresAuth: true, guard: 'admin' }`) — follow
  whatever pattern already exists there for admin/seller/employee split.
- Sidebar navigation is data-driven from `src/.../sidebarLinks.js`
  (`menuLinks` array, items are `{ name, path, icon }` or
  `{ name, icon, children: [{ name, path }] }`). **Any new POS page
  must be registered here too**, e.g.:

  ```javascript
  {
    name: "POS",
    icon: "pi pi-calculator",
    children: [
      { name: "New Sale", path: "/pos/create" },
      { name: "Sales History", path: "/pos/sales" }
    ]
  }
  ```

  Note there's already an `Orders` entry (`/orders`, `pi pi-shopping-cart`)
  — confirm with existing code/routes whether "Orders" and "POS Sales"
  are meant to be the same thing before creating a parallel, redundant
  module. If POS sales and online Orders are different concepts in this
  app, keep them as separate menu groups as above; if they're the same
  underlying resource, extend `Orders` instead of duplicating it.

---

## 5. Domain rules relevant to POS

- Currency is BDT, symbol `৳` (`currency_symbol()` / `money()` helpers).
- Discounts: `discount_type` (`percentage` | `flat`, see `DiscountType`
  enum) + `discount_value` → always derive `discount_amount` and
  `discounted_price` via the helpers, never compute manually.
- VAT: use `calculate_vat($vatPercentage, $price)`.
- SKU pattern already used for products: `PRD-{Ymd}-{RANDOM6}`. Reuse the
  **same style** (prefix changes per entity) for any new POS entity code
  (e.g. `INV-`, `SALE-`, `PAY-`) — do not invent a different format.
- File uploads (product thumbnails, receipts if ever rasterized) go
  through `upload_file()` and are deleted via `delete_file()` on
  replace/delete — mirror `ProductService::update()`/`deleteProduct()`.

---

## 6. Before implementing any POS feature — agent checklist

1. **Read first, write second.** Open `app/Models`, `app/Repositories`,
   `app/Services`, `app/Http/Controllers`, `routes/api.php`, the
   RepositoryServiceProvider, and the relevant `src/stores` + `src/pages`
   files that already exist (Product, Category, Supplier, User) before
   writing a single line for POS.
2. Check whether `Customer`, `Order`/`Sale`, `SaleItem`/`OrderItem`,
   `Payment`, and any `Stock`/inventory model **already exist**. Do not
   create duplicate tables/models — extend what's there.
3. Match existing migration naming/timestamp conventions
   (`database/migrations`) and existing enum conventions
   (`app/Enums/*.php`, e.g. `DiscountType`, `AdminRole`) when adding new
   enums (e.g. `PaymentMethod`, `SaleStatus`).
4. Every new table needs: migration, model (with `$fillable`/casts
   matching sibling models), repository interface + implementation,
   service, controller, API routes, and — if the interface is new —
   a binding in the RepositoryServiceProvider.
5. Every new endpoint needs a matching Pinia store action + a page/
   component wired to it, following the existing PrimeVue/Tailwind
   look of the Products/Suppliers pages.
6. Run/extend Pest tests (`tests/`) for new services — this repo uses
   Pest 3, not PHPUnit-style classes, for new tests.
7. Never touch `.env`, run destructive migrations, or push git changes
   without being explicitly asked.

---

## 7. Feature: POS — Create Sale

**Goal:** cashier-facing screen to build a sale and check out.

Backend:
- `SaleRepositoryInterface`/`SaleRepository`, `SaleService`.
- Endpoint to search/list sellable products (reuse
  `ProductRepositoryInterface`, filter by stock > 0 and active status —
  check `Product` model for the actual stock/status columns first).
- `POST /api/{guard}/pos/sales` — accepts `customer_id?`, `items[]`
  (`product_id`, `quantity`, `unit_price` override optional),
  `discount_type?`, `discount_value?`, `payment_method`, `paid_amount`.
- `SaleService::store()` must, inside a **DB transaction**:
  1. Re-validate stock for every line item (lock rows / re-check qty).
  2. Compute per-item discount + VAT via existing helpers.
  3. Compute grand total, due/change amount.
  4. Decrement product stock.
  5. Generate a sale code (`SALE-{Ymd}-{RANDOM6}` style, matching the
     product SKU convention).
  6. Create `Sale`, `SaleItem[]`, `Payment` records.
  7. Roll back everything on any failure (insufficient stock, etc.) and
     return `errorResponse()`.
- Return the created sale via `apiResponse()` including items + totals,
  ready for receipt rendering.

Frontend (`/frontend/src`):
- `stores/pos.js` (Pinia): cart state (line items, quantities, live
  totals via computed/getters), `addToCart`, `updateQty`, `removeItem`,
  `applyDiscount`, `checkout()` action that calls the API.
- `pages/pos/Create.vue` (or similar, match existing page naming):
  product search/grid (PrimeVue DataView or similar — check existing
  list pages for the pattern used), cart panel, customer picker,
  payment method + amount input, checkout button.
- On success, show a **receipt view** (print-friendly component) with
  sale code, items, totals, paid/change — check whether a print/PDF
  pattern already exists in the repo before introducing `window.print()`
  vs a PDF endpoint.

---

## 8. Feature: POS — Update Sale

**Goal:** edit/adjust an existing sale (before it's finalized/reconciled)
or perform post-sale corrections.

Decide scope with existing status enum conventions — typical POS update
scenarios, confirm which apply here before building all of them:

- **Edit while still open/draft** (if sales have a draft state): change
  quantities, add/remove items, change discount or payment method —
  re-run the same total/stock recalculation as create, restoring stock
  for removed lines and deducting stock for added lines.
- **Void/cancel a completed sale**: restore all stock, mark sale as
  voided, keep an audit trail (don't hard-delete).
- **Partial refund / return**: reduce specific line quantities, restore
  that stock, record a negative payment/refund entry, recompute totals.
- **Payment adjustment**: record an additional payment against a sale
  with outstanding due (`paid_amount` less than total).

Backend:
- `PUT/PATCH /api/{guard}/pos/sales/{sale}` for draft edits.
- `POST /api/{guard}/pos/sales/{sale}/void` for cancellation.
- `POST /api/{guard}/pos/sales/{sale}/refund` for returns.
- `POST /api/{guard}/pos/sales/{sale}/payments` for adding a payment.
- Every mutation wrapped in a DB transaction; every stock change goes
  through one central method (e.g. `StockService::adjust()`) so stock
  logic isn't duplicated between create/update/void/refund — **check if
  a stock/inventory service already exists before writing a new one.**
- Log who performed the update (`user_id`/`employee_id` + timestamp) —
  match whatever audit pattern (if any) exists elsewhere in the app
  (e.g. `updated_by` columns, activity log package in `composer.json`).

Frontend:
- `stores/pos.js`: `loadSale(id)`, `updateSale()`, `voidSale()`,
  `refundItems()`, `addPayment()` actions.
- A sale detail/edit page reusing the cart component from Create where
  possible instead of duplicating markup.
- Guard destructive actions (void/refund) behind a PrimeVue `ConfirmDialog`
  — check if that pattern is already used elsewhere (e.g. product
  delete) and reuse it.

---

## 9. Feature: Auth guard — redirect to login on app load if not authenticated

Currently the axios response interceptor only redirects to `/login` on a
`401` *after* a request fails — there's no check on initial app load
before any API call happens. Add a proper router guard:

- In `src/router/index.js`, add a global `router.beforeEach` navigation
  guard:
  - Read `localStorage.getItem('token')`.
  - Routes need a `meta: { requiresAuth: true }` flag (add it to every
    existing protected route if not already present, and to every new
    POS/report/expense route).
  - If a route requires auth and there's no token → redirect to
    `/login`. Preserve the intended destination
    (`redirect: to.fullPath` as a query param) so login can send the
    user back afterward.
  - If a route is `/login` and a token already exists, redirect straight
    to `/dashboard` instead of showing the login form again.
- Do **not** duplicate this logic elsewhere — the axios 401 interceptor
  stays as the *fallback* for expired/invalid tokens caught mid-session;
  the router guard is the *first-load* / navigation-time check. Both are
  needed, they cover different moments.
- Token presence alone is enough for the guard (don't add a token
  validity API call on every navigation — that belongs on login and can
  be handled by the interceptor for expired tokens).

---

## 10. Feature: Dashboard

Landing page after login (`/dashboard`), summarizing store performance.
Check first whether a dashboard page/route/controller already exists —
extend rather than duplicate.

Backend:
- `GET /api/{guard}/dashboard` — a single endpoint returning everything
  the dashboard needs in one payload (avoid N separate calls / N+1
  waterfall on load). Suggested shape via `apiResponse()`:
  ```json
  {
    "todaySales": { "count": 0, "total": 0 },
    "todayExpenses": 0,
    "monthSales": { "count": 0, "total": 0 },
    "monthExpenses": 0,
    "lowStockProducts": [ { "id": 1, "name": "...", "stock": 2 } ],
    "recentSales": [ ... last 5-10 sales ... ],
    "salesChart": { "labels": [...dates], "data": [...totals] } // last 7/30 days
  }
  ```
- Build via a `DashboardService` that composes data from
  `SaleRepository`, `ExpenseRepository`, `ProductRepository` — don't put
  raw queries in the controller.
- Cache the heavier aggregates briefly (`Cache::remember`, matching the
  `settings()`/`hasPermission()` caching style already in
  `app/utils/helpers.php`) since dashboards get hit on every login.

Frontend:
- `src/stores/dashboard.js` + `src/pages/Dashboard.vue` (check exact
  existing filename/route first).
- Use PrimeVue `Card`/stat-tile components for the KPI numbers (today's
  sales, today's expenses, low stock count, etc.) and a chart (check if
  a charting lib is already installed — none is in `package.json` yet,
  so either add one consistent with PrimeVue theming, e.g. Chart.js via
  PrimeVue's built-in `Chart` component which wraps chart.js, and use
  that rather than introducing a second charting library).
- Low-stock list links into the existing Products page filtered/sorted
  by stock.

---

## 11. Feature: Reports (Sales, Customers, Expenses, Overall)

All reports need a **date range filter** (default: current month) and
should support exporting or at least printing — check if the repo
already has an export pattern (CSV/PDF) before introducing one.

Backend — one `ReportController` (or split per report, match whichever
is more consistent with existing controller granularity) calling a
`ReportService`:

- `GET /api/{guard}/reports/sales?from=&to=&customer_id=&status=`
  → totals (gross, discount, VAT, net), count, breakdown by day, top
  products sold, payment-method breakdown.
- `GET /api/{guard}/reports/customers?from=&to=`
  → per-customer: total spent, number of sales, last purchase date,
  average order value. Sortable by total spent.
- `GET /api/{guard}/reports/expenses?from=&to=&category_id=`
  → total expenses, breakdown by category, list of expense entries in
  range.
- `GET /api/{guard}/reports/overall?from=&to=`
  → combines the above: total sales, total expenses, net profit
  (sales net − expenses), sales vs expense trend by day/week.
- All report queries live in the repository layer (e.g.
  `SaleRepository::sumBetween()`, `ExpenseRepository::sumBetween()`,
  grouped queries) — services just compose/format, following the same
  layering as everything else in this repo. Use query aggregation
  (`SUM`, `GROUP BY` via Eloquent/DB query builder) rather than pulling
  all rows into PHP and summing in a loop.

Frontend:
- `src/pages/reports/Sales.vue`, `Customers.vue`, `Expenses.vue`,
  `Overall.vue` (or a single `Reports.vue` with tabs — pick whichever
  matches how multi-view pages are already structured elsewhere, e.g.
  check if PrimeVue `Tabs`/`TabView` is used anywhere).
- Shared date-range picker component (PrimeVue `DatePicker` in range
  mode) reused across all four report pages instead of duplicated per
  page.
- Add a "Reports" entry with children to `menuLinks` in
  `sidebarLinks.js`:
  ```javascript
  {
    name: "Reports",
    icon: "pi pi-chart-bar",
    children: [
      { name: "Sales Report", path: "/reports/sales" },
      { name: "Customers Report", path: "/reports/customers" },
      { name: "Expenses Report", path: "/reports/expenses" },
      { name: "Overall Report", path: "/reports/overall" }
    ]
  }
  ```

---

## 12. Feature: Expense — full CRUD

Follows the exact same repository/service/controller pattern as Product
(section 2) — do not deviate.

Backend:
- Migration: `expenses` table — at minimum `title`, `amount`,
  `expense_category_id` (FK), `note`/`description`, `expense_date`,
  `user_id`, `attachment` (nullable, receipt/invoice image — reuse
  `upload_file()`/`delete_file()` exactly like `ProductService` does for
  thumbnails), timestamps.
- If there's no expense-category concept yet, add a lightweight
  `expense_categories` table + simple CRUD (mirror `Category` for
  products) rather than free-text categories.
- `ExpenseRepositoryInterface`/`ExpenseRepository`,
  `ExpenseService` — mirror `ProductRepository`/`ProductService`
  structure exactly (all/paginate/find/create/update/delete, plus
  whatever date-range/category filtering the report feature above
  needs, e.g. `sumBetween($from, $to)`).
- Routes (mirror Product routing style found in `routes/api.php`):
  - `GET    /api/{guard}/expenses`
  - `GET    /api/{guard}/expenses/{expense}`
  - `POST   /api/{guard}/expenses`
  - `PUT    /api/{guard}/expenses/{expense}`
  - `DELETE /api/{guard}/expenses/{expense}`
  - `GET    /api/{guard}/expense-categories` (+ CRUD if categories are
    added as their own resource)
- Bind the new interface in the RepositoryServiceProvider.
- Validate `amount` as numeric/positive, `expense_date` as a valid date
  not in the future (confirm this rule makes sense for this business
  before enforcing it).
- Use `apiResponse`/`apiResourceResponse`/`errorResponse`/
  `sendValidationError` — same as every other module.

Frontend:
- `src/stores/expense.js` mirroring the existing product store's
  structure/action names.
- `src/pages/expenses/Index.vue` (PrimeVue DataTable, same
  columns/actions style as the Products list — search, paginate, edit,
  delete with `ConfirmDialog`) and `src/pages/expenses/Create.vue` (or a
  single page with a create/edit Dialog — match whichever pattern
  Products already uses).
- Add `Expenses` to `menuLinks` in `sidebarLinks.js`
  (`icon: "pi pi-money-bill"` or similar), and register routes in
  `src/router/index.js` with `meta: { requiresAuth: true }`.

---

## 13. What NOT to do

- Don't bypass the repository/service layers "for speed."
- Don't invent a different API response envelope.
- Don't add a new frontend HTTP client instance — reuse the existing
  axios wrapper.
- Don't introduce a new CSS/UI framework alongside PrimeVue + Tailwind.
- Don't compute discounts, VAT, or slugs manually — use the helpers.
- Don't assume table/column names — inspect the actual migration/model
  before referencing a field.