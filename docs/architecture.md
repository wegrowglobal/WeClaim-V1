# WeClaim-V1 System Architecture

## Overview

WeClaim is built using the Laravel PHP framework, following a modern web application architecture pattern. The system is designed around a core domain model that represents expense claims, their approval workflows, and supporting elements.

## High-Level Architecture

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│  Web Interface  │     │     API Layer   │     │  External APIs  │
│  (Blade/Livewire) ◄─►  │  (Controllers)  │ ◄─► │ (Maps, Email)   │
└─────────────────┘     └────────┬────────┘     └─────────────────┘
                                  │
                        ┌─────────▼────────┐
                        │  Service Layer   │
                        │                  │
                        └────────┬────────┘
                                  │
                        ┌─────────▼────────┐     ┌─────────────────┐
                        │   Data Models    │ ◄─► │    Database     │
                        │   (Eloquent)     │     │    (MySQL)      │
                        └─────────────────┘     └─────────────────┘
```

## Core Components

### 1. User Interface Layer

- **Blade Templates**: Responsible for rendering HTML views
- **Livewire Components**: Handle dynamic frontend interactions without full page reloads
- **AlpineJS**: Provides lightweight JavaScript functionality within components

### 2. Controller Layer

Controllers handle HTTP requests and delegate business logic to service classes:

- **ClaimController**: Manages claim creation, updates, approvals
- **UserController**: Handles user authentication and profile management
- **RegistrationRequestController**: Manages new user registrations
- **NotificationController**: Handles notifications
- **UserManagementController**: Admin-only user management functions
- **SystemConfigController**: Manages system configuration

### 3. Service Layer

The service layer contains the core business logic of the application:

- **ClaimService**: Handles claim operations, calculations, and workflow
- **NotificationService**: Manages sending and tracking notifications
- **ClaimExportService**: Handles exporting claims to various formats
- **ClaimTemplateMapper**: Maps claim data to export templates

### 4. Model Layer

Models represent the database entities and their relationships:

- **User**: System users with various roles
- **Claim**: The central entity representing expense claims. Modified to include specific fields for Petty Cash (`park_location`, `advised_by`, `total_amount`) and a general `claim_type` to distinguish between types like 'Petrol' and 'Petty Cash'.
- **ClaimLocation**: Travel points within a claim (primarily for Petrol claims)
- **ClaimAccommodation**: Accommodation details for a claim
- **ClaimDocument**: Supporting documents attached to claims
- **ClaimReview**: Review entries from approvers
- **ClaimHistory**: Historical record of claim changes
- **PettyCashItem**: Represents individual line items within a Petty Cash claim, linked via a one-to-many relationship from `Claim`. Each item includes details like `item_name`, `quantity`, `price_per_unit`, and `purpose`.
- **Role**: User roles (Staff, Admin, HR, Finance, Director)
- **Department**: Organizational departments
- **SystemConfig**: Application configuration settings

## Data Flow

### Claim Submission Flow

```
┌───────────┐     ┌───────────┐     ┌───────────┐     ┌───────────┐
│   Form    │     │Controller │     │  Service  │     │  Models   │
│           │ ─►  │           │ ─►  │           │ ─►  │           │
└───────────┘     └───────────┘     └───────────┘     └───────────┘
      │                 │                 │                 │
      │                 │                 │      Save Claim │
      │                 │                 │ ─────────────► │
      │                 │                 │                 │
      │                 │                 │  Create Review │
      │                 │                 │ ─────────────► │
      │                 │                 │                 │
      │                 │     Return      │                 │
      │                 │ ◄───────────────┤                 │
      │     Response    │                 │                 │
      │ ◄───────────────┤                 │                 │
      │                 │                 │                 │
```

### Claim Approval Flow

```
┌───────────┐     ┌───────────┐     ┌───────────┐    ┌───────────┐     ┌───────────┐
│  Admin    │     │Controller │     │  Service  │    │  Models   │     │ Email     │
│  Review   │ ─►  │           │ ─►  │           │ ─► │           │ ─►  │ Service   │
└───────────┘     └───────────┘     └───────────┘    └───────────┘     └───────────┘
      │                 │                 │                │                  │
      │                 │                 │   Update Status│                  │
      │                 │                 │ ────────────► │                  │
      │                 │                 │                │                  │
      │                 │                 │  Create Review │                  │
      │                 │                 │ ────────────► │                  │
      │                 │                 │                │                  │
      │                 │                 │                │    Send Email    │
      │                 │                 │ ─────────────────────────────────│
      │                 │                 │                │                  │
      │                 │     Return      │                │                  │
      │                 │ ◄───────────────┤                │                  │
      │     Response    │                 │                │                  │
      │ ◄───────────────┤                 │                │                  │
      │                 │                 │                │                  │
```

## Database Schema

Key database relationships:

- **User** 1→N **Claim**: A user can have multiple claims
- **Claim** 1→N **ClaimLocation**: A claim can have multiple locations (primarily for Petrol claims)
- **Claim** 1→N **ClaimAccommodation**: A claim can have accommodation details
- **Claim** 1→N **ClaimDocument**: A claim can have multiple supporting documents
- **Claim** 1→N **ClaimReview**: A claim can have multiple reviews
- **Claim** 1→N **ClaimHistory**: A claim can have multiple history entries
- **Claim** 1→N **PettyCashItem**: A claim of type 'Petty Cash' can have multiple line items.
- **Role** 1→N **User**: A role can be assigned to multiple users
- **Department** 1→N **User**: A department can have multiple users

## System Boundaries and Integrations

External systems integrated with WeClaim:

1. **Email Service**: For notifications and approvals
2. **Google Maps API**: For distance calculation and location validation
3. **Azure Storage**: For document storage (optional)

## Security Architecture

- **Authentication**: Laravel Fortify authentication framework
- **Authorization**: Role-based access control via middleware
- **Cross-Site Request Forgery (CSRF)**: Laravel CSRF protection
- **Input Validation**: Form requests for validation
- **Email Verification**: Required for new accounts
- **Password Policies**: Enforced for all users

## Caching Strategy

- **Application Cache**: For system configuration and static data
- **Session Cache**: For multi-step form data
- **Query Cache**: For frequently accessed database queries

## Deployment Architecture

```
┌─────────────────────────────────────────────────┐
│                  Web Server                      │
│                                                  │
│  ┌─────────────┐  ┌─────────────┐ ┌──────────┐  │
│  │  Laravel    │  │  Web Server │ │ Scheduler │  │
│  │ Application │  │  (Nginx)    │ │          │  │
│  └─────────────┘  └─────────────┘ └──────────┘  │
└─────────────────────────────────────────────────┘
                          │
           ┌─────────────┴─────────────┐
           │                           │
┌──────────▼───────┐        ┌──────────▼───────┐
│                  │        │                  │
│   MySQL Database │        │  Email Server    │
│                  │        │                  │
└──────────────────┘        └──────────────────┘
```

## Performance Considerations

- **Database Indexing**: Optimized for frequent query patterns
- **Caching**: Strategic caching for static and calculated data
- **Pagination**: Implemented for large data sets
- **Query Optimization**: Eager loading of relationships to minimize N+1 problems
- **Asset Management**: Frontend assets minification and bundling 