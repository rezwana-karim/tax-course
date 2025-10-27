# Tax Course Creation Platform

A comprehensive course creation web application built with Laravel 12 (backend) and HTML, CSS, JavaScript, jQuery (frontend). This platform enables users to create structured courses with multiple modules and nested content.

## Features

- ✅ Create courses with multiple modules
- ✅ Add unlimited nested content to modules
- ✅ Dynamic form handling with add/remove actions
- ✅ Frontend and backend validation
- ✅ RESTful API for course management
- ✅ Responsive and modern UI
- ✅ Database storage with proper relationships
- ✅ Comprehensive test coverage
- ✅ Error handling and validation
- ✅ Performance optimized with eager loading

## Technology Stack

- **Backend**: Laravel 12.35.1 (PHP 8.3.6)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript, jQuery 3.7.1
- **Database**: SQLite (configurable for MySQL/PostgreSQL)
- **Testing**: PHPUnit with Laravel's testing framework

## Installation

1. Clone the repository:
```bash
git clone https://github.com/rezwana-karim/tax-course.git
cd tax-course
```

2. Install dependencies:
```bash
composer install
npm install
```

3. Configure environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Run migrations:
```bash
php artisan migrate
```

5. Start the development server:
```bash
php artisan serve
```

6. Access the application at `http://localhost:8000`

## Usage

### Creating a Course

1. Navigate to the course creation page
2. Enter course title and description
3. Add modules with titles and descriptions
4. Add content items to each module
5. Create nested content using the "+ Add Nested" button
6. Submit the form to create the course

### API Endpoints

- `GET /api/courses` - List all courses
- `POST /api/courses` - Create a new course
- `GET /api/courses/{id}` - Get a specific course
- `PUT /api/courses/{id}` - Update a course
- `DELETE /api/courses/{id}` - Delete a course

### Example API Request

Create a course:
```bash
curl -X POST http://localhost:8000/api/courses \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Tax Fundamentals",
    "description": "Introduction to taxation",
    "modules": [{
      "title": "Module 1",
      "description": "Getting started",
      "contents": [{
        "title": "What is Tax?",
        "type": "text",
        "body": "Tax is a mandatory financial charge...",
        "children": [{
          "title": "Types of Taxes",
          "type": "text",
          "body": "Income tax, sales tax..."
        }]
      }]
    }]
  }'
```

## Testing

Run the test suite:
```bash
php artisan test
```

Run code formatter:
```bash
./vendor/bin/pint
```

## Database Structure

### Courses Table
- `id` - Primary key
- `title` - Course title (required)
- `description` - Course description
- `timestamps` - Created/updated timestamps

### Modules Table
- `id` - Primary key
- `course_id` - Foreign key to courses
- `title` - Module title (required)
- `description` - Module description
- `order` - Display order
- `timestamps` - Created/updated timestamps

### Contents Table
- `id` - Primary key
- `module_id` - Foreign key to modules
- `parent_id` - Foreign key to contents (for nesting)
- `title` - Content title (required)
- `body` - Content body
- `type` - Content type (text, video, document, quiz)
- `order` - Display order
- `timestamps` - Created/updated timestamps

## Documentation

- **[TASK.md](TASK.md)** - Original project requirements
- **[IMPLEMENTATION_STATUS.md](IMPLEMENTATION_STATUS.md)** - Detailed implementation status and task list
- **[DEPLOYMENT_PLAN.md](DEPLOYMENT_PLAN.md)** - Comprehensive deployment guide for various platforms
- **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)** - Technical implementation details
- **[.github/copilot-instructions.md](.github/copilot-instructions.md)** - GitHub Copilot instructions for the project

## GitHub Copilot Instructions

This project includes comprehensive GitHub Copilot instructions to help with development:

- **General Instructions**: See `.github/copilot-instructions.md` for project-wide guidelines
- **GitHub Actions**: Path-specific instructions in `.github/instructions/actions.instructions.md`
- **Accessibility**: WCAG guidelines for Blade templates in `.github/instructions/accessibility.instructions.md`
- **Testing**: PHP/Laravel testing guidelines in `.github/instructions/tests.instructions.md`

## Deployment

This Laravel application **cannot be deployed on GitHub Pages** (static sites only). See [DEPLOYMENT_PLAN.md](DEPLOYMENT_PLAN.md) for:

- ⭐ **Recommended**: Railway.app (Free tier with $5/month credit)
- Alternative platforms: Render.com, Heroku, DigitalOcean
- Step-by-step deployment guides
- Configuration examples
- Cost comparisons

Quick start deployment on Railway.app:
```bash
# Files already included in repository:
# - Procfile
# - nixpacks.toml

# 1. Create account at railway.app
# 2. Connect GitHub repository
# 3. Add PostgreSQL database
# 4. Configure environment variables
# 5. Deploy automatically
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

When contributing, please refer to:
- `.github/copilot-instructions.md` for coding standards
- Path-specific instructions in `.github/instructions/`
- Run tests before submitting: `php artisan test`
- Format code with: `./vendor/bin/pint`

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

