Thought for a couple of seconds


## Blog API (Laravel 12)

A RESTful API for managing blog posts, built with Laravel 12. Supports full CRUD with Form Requests and advanced validation, plus soft-deletes, restoring, and permanent deletes.

---

### 📋 Features

* **Posts CRUD** (Create, Read, Update, Delete)
* **Soft Deletes** (with restore)
* **Force Delete** (permanent removal)
* **API Versioning** (`/api/v1/...`)
* **Form Requests** with:

  * `StorePostRequest` & `UpdatePostRequest`
  * Automatic slug generation
  * Advanced tags & keywords parsing
  * Custom validation rules (`SlugFormat`, `FutureDate`)
  * Custom JSON error responses
* **Service Layer** (Business logic extracted to `PostService`)
* **Consistent JSON responses** via `BaseApiController` helpers

---

### 🚀 Requirements

* PHP ≥ 8.1
* Composer
* MySQL (or other supported DB)
* Laravel 12

---

### 🔧 Installation

1. **Clone repository**

   ```bash
   git clone https://github.com/Ali-S-Mohamad/Slugs_Management_System.git
   cd Slugs_Management_System
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Environment setup**

   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Configure your `.env` for DB, Redis, etc.

4. **Run migrations**

   ```bash
   php artisan migrate
   ```

5. **Serve locally**

   ```bash
   php artisan serve
   ```

   The API will be available at `http://127.0.0.1:8000/api/v1`.

---

### 🔗 API Endpoints

All endpoints are prefixed with `/api/v1`.

| Method | URI                   | Description                  |
| ------ | --------------------- | ---------------------------- |
| GET    | `/posts`              | List posts (with pagination) |
| GET    | `/posts/{id}`         | Retrieve single post         |
| POST   | `/posts`              | Create a new post            |
| PATCH  | `/posts/{id}`         | Update an existing post      |
| DELETE | `/posts/{id}`         | Soft-delete a post           |
| POST   | `/posts/{id}/restore` | Restore a soft-deleted post  |
| DELETE | `/posts/{id}/force`   | Permanently delete a post    |

#### Query Parameters for `GET /posts`

* `published_only` (boolean) — show only published posts
* `with_trashed` (boolean) — include soft-deleted posts

---

### 🔍 Validation Highlights

* **title**: required (create), sometimes (update), string, max:255
* **slug**: required/sometimes, unique, custom `SlugFormat` rule
* **body**: required/sometimes, string
* **is\_published**: boolean
* **publish\_date**: nullable, date, custom `FutureDate` rule
* **meta\_description**: nullable, string, max:160
* **tags**: nullable, array of strings (max 50 chars each)
* **keywords**: nullable, array of strings (max 10 items)

All validation logic lives in the Form Requests under `app/Http/Requests/`.

---

### 🛠️ Architecture

* **Controllers** (`app/Http/Controllers/Api/V1/PostController.php`)

  * Handle HTTP, call `PostService`, return JSON via `Controller::success()` / `error()`.
* **Service Layer** (`app/Services/PostService.php`)

  * Contains data-access and business logic: listing with filters, create, update, soft/delete, restore, forceDelete.
* **Form Requests** (`StorePostRequest`, `UpdatePostRequest`)

  * Prepare input (slug, tags, keywords), enforce rules, customize messages, return JSON errors.
* **Custom Rules** (`app/Rules/SlugFormat.php`, `FutureDate.php`)

  * Enforce lowercase-hyphen slugs and future publish dates.

---

### 🔗 Postman Collection

You can import the API endpoints into Postman using this collection:  
[Open Postman Collection](https://documenter.getpostman.com/view/24693079/2sB2j6AAfW)