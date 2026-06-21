# WhatsApp SaaS Platform - Technical Requirements & Progress Tracker

This document serves as the master checklist and technical specification for building the WhatsApp SaaS Platform in Laravel. It outlines each module, backend/frontend architectural constraints, security details, and tracks implementation progress.

---

## 🛠️ Tech Stack & Infrastructure

### Backend
- [ ] Laravel 13 Setup & Configuration `[Pending]`
- [ ] PHP 8.3+ Compatibility `[Pending]`
- [x] MySQL 8+ Database Schema Design `[Completed]`
- [ ] Laravel Sanctum (Authentication & API Security) `[Pending]`
- [ ] Service Layer Architecture (Business logic separated from Controllers and Blade) `[Pending]`
- [ ] Repository Pattern (Optional/Decoupled database access) `[Pending]`
- [ ] Laravel Events & Listeners (For decoupled triggers, e.g., incoming messages, campaigns) `[Pending]`
- [ ] Laravel Jobs & Queue structure (Prepared for future Redis/worker switch, running synchronously or via database driver initially) `[Pending]`
- [ ] Laravel Scheduler (For campaign delivery checks & cleanups) `[Pending]`

### Frontend
- [ ] Blade Templates Structure (Layouts, Partials, Components) `[Pending]`
- [ ] CSS Variables / Theme System (No hardcoded colors, dynamic switching) `[Pending]`
- [ ] Bootstrap 5 Framework integration `[Pending]`
- [ ] jQuery (For DOM manipulations, ajax requests) `[Pending]`
- [ ] DataTables (For paginated, searchable lists) `[Pending]`
- [ ] Select2 (For search/dropdown selections) `[Pending]`
- [ ] SweetAlert2 & Notiflix (For beautiful alerts and UI notifications) `[Pending]`
- [ ] ApexCharts (For dashboard analytics visualization) `[Pending]`

### Infrastructure Requirements
- [ ] Shared Hosting Compatibility (Deployable without Redis, Docker, Node.js runtime, or daemonized WebSockets) `[Pending]`
- [ ] MySQL-backed queuing/scheduler support `[Pending]`
- [ ] Abstracted Smart Polling fallback (replaceable by WebSockets in the future) `[Pending]`

---

## 👥 Multi-Tenant Architecture & Data Isolation

To guarantee security, **every single database record must belong to a user (`user_id`)**. All database queries must be scoped to the authenticated user.

- [x] Add `user_id` foreign key constraint to:
  - [x] `whatsapp_accounts` `[Completed]`
  - [x] `contacts` `[Completed]`
  - [x] `contact_groups` `[Completed]`
  - [x] `conversations` `[Completed]`
  - [x] `messages` `[Completed]`
  - [x] `campaigns` `[Completed]`
  - [x] `templates` `[Completed]`
  - [x] `media_library` `[Completed]`
- [ ] Global Query Scopes or Repository-level filters to prevent cross-tenant data leaks `[Pending]`
- [ ] Verify that no API endpoint or page route can access another tenant's data via ID manipulation (IDOR prevention) `[Pending]`

---

## 🔌 WhatsApp Account Management

Allows users to manage multiple WhatsApp Business Accounts (WABAs) and configure credentials.

- [x] Database Schema for `whatsapp_accounts`:
  - `id` (Primary Key)
  - `user_id` (Foreign Key)
  - `display_name` (string)
  - `meta_access_token` (text, encrypted)
  - `phone_number_id` (string)
  - `whatsapp_business_account_id` (string)
  - `meta_app_id` (string)
  - `verify_token` (string, for webhook validation)
  - `status` (boolean/enum: active, inactive, verification_failed)
  - Timestamps `[Completed]`
- [x] UI Form & Controller for managing WABA accounts:
  - [x] Add Account Form `[Completed]`
  - [x] Edit Account Details `[Completed]`
  - [x] Enable/Disable Toggle `[Completed]`
- [x] Meta API Connection Verification:
  - [x] Test Connection API integration (Verify credentials against Graph API) `[Completed]`
  - [x] Send Test Message action (Sends template/text message to a specific number) `[Completed]`

---

## 🔗 Meta Webhook Architecture

A single webhook endpoint parses incoming Meta payloads and routes them to the correct user account based on `phone_number_id`.

- [ ] Unified endpoint: `/webhook/meta` (POST for messages/events, GET for Meta verification challenge) `[Pending]`
- [ ] Meta Webhook Signature Verification (Validating SHA256 signature using Meta App Secret) `[Pending]`
- [ ] Dynamically resolve tenant (`user_id`) via `phone_number_id` from the Meta payload `[Pending]`
- [ ] Webhook Event Handler:
  - [ ] Incoming Message Processing (Texts, Media, Location, Interactive) `[Pending]`
  - [ ] Message Status Updates (Sent -> Delivered -> Read -> Failed) `[Pending]`
  - [ ] Template Status Change notifications (Meta updates template approval status) `[Pending]`
  - [ ] Contact Profile Updates (Name changes/updates from Meta payload) `[Pending]`
- [ ] Webhook Payloads Logger (Audit trail database table/log files for debugging webhook payloads) `[Pending]`

---

## 📊 Dashboard

A high-performance SaaS dashboard visualizing tenant activity.

- [x] KPI Metrics Widgets (Aggregate counts per tenant):
  - [x] Total Contacts count `[Completed]`
  - [x] Total Groups count `[Completed]`
  - [x] Total Conversations count `[Completed]`
  - [x] Sent Messages Today `[Completed]`
  - [x] Delivered Messages Today `[Completed]`
  - [x] Read Messages Today `[Completed]`
  - [x] Failed Messages Today `[Completed]`
  - [x] Active WABA Accounts count `[Completed]`
- [x] Trend Analytics Charts (ApexCharts):
  - [x] Daily Messages volume (sent vs received vs failed) `[Completed]`
  - [x] Weekly Messages volume `[Completed]`
  - [x] Monthly Messages volume `[Completed]`
- [x] Activity Panels:
  - [x] Recent Activity Stream (Latest incoming/outgoing messages, webhook updates) `[Completed]`
  - [x] Campaign Summary list (Latest campaigns and progress percentages) `[Completed]`

---

## 📇 Contact & Group Management

### Contact Management
- [x] Database Schema for `contacts` (name, mobile_number, email, tags, notes, user_id) `[Completed]`
- [x] CRUD Endpoints & UI Forms for Contacts `[Completed]`
- [x] Contact Tagging System (Multi-tag support) `[Completed]`
- [x] Bulk Upload System (CSV parser for importing contacts with validation) `[Completed]`
- [x] Export System (Generate CSV download of filtered contacts list) `[Completed]`
- [x] Client-side & Server-side Search + Filters (DataTables integrated) `[Completed]`

### Contact Groups
- [x] Database Schema for `contact_groups` (name, user_id) and pivot table `contact_group_pivot` `[Completed]`
- [x] Group Management UI (Create, rename, delete) `[Completed]`
- [x] Contact Assignment Workflow (Add/remove contacts from groups in bulk) `[Completed]`

---

## 💬 WhatsApp Live Chat (Conversation Module)

A WhatsApp-like two-panel chat interface utilizing abstracted message retrieval.

### Layout & UI
- [ ] Left Sidebar:
  - [ ] Search input for contacts/messages `[Pending]`
  - [ ] Conversation list showing Contact Name, Unread Badge, Last Message Snippet, and Last Activity Timestamp `[Pending]`
- [ ] Right Panel:
  - [ ] Header: Active contact details, status, WABA selector `[Pending]`
  - [ ] Message History Thread: Bubble messages color-coded by source (Incoming, Outgoing, System) showing Delivery Status Icons `[Pending]`
  - [ ] Attachment button (Media Library modal integration) `[Pending]`
  - [ ] Message Input box with Send Action `[Pending]`

### Features
- [ ] Sending Text Messages via API `[Pending]`
- [ ] Sending Template Messages with dynamic parameters `[Pending]`
- [ ] Sending Media Messages (Images, Video, Documents, Audio) `[Pending]`
- [ ] Multi-Status tracker (Pending ➡️ Sent ➡️ Delivered ➡️ Read) `[Pending]`

---

## 🔄 Smart Polling & WebSocket-Ready Frontend Architecture

Designed to work via Polling initially, but built with interfaces that allow WebSockets to be swapped in with zero UI changes.

- [ ] CSS & JS Asset Directory layout:
  ```text
  public/assets/
  ├── css/
  │   ├── variables.css
  │   ├── components.css
  │   └── pages.css
  └── js/
      ├── services/
      │   ├── api-service.js
      │   └── chat-service.js
      └── providers/
          ├── polling-provider.js
          └── websocket-provider.js (placeholder for future implementation)
  ```
- [ ] `ChatProvider` Interface abstraction (`listen`, `sendMessage`, `onMessageReceived`, `onStatusChanged`) `[Pending]`
- [ ] `polling-provider.js` Implementation (Smart Polling logic):
  - [ ] Polls only the currently open conversation thread `[Pending]`
  - [ ] Checks for updates using the latest message ID or timestamp (no full-thread reloading) `[Pending]`
  - [ ] Polls the conversation list separately at a lower frequency `[Pending]`
- [ ] `ChatService` layer which references `ChatProvider` and decouples UI views from AJAX/polling logic `[Pending]`

---

## 📋 Template Management

Synchronizes WhatsApp Templates directly from the Meta Cloud API.

- [x] Database Schema for `templates`:
  - `id`, `user_id`, `meta_template_id`, `name`, `language`, `category`, `status`, `components` (json) `[Completed]`
- [ ] Sync Meta Templates logic (Fetches, updates, and deletes local templates to match WABA records on Meta) `[Pending]`
- [ ] Templates Directory UI (Search, filter, view approval status) `[Pending]`
- [ ] Template Previewer Modal (Builds preview layout based on Meta JSON components structure) `[Pending]`

---

## 📣 Campaign & Bulk Messaging Module

Enables broadcasting messages to target audiences.

- [x] Database Schema for `campaigns`:
  - `id`, `user_id`, `name`, `template_id`, `status` (draft, scheduled, processing, paused, completed, cancelled), `scheduled_at`, `total_contacts`, `sent_count`, `delivered_count`, `read_count`, `failed_count` `[Completed]`
- [ ] Campaign Builder UI:
  - [ ] Select/Upload Contacts or Select Contact Group `[Pending]`
  - [ ] Select Template & Bind dynamic variables to custom contact fields `[Pending]`
  - [ ] Select Outgoing WhatsApp Business Account (WABA) `[Pending]`
  - [ ] Schedule Campaign execution date/time `[Pending]`
- [ ] Campaign Control Action Handlers:
  - [ ] Start / Resume Campaign `[Pending]`
  - [ ] Pause Campaign `[Pending]`
  - [ ] Cancel/Stop Campaign `[Pending]`
- [ ] Isolated Background Queue Runner (Laravel job processing queue for sending messages to 100-200 contacts with rate throttling to prevent Meta rate-limit blocking) `[Pending]`
- [ ] Campaign Analytics Dashboard (Pie charts and metrics tables showing real-time delivery performance) `[Pending]`

---

## 📁 Media Library

Central repository for files sent in conversations or campaigns.

- [x] Database Schema for `media_library` (id, user_id, filename, file_path, file_type, file_size) `[Completed]`
- [ ] Upload Handler (Validating, resizing, and storing files in `public` or `storage` directory) `[Pending]`
- [ ] Media browser/picker modal (Select file to send directly in chat or campaigns) `[Pending]`
- [ ] Delete feature (Removes file record and disk asset) `[Pending]`

---

## 🎨 Theme & UI Styling System

Uses CSS variables to implement custom themes and dark modes without hardcoded color classes.

- [x] CSS variables definition in `variables.css`:
  - `--primary-color`, `--secondary-color`, `--success-color`, `--danger-color`
  - `--background-color`, `--card-background`, `--text-primary`, `--text-secondary`
- [x] UI components using variables for borders, text, backgrounds, and shadows `[Completed]`
- [x] Dynamic Theme Switcher (Client-side trigger, saves selection in local storage or user preferences database table) `[Completed]`
- [x] Dark Mode Theme stylesheet rules `[Completed]`

---

## 🔒 Security & Data Integrity

- [ ] CSRF protection enforced globally `[Pending]`
- [ ] Data Encryption for Meta credentials (`meta_access_token` encrypted at rest in the database) `[Pending]`
- [ ] Webhook validation using SHA256 app signatures `[Pending]`
- [ ] Secure file uploads (mime-type verification, storage mapping) `[Pending]`
- [ ] Form Input Validation on all CRUD actions `[Pending]`
- [ ] Rate limiting on APIs and Webhooks to avoid DoS issues `[Pending]`

---

## 📐 Coding Standards & Architectural Patterns

### Routing Conventions
- [x] Implement Route declaration using string-based namespace grouping (e.g., `Route::namespace('App\Http\Controllers')->group(...)`) `[Completed]`
- **Pattern Example:**
```php
use Illuminate\Support\Facades\Route;

//Normal Routes, Tracking Routes
Route::namespace('App\\Http\\Controllers')->group(function () {
    Route::get('/', 'AuthController@home')->name('home');
    Route::get('/tracking', 'TrackingController@index')->name('tracking'); //TODO: TEMP URL FOR TRACKING
    Route::post('/login-api', 'AuthController@login_api')->name('login-api');
    Route::post('/register-api', 'AuthController@register_api')->name('register-api');
    Route::get('/forgot-password', 'AuthController@show_forgot_password')->name('forgot-password');
    Route::post('/forgot-password', 'AuthController@forgot_password')->name('forgot-password.submit');
    Route::get('/reset-password/{token}', 'AuthController@show_reset_password')->name('password.reset');
    Route::post('/reset-password', 'AuthController@reset_password')->name('password.update');
});
```

### Controller Coding Pattern
- [x] Controllers must declare strict return types (e.g., `: View`, `: JsonResponse`) `[Completed]`
- [x] Use `Validator::make` manually within the controller method rather than FormRequest classes `[Completed]`
- [x] Always return JSON response on validation failure: `return response()->json(['status' => false, 'message' => $validation->errors()->first()], 422)` `[Completed]`
- [x] Use `try-catch` blocks for mailing and external API calls (e.g., Meta Cloud API) `[Completed]`
- **Pattern Example:**
```php
namespace App\Http\Controllers;

use App\Mail\PublisherRegistrationMail;
use App\Models\User;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function home(): View
    {
        return view('welcome');
    }

    public function login_api(Request $request): JsonResponse
    {
        $validation = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'user_type' => 'required|in:admin,advertiser,publisher'
        ]);

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validation->errors()->first()
            ], 422);
        }

        $validated = $validation->validated();

        if (Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password']
        ])) {
            $user = Auth::user();

            $expectedUserType = match ($validated['user_type']) {
                'admin' => config('const.user_types.admin.key'),
                'advertiser' => config('const.user_types.advertiser.key'),
                'publisher' => config('const.user_types.publisher.key'),
            };

            if ($user->status == 1 && $user->user_type == $expectedUserType) {
                $request->session()->regenerate();

                $redirectUrl = match ($validated['user_type']) {
                    'admin' => route('admin.dashboard'),
                    'advertiser' => route('advertiser.dashboard'),
                    'publisher' => route('publisher.dashboard'),
                };

                return response()->json([
                    'status' => true,
                    'message' => 'Login Successful!',
                    'redirect_url' => $redirectUrl
                ]);
            } else {
                Auth::logout();
                return response()->json([
                    'status' => false,
                    'message' => 'Your account is inactive or unauthorized!'
                ], 403);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Invalid Username or Password!'
        ], 401);
    }
}
```

---

## 📈 Status Legend
- `[Pending]` : Work not yet started.
- `[In Progress]` : Work is active.
- `[Completed]` : Work completed, tested, and verified.

