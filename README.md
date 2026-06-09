# Recipe Box 🍳

A recipe-sharing web application built for the **Podstawy Technologii WWW** individual project.
Users can browse recipes, search and filter them, mark favorites, and comment with star
ratings. Administrators can add, edit and delete recipes (with photo upload).

Built with **PHP 8 + MySQL (MySQLi, prepared statements) + jQuery/AJAX**, mobile-first
responsive CSS.

## Features

- User **registration, login, logout** (passwords hashed with bcrypt).
- **Role system**: regular user vs. administrator (`is_admin`).
- Full **CRUD** for recipes (admin): create, read, update, delete.
- **Photo upload** and display for each recipe.
- **Search by phrase** (live AJAX search on title / category / ingredients).
- **Filtering by category** — both via links (chips) and via a drop-down list.
- **Quick navigation**: on a recipe page the category name links back to the filtered list.
- **Favorites** — add/remove via AJAX, dedicated "My favorites" page.
- **Comments & 1–5 star ratings**; users can delete their own comments via AJAX.
- CSRF protection on all state-changing forms, server-side + client-side validation.
- **Admin dashboard** (`admin.php`): site statistics, most-favorited recipes, category management and user management.
- **Comment moderation**: admins can delete any comment from a recipe page via AJAX.
- Shared layout (`header.php`) and a consistent graphic theme.

Full project documentation is in [`docs/dokumentacja.md`](docs/dokumentacja.md).

## Database (5 tables)

`users`, `categories`, `recipes`, `comments`, `favorites` — linked by foreign keys
(see `setup.sql` for the full schema and the relationship diagram in phpMyAdmin).

## Setup (XAMPP)

1. Copy this folder into `C:\xampp\htdocs\recipes`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Import the database: open `http://localhost/phpmyadmin` → SQL tab → paste the
   contents of `setup.sql` and run it (creates the `recipe_box` database with sample data).
4. Open `http://localhost/recipes/` in your browser.
5. Register an account (e.g. `admin`), then make it an administrator:
   ```sql
   UPDATE users SET is_admin = 1 WHERE login = 'admin';
   ```
6. Log back in — you can now add, edit and delete recipes.

## Project structure

| File / folder | Purpose |
|---|---|
| `db.php` | MySQLi connection + `h()` escape helper |
| `session.php` / `admin_guard.php` | auth + admin authorization guards |
| `csrf.php` | CSRF token helpers |
| `header.php` | shared navigation bar |
| `index.php` | recipe list + search + category filter |
| `details.php` | single recipe + comments + favorite button |
| `insert_recipe.php` / `insert.php` | add recipe form + handler (admin) |
| `edit.php` / `delete.php` | edit / delete recipe (admin) |
| `insert_comment.php` / `delete_comment_ajax.php` | comments |
| `toggle_fav.php` / `favorites.php` | favorites |
| `search_ajax.php` | live search endpoint |
| `partials/recipe_card.php` | reusable recipe card |
| `scripts/` | `app.js` (AJAX), `recipe_form_validator.js` |
| `styles/style.css` | mobile-first responsive theme |
