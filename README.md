# Estatly - Property and Estate Management Application

Estatly is a modern Property and Estate Management application built using Laravel for the backend and Vue.js for the frontend. It is designed to manage properties, tenants, and estate-related activities efficiently.

## Project Structure

The project is organized into two main directories: `backend` and `frontend`.

### Backend

The backend is built with Laravel and contains the following directories:

- **app**: Core application logic, including models, controllers, and middleware.
- **bootstrap**: Files for bootstrapping the Laravel application.
- **config**: Configuration files for various services and settings.
- **database**: Database migrations, factories, and seeders.
- **public**: Publicly accessible files, including the entry point for the application.
- **resources**: Views, language files, and other resources.
- **routes**: Route definitions for web and API routes.
- **storage**: Storage for logs, cache, and file uploads.
- **tests**: Test cases organized into feature and unit tests.
- **artisan**: Command-line interface for running various Laravel commands.
- **composer.json**: Composer configuration file for backend dependencies.
- **package.json**: npm configuration file for backend JavaScript dependencies.
- **phpunit.xml**: PHPUnit configuration file for running tests.
- **README.md**: Documentation specific to the backend.

### Frontend

The frontend is built with Vue.js and contains the following directories:

- **public**: Publicly accessible files for the Vue.js application.
- **src**: Source code for the Vue.js application.
  - **assets**: Static assets such as images and stylesheets.
  - **components**: Reusable Vue components.
  - **router**: Routing configuration for the Vue application.
  - **store**: Vuex store for state management.
  - **views**: Main views of the application.
- **package.json**: npm configuration file for frontend dependencies.
- **vite.config.js**: Vite configuration file for building and serving the Vue.js application.
- **README.md**: Documentation specific to the frontend.

## Getting Started

### Local Development

To get started with the application locally, follow these steps:

1. Clone the repository.
2. Navigate to the `backend` directory and run `composer install` to install PHP dependencies.
3. Set up your `.env` file for database configuration.
4. Run `php artisan migrate` to set up the database.
5. Navigate to the `frontend` directory and run `npm install` to install JavaScript dependencies.
6. Run `npm run dev` to start the Vue.js development server.
7. Run `php artisan serve` in the backend directory to start the Laravel development server.

### Testing

Run the test suite to ensure everything is working:

```bash
# Frontend tests
cd frontend
npm run test:unit

# Backend tests  
cd backend
php artisan test
```

## Production Deployment

### Quick Deployment to cPanel Hosting

1. **Build for production:**
   ```bash
   ./build-production.sh
   ```

2. **Upload files:**
   - Upload `deployment/api/` contents to `public_html/api/`
   - Upload `deployment/public_html/` contents to `public_html/`

3. **Configure database in cPanel:**
   - Create MySQL database and user
   - Update `/api/.env` with database credentials

4. **Run migrations:**
   - Upload and visit `migrate-production.php` once
   - Delete the migration file afterward

5. **Set permissions:**
   - Set `storage/` and `bootstrap/cache/` folders to 755

### Detailed Deployment Guide

See [deploy.md](deploy.md) for complete step-by-step deployment instructions for cPanel shared hosting.

## Tech Stack

- **Backend:** Laravel 11, PHP 8.1+, MariaDB/MySQL
- **Frontend:** Vue.js 3, Vite, TypeScript, Vitest
- **Testing:** PHPUnit (backend), Vitest + Vue Test Utils (frontend)
- **Authentication:** Laravel Sanctum
- **Styling:** Custom CSS with modern gradients and animations

## Contributing

Contributions are welcome! Please open an issue or submit a pull request for any improvements or bug fixes.

## License

This project is licensed under the MIT License.