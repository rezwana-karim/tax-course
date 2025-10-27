# GitHub Copilot Instructions for Tax Course Creation Platform

## Project Overview

This is a comprehensive course creation web application built with Laravel 12 and modern frontend technologies (HTML5, CSS3, JavaScript, jQuery). The platform enables users to create structured courses with multiple modules and nested content.

## Technology Stack

- **Backend**: Laravel 12.35.1, PHP 8.3.6
- **Frontend**: HTML5, CSS3, Vanilla JavaScript, jQuery 3.7.1
- **Database**: SQLite (configurable for MySQL/PostgreSQL)
- **Testing**: PHPUnit 11.5.42
- **Build Tools**: Vite 7.0.7, Tailwind CSS 4.0.0
- **Code Quality**: Laravel Pint (PSR-12)

## Architecture Patterns

### Database Design
- **Courses** → hasMany → **Modules** → hasMany → **Contents**
- **Contents** support self-referencing parent-child relationships for nested content
- All foreign keys have cascading deletes for data integrity
- Ordering is maintained via `order` column

### Design Patterns
- **Repository Pattern**: Not implemented - use Eloquent directly
- **Service Layer**: Business logic in controllers (simple CRUD operations)
- **MVC Pattern**: Standard Laravel MVC architecture
- **RESTful API**: Resource controllers with standard REST conventions

## Code Style Guidelines

### PHP/Laravel
- Follow PSR-12 coding standards
- Use type hints for method parameters and return types
- Use Laravel's Eloquent ORM for database operations
- Implement validation using Laravel's Validator or Form Requests
- Use database transactions for complex operations
- Implement eager loading to prevent N+1 queries
- Use resource controllers for REST APIs

### Frontend (JavaScript/jQuery)
- Use jQuery 3.7.1 for DOM manipulation and AJAX
- Implement vanilla JavaScript for modern features (ES6+)
- Use semantic HTML5 markup
- Ensure CSRF token is included in all AJAX requests
- Validate forms on both frontend and backend
- Provide user feedback with success/error messages

### Blade Templates
- Use Blade templating engine features (@extends, @section, @yield)
- Keep templates clean and avoid business logic
- Use partials for reusable components
- Include CSRF tokens in forms: `@csrf`
- Use Laravel helpers for URLs: `route()`, `url()`, `asset()`

## Naming Conventions

### Models
- Singular, PascalCase: `Course`, `Module`, `Content`
- Model properties use snake_case: `course_id`, `parent_id`

### Controllers
- Suffix with 'Controller': `CourseController`
- Use resource methods: `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`

### Routes
- RESTful route naming
- API routes: `/api/courses`, `/api/courses/{id}`
- Web routes: `/courses/create`, `/courses/{id}/edit`

### Database
- Table names: plural, snake_case: `courses`, `modules`, `contents`
- Column names: snake_case: `course_id`, `created_at`
- Foreign keys: `{table_singular}_id`: `course_id`, `module_id`

### Tests
- Test classes: Suffix with 'Test': `CourseTest`
- Test methods: `test_` prefix: `test_course_can_be_created()`
- Use descriptive test names that explain what is being tested

## Validation Rules

### Course Creation
- `title`: required, string, max 255 characters
- `description`: optional, string
- `modules`: required, array, minimum 1 module
- `modules.*.title`: required, string, max 255
- `modules.*.description`: optional, string
- `modules.*.contents`: required, array, minimum 1 content item
- `modules.*.contents.*.title`: required, string, max 255
- `modules.*.contents.*.type`: required, enum (text, video, document, quiz)
- `modules.*.contents.*.body`: optional, string

## Testing Guidelines

### Test Structure
- Use `RefreshDatabase` trait for feature tests
- Test happy path and error cases
- Test validation rules thoroughly
- Use factories for test data generation
- Assert HTTP status codes and JSON responses
- Test database state changes

### Running Tests
```bash
php artisan test              # Run all tests
php artisan test --filter CourseTest  # Run specific test
./vendor/bin/pint            # Format code (PSR-12)
```

## Security Best Practices

- Always include CSRF tokens in forms and AJAX requests
- Use Eloquent ORM to prevent SQL injection
- Validate and sanitize all user input
- Use Laravel's built-in authentication when needed
- Implement proper authorization checks
- Use mass assignment protection (`$fillable` or `$guarded`)
- Sanitize output in Blade templates (automatic with `{{ }}`)

## Performance Optimization

- Use eager loading: `Course::with(['modules.allContents'])`
- Index foreign keys and frequently queried columns
- Use database transactions for multi-step operations
- Cache queries when appropriate
- Optimize frontend assets (minimize, compress)
- Use CDN for jQuery and other libraries

## API Response Format

### Success Response
```json
{
  "success": true,
  "message": "Course created successfully",
  "data": {
    "id": 1,
    "title": "Course Title",
    ...
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."]
  }
}
```

## Common Tasks

### Adding a New Feature
1. Create/update migration files
2. Create/update model with relationships and fillable properties
3. Create/update controller with validation
4. Add routes (web.php or api.php)
5. Create Blade views (if needed)
6. Write feature tests
7. Run tests and format code
8. Update documentation

### Creating a New API Endpoint
1. Define route in `routes/api.php`
2. Create controller method with validation
3. Return JSON response with appropriate status code
4. Write feature test for the endpoint
5. Test with curl or Postman

### Adding Frontend Functionality
1. Update Blade template with HTML structure
2. Add jQuery event handlers in `@section('scripts')`
3. Implement AJAX calls with CSRF token
4. Handle success/error responses
5. Update UI with feedback messages

## Dependencies

### PHP Packages (Composer)
- `laravel/framework`: ^12.0
- `laravel/tinker`: ^2.10.1
- `phpunit/phpunit`: ^11.5.3 (dev)
- `laravel/pint`: ^1.24 (dev)

### JavaScript Packages (NPM)
- `jquery`: ^3.7.1
- `vite`: ^7.0.7
- `tailwindcss`: ^4.0.0
- `axios`: ^1.11.0

## Helpful Commands

```bash
# Development
php artisan serve              # Start development server
php artisan migrate            # Run migrations
php artisan migrate:fresh      # Fresh migration (drop all tables)
php artisan tinker             # Laravel REPL

# Testing
php artisan test               # Run tests
php artisan test --coverage    # Run with coverage

# Code Quality
./vendor/bin/pint              # Format code
./vendor/bin/pint --test       # Check formatting

# Build
npm install                    # Install dependencies
npm run dev                    # Development build
npm run build                  # Production build
```

## Known Limitations

- No user authentication system (can be added if needed)
- No file upload functionality (placeholder for future)
- No real-time features (WebSockets)
- SQLite database (can be changed to MySQL/PostgreSQL)

## Future Enhancement Opportunities

When suggesting enhancements, consider:
- Course preview/view functionality
- File upload for content (videos, documents)
- Rich text editor for content body
- Course categories and tagging
- Search and filtering
- User authentication and authorization
- Role-based access control
- API rate limiting
- Caching layer
- Queue system for heavy operations
- Email notifications
- Analytics and reporting

## Important Notes

- Always run migrations in development before testing
- Keep `.env` file secure and never commit it
- Use `.env.example` as template for environment variables
- Follow Laravel's directory structure conventions
- Document API changes in README.md
- Write tests for new features
- Run code formatter before committing
- Use semantic versioning for releases
