# WeClaim Technical Documentation

## Project Structure

The WeClaim application follows Laravel's standard directory structure with additional organization for domain-specific code:

```
app/
├── Console/                # Console commands
├── Exceptions/             # Exception handlers
├── Http/
│   ├── Controllers/        # Request handlers
│   ├── Middleware/         # Request filters
│   └── Requests/           # Form validation
├── Livewire/               # Livewire components
├── Mail/                   # Mail templates
├── Models/                 # Eloquent models
├── Notifications/          # Notification classes
├── Providers/              # Service providers
├── Services/               # Business logic services
├── Traits/                 # Reusable traits
└── View/                   # View composers
```

## Coding Standards

### PHP Coding Style

The project follows PSR-12 coding standards with some additional conventions:

- Class names: PascalCase (e.g., `ClaimController`)
- Method names: camelCase (e.g., `createClaim()`)
- Property names: camelCase (e.g., `$claimService`)
- Constants: UPPER_CASE (e.g., `STATUS_APPROVED`)
- Indentation: 4 spaces (no tabs)
- Line length: 120 characters maximum

### Database Naming Conventions

- Table names: snake_case, plural (e.g., `claims`, `claim_documents`)
- Column names: snake_case (e.g., `user_id`, `first_name`)
- Primary keys: `id` (auto-increment)
- Foreign keys: singular model name + `_id` (e.g., `user_id`)
- Pivot tables: singular model names in alphabetical order (e.g., `claim_user`)
- Boolean columns: prefixed with `is_`, `has_`, or `should_` (e.g., `is_approved`)
- Timestamp columns: `created_at`, `updated_at`, `deleted_at` (for soft deletes)

## Key Design Patterns & Conventions

### Service Pattern

Business logic is encapsulated in dedicated service classes to keep controllers thin:

```php
// Controller (keeps logic minimal)
public function store(StoreClaimRequest $request)
{
    $claim = $this->claimService->createClaim(
        $request->validated(),
        Auth::id()
    );
    
    return redirect()->route('claims.view', $claim->id);
}

// Service (contains business logic)
public function createClaim(array $validatedData, int $userId): Claim
{
    // Complex business logic here
}
```

### Repository Pattern

While not implemented currently, future versions may implement repositories to abstract database operations from services:

```php
// Before: Using Eloquent directly in service
public function getClaims(User $user)
{
    return Claim::where('user_id', $user->id)->get();
}

// After: Repository pattern
public function getClaims(User $user)
{
    return $this->claimRepository->getByUser($user);
}
```

### Form Request Validation

Form validation logic is encapsulated in dedicated Request classes:

```php
// app/Http/Requests/StoreClaimRequest.php
public function rules()
{
    return [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'claim_type' => 'required|in:Petrol,Accommodation,Food',
        // ...
    ];
}
```

### Middleware Pipeline

Role-based access control and other cross-cutting concerns are implemented as middleware:

```php
// In Controller constructor
public function __construct()
{
    $this->middleware('auth');
    $this->middleware('role:admin')->only(['adminIndex', 'destroy']);
    $this->middleware('profile.complete')->except(['show']);
}
```

### View Components & Partials

Reusable UI elements are extracted into Blade components and partials:

```php
// Using components
<x-alert type="danger" :message="$errorMessage" />

// Using partials
@include('partials.claim-card', ['claim' => $claim])
```

## Authentication & Authorization

### Authentication

Authentication is handled through Laravel's built-in authentication system with customizations:

- **Login**: Email/password authentication
- **Registration**: Requires approval by an admin
- **Password Reset**: Email-based password reset flow
- **Session Management**: 2-hour inactivity timeout

### Role-Based Authorization

Authorization is implemented using a role-based system:

```
Role Hierarchy:
- Staff (role_id: 1)
- Admin (role_id: 2)
- HR (role_id: 3)
- Finance (role_id: 4)
- Director (role_id: 5)
```

Role permissions are enforced via middleware and gates:

```php
// Using middleware
Route::middleware('role:2,3,4')->group(function () {
    // Routes accessible to Admin, HR, and Finance
});

// Using gates
Gate::define('approve-claim', function (User $user, Claim $claim) {
    // Complex approval logic here
});
```

## Data Models & Relationships

### Core Models

- **User**: System users with various roles
- **Claim**: The central entity representing expense claims
- **ClaimLocation**: Travel points within a claim
- **ClaimDocument**: Supporting documents attached to claims
- **ClaimReview**: Review entries from approvers
- **ClaimHistory**: Historical record of claim changes

### Key Relationships

```php
// User model
public function claims()
{
    return $this->hasMany(Claim::class);
}

public function role()
{
    return $this->belongsTo(Role::class);
}

// Claim model
public function user()
{
    return $this->belongsTo(User::class);
}

public function locations()
{
    return $this->hasMany(ClaimLocation::class);
}

public function documents()
{
    return $this->hasMany(ClaimDocument::class);
}

public function reviews()
{
    return $this->hasMany(ClaimReview::class);
}

public function history()
{
    return $this->hasMany(ClaimHistory::class);
}
```

## Workflow & State Management

### Claim States

```
Claim Status Flow:
┌─────────────┐      ┌─────────────┐      ┌─────────────┐      ┌─────────────┐
│  Submitted  │ ──► │ AdminReview  │ ──► │  HRReview   │ ──► │FinanceReview │
└─────────────┘      └─────────────┘      └─────────────┘      └─────────────┘
       │                   │                   │                     │
       │                   │                   │                     │
       ▼                   ▼                   ▼                     ▼
┌─────────────┐      ┌─────────────┐      ┌─────────────┐      ┌─────────────┐
│   Rejected  │      │   Rejected  │      │   Rejected  │      │   Rejected  │
└─────────────┘      └─────────────┘      └─────────────┘      └─────────────┘
                                                                     │
                                                                     │
                                                                     ▼
                                                              ┌─────────────┐
                                                              │ DatukReview │
                                                              └─────────────┘
                                                                     │
                                                                     │
                                       ┌─────────────┐               │
                                       │   Rejected  │ ◄─────────────┘
                                       └─────────────┘               │
                                                                     │
                                                                     ▼
                                                              ┌─────────────┐
                                                              │    Done     │
                                                              └─────────────┘
```

### Status Constants

Status constants are defined in the Claim model:

```php
// In Claim model
public const STATUS_SUBMITTED = 'Submitted';
public const STATUS_ADMIN_REVIEW = 'AdminReview';
public const STATUS_HR_REVIEW = 'HRReview';
public const STATUS_FINANCE_REVIEW = 'FinanceReview';
public const STATUS_DATUK_REVIEW = 'DatukReview';
public const STATUS_DONE = 'Done';
public const STATUS_REJECTED = 'Rejected';
```

## API & Integration Points

### Internal APIs

The system currently does not expose a public API but uses internal endpoints for AJAX operations:

```
POST /claims/store                # Create a new claim
POST /claims/{id}/update          # Update an existing claim
POST /claims/send-to-datuk/{id}   # Send a claim to the director for final approval
```

### External Integrations

- **Google Maps API**: Used for location validation and distance calculation
- **SMTP Mail Server**: Used for email notifications

## Frontend Technologies

### CSS Framework

Tailwind CSS is used for styling with custom components:

```html
<button class="btn btn-primary">Submit</button>

<!-- Compiles to -->
<button class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">
    Submit
</button>
```

### JavaScript Libraries

- **Alpine.js**: For reactive components
- **Axios**: For AJAX requests
- **Leaflet**: For map interactions
- **Flatpickr**: For date pickers

## Testing Strategy

### Testing Levels

- **Unit Tests**: For isolated service and model logic
- **Feature Tests**: For controller actions and workflows
- **Browser Tests**: For UI interactions (using Laravel Dusk)

### Test Organization

Tests are organized in the `tests` directory:

```
tests/
├── Feature/            # Feature tests
│   ├── ClaimTest.php
│   └── ...
├── Unit/               # Unit tests
│   ├── Services/
│   └── ...
└── Browser/            # Browser tests
    ├── ClaimSubmissionTest.php
    └── ...
```

## Environment & Configuration

### Environment Variables

Critical configuration is managed through environment variables:

- `APP_ENV`: Application environment (local, staging, production)
- `DB_*`: Database connection parameters
- `MAIL_*`: Mail server configuration
- `GOOGLE_MAPS_API_KEY`: Google Maps API key
- `DATUK_EMAIL`: Director's email address

### Feature Flags

Feature flags are managed through the `SystemConfig` model:

```php
// Check if a feature is enabled
if (SystemConfig::where('key', 'enable_accommodation_claims')->first()->value === 'true') {
    // Feature is enabled
}
```

## Logging & Monitoring

### Log Levels

- **Emergency**: System is unusable
- **Alert**: Action must be taken immediately
- **Critical**: Critical conditions
- **Error**: Error conditions
- **Warning**: Warning conditions
- **Notice**: Normal but significant condition
- **Info**: Informational messages
- **Debug**: Debug-level messages

### Log Channels

- **Single**: Single file log (local development)
- **Daily**: Rotating daily logs (production)
- **Slack**: Critical errors (optional)

## Data Export Formats

The system supports exporting claims in multiple formats:

- **Excel**: For financial reporting
- **Word**: For formal documentation
- **PDF**: For printable claim records

## Known Technical Debt

1. Large controller methods that should be refactored
2. Lack of comprehensive test coverage
3. Some database queries need optimization
4. JavaScript could benefit from more structure
5. Duplication between some service methods 