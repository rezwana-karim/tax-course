# Test Instructions for PHP/Laravel

applyTo:
  - "tests/**/*.php"

## Purpose

These instructions guide GitHub Copilot when writing tests for the Tax Course Creation Platform, a Laravel application.

## Testing Framework

- **PHPUnit**: 11.5.42
- **Laravel Testing Utilities**: Built-in Laravel testing helpers
- **Test Database**: SQLite in-memory or file-based

## Test Structure

### Directory Organization

```
tests/
├── Feature/           # Feature/integration tests (HTTP requests, database)
│   ├── CourseTest.php
│   ├── ModuleTest.php
│   └── ContentTest.php
├── Unit/              # Unit tests (isolated component testing)
│   └── ExampleTest.php
└── TestCase.php       # Base test case with common functionality
```

### Test Naming Conventions

```php
// Good: Descriptive test method names
public function test_course_can_be_created(): void
public function test_course_creation_requires_title(): void
public function test_nested_content_can_be_created(): void
public function test_user_can_delete_their_own_course(): void

// Also acceptable: Snake case with test_ prefix
public function test_course_creation_page_loads(): void

// Bad: Non-descriptive names
public function testCourse(): void
public function test1(): void
```

## Feature Tests

### Basic Structure

```php
<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Module;
use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that course creation page loads successfully.
     */
    public function test_course_creation_page_loads(): void
    {
        $response = $this->get('/courses/create');
        
        $response->assertStatus(200);
        $response->assertSee('Create New Course');
    }
}
```

### HTTP Request Testing

```php
/**
 * Test course can be created via API.
 */
public function test_course_can_be_created(): void
{
    $courseData = [
        'title' => 'Tax Fundamentals',
        'description' => 'Introduction to taxation',
        'modules' => [
            [
                'title' => 'Module 1',
                'description' => 'Getting started',
                'contents' => [
                    [
                        'title' => 'What is Tax?',
                        'type' => 'text',
                        'body' => 'Tax is a mandatory contribution...',
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/api/courses', $courseData);

    $response->assertStatus(201);
    $response->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'id',
            'title',
            'description',
            'created_at',
            'updated_at',
        ],
    ]);

    $this->assertDatabaseHas('courses', [
        'title' => 'Tax Fundamentals',
    ]);
}
```

### Validation Testing

```php
/**
 * Test course creation requires title.
 */
public function test_course_creation_requires_title(): void
{
    $courseData = [
        'description' => 'Test description',
        'modules' => [
            [
                'title' => 'Module 1',
                'contents' => [
                    ['title' => 'Content 1', 'type' => 'text'],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/api/courses', $courseData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['title']);
}

/**
 * Test course creation requires at least one module.
 */
public function test_course_creation_requires_modules(): void
{
    $courseData = [
        'title' => 'Test Course',
        'description' => 'Test description',
        'modules' => [],
    ];

    $response = $this->postJson('/api/courses', $courseData);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['modules']);
}
```

### Database Testing

```php
/**
 * Test nested content can be created.
 */
public function test_nested_content_can_be_created(): void
{
    $courseData = [
        'title' => 'Test Course',
        'description' => 'Test description',
        'modules' => [
            [
                'title' => 'Module 1',
                'description' => 'Module description',
                'contents' => [
                    [
                        'title' => 'Parent Content',
                        'type' => 'text',
                        'body' => 'Parent body',
                        'children' => [
                            [
                                'title' => 'Child Content',
                                'type' => 'text',
                                'body' => 'Child body',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/api/courses', $courseData);

    $response->assertStatus(201);

    $this->assertDatabaseHas('contents', [
        'title' => 'Parent Content',
        'parent_id' => null,
    ]);

    $this->assertDatabaseHas('contents', [
        'title' => 'Child Content',
    ]);

    $parentContent = Content::where('title', 'Parent Content')->first();
    $childContent = Content::where('title', 'Child Content')->first();

    $this->assertEquals($parentContent->id, $childContent->parent_id);
}
```

### CRUD Operations Testing

```php
/**
 * Test courses can be listed.
 */
public function test_courses_can_be_listed(): void
{
    Course::factory()
        ->count(3)
        ->create();

    $response = $this->getJson('/api/courses');

    $response->assertStatus(200);
    $response->assertJsonCount(3);
}

/**
 * Test course can be retrieved.
 */
public function test_course_can_be_retrieved(): void
{
    $course = Course::factory()
        ->has(Module::factory()->count(2))
        ->create();

    $response = $this->getJson("/api/courses/{$course->id}");

    $response->assertStatus(200);
    $response->assertJson([
        'id' => $course->id,
        'title' => $course->title,
    ]);
}

/**
 * Test course can be updated.
 */
public function test_course_can_be_updated(): void
{
    $course = Course::factory()->create([
        'title' => 'Original Title',
    ]);

    $updateData = [
        'title' => 'Updated Title',
        'description' => 'Updated description',
    ];

    $response = $this->putJson("/api/courses/{$course->id}", $updateData);

    $response->assertStatus(200);

    $this->assertDatabaseHas('courses', [
        'id' => $course->id,
        'title' => 'Updated Title',
    ]);
}

/**
 * Test course can be deleted.
 */
public function test_course_can_be_deleted(): void
{
    $course = Course::factory()->create();

    $response = $this->deleteJson("/api/courses/{$course->id}");

    $response->assertStatus(200);

    $this->assertDatabaseMissing('courses', [
        'id' => $course->id,
    ]);
}
```

### Relationship Testing

```php
/**
 * Test course has modules relationship.
 */
public function test_course_has_modules(): void
{
    $course = Course::factory()
        ->has(Module::factory()->count(3))
        ->create();

    $this->assertCount(3, $course->modules);
    $this->assertInstanceOf(Module::class, $course->modules->first());
}

/**
 * Test cascading delete works.
 */
public function test_deleting_course_deletes_associated_modules(): void
{
    $course = Course::factory()
        ->has(Module::factory()->count(2))
        ->create();

    $moduleIds = $course->modules->pluck('id')->toArray();

    $course->delete();

    foreach ($moduleIds as $moduleId) {
        $this->assertDatabaseMissing('modules', ['id' => $moduleId]);
    }
}
```

## Unit Tests

### Model Testing

```php
<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Module;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test course has fillable attributes.
     */
    public function test_course_has_fillable_attributes(): void
    {
        $course = new Course([
            'title' => 'Test Course',
            'description' => 'Test description',
        ]);

        $this->assertEquals('Test Course', $course->title);
        $this->assertEquals('Test description', $course->description);
    }

    /**
     * Test course modules relationship returns collection.
     */
    public function test_modules_relationship_returns_collection(): void
    {
        $course = Course::factory()->create();

        $this->assertInstanceOf(
            \Illuminate\Database\Eloquent\Collection::class,
            $course->modules
        );
    }
}
```

### Helper Function Testing

```php
/**
 * Test helper function formats course title correctly.
 */
public function test_course_title_is_formatted_correctly(): void
{
    $title = format_course_title('  test course  ');

    $this->assertEquals('Test Course', $title);
}
```

## Testing Best Practices

### Use Factories for Test Data

```php
// Good: Use factories
$course = Course::factory()->create();
$course = Course::factory()->create(['title' => 'Custom Title']);

// Good: Create with relationships
$course = Course::factory()
    ->has(Module::factory()->count(3))
    ->create();

// Avoid: Manual creation in every test
$course = Course::create([
    'title' => 'Test',
    'description' => 'Test',
]);
```

### Use Database Transactions

```php
use Illuminate\Foundation\Testing\RefreshDatabase;

class CourseTest extends TestCase
{
    use RefreshDatabase;  // Refreshes database for each test

    // Tests here
}
```

### Test Edge Cases

```php
/**
 * Test creating course with maximum allowed modules.
 */
public function test_course_can_have_maximum_modules(): void
{
    $modules = collect(range(1, 100))->map(fn($i) => [
        'title' => "Module $i",
        'contents' => [
            ['title' => "Content $i", 'type' => 'text'],
        ],
    ])->toArray();

    $courseData = [
        'title' => 'Test Course',
        'modules' => $modules,
    ];

    $response = $this->postJson('/api/courses', $courseData);

    $response->assertStatus(201);
    $this->assertDatabaseCount('modules', 100);
}

/**
 * Test title with special characters.
 */
public function test_course_title_accepts_special_characters(): void
{
    $courseData = [
        'title' => 'Tax & Finance: 101 (Beginner\'s Guide)',
        'modules' => [
            [
                'title' => 'Module 1',
                'contents' => [
                    ['title' => 'Content', 'type' => 'text'],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/api/courses', $courseData);

    $response->assertStatus(201);
}
```

### Test Error Handling

```php
/**
 * Test database error is handled gracefully.
 */
public function test_handles_database_error_gracefully(): void
{
    // Mock database error
    $this->mock(Course::class)
        ->shouldReceive('create')
        ->andThrow(new \Exception('Database error'));

    $response = $this->postJson('/api/courses', [
        'title' => 'Test Course',
        'modules' => [
            [
                'title' => 'Module',
                'contents' => [
                    ['title' => 'Content', 'type' => 'text'],
                ],
            ],
        ],
    ]);

    $response->assertStatus(500);
}
```

## Assertions

### Common Assertions

```php
// HTTP Response Assertions
$response->assertStatus(200);
$response->assertOk();
$response->assertCreated();
$response->assertNoContent();
$response->assertNotFound();
$response->assertForbidden();
$response->assertUnauthorized();

// JSON Assertions
$response->assertJson(['key' => 'value']);
$response->assertJsonStructure(['key']);
$response->assertJsonCount(3);
$response->assertJsonFragment(['title' => 'Test']);
$response->assertJsonMissing(['deleted' => true]);

// Validation Assertions
$response->assertJsonValidationErrors(['title']);
$response->assertJsonMissingValidationErrors(['description']);

// Database Assertions
$this->assertDatabaseHas('courses', ['title' => 'Test']);
$this->assertDatabaseMissing('courses', ['id' => 999]);
$this->assertDatabaseCount('courses', 5);

// Model Assertions
$this->assertModelExists($course);
$this->assertModelMissing($course);

// General Assertions
$this->assertEquals($expected, $actual);
$this->assertNotEquals($expected, $actual);
$this->assertTrue($value);
$this->assertFalse($value);
$this->assertNull($value);
$this->assertNotNull($value);
$this->assertEmpty($array);
$this->assertNotEmpty($array);
$this->assertCount(3, $array);
$this->assertContains('value', $array);
$this->assertInstanceOf(Course::class, $object);
```

### View Assertions

```php
public function test_view_contains_course_data(): void
{
    $course = Course::factory()->create(['title' => 'Tax 101']);

    $response = $this->get('/courses/create');

    $response->assertSee('Create New Course');
    $response->assertDontSee('Tax 101');
}
```

## Test Organization

### Group Related Tests

```php
/**
 * @group course
 * @group api
 */
class CourseApiTest extends TestCase
{
    // Tests for course API
}

/**
 * @group course
 * @group validation
 */
class CourseValidationTest extends TestCase
{
    // Tests for course validation
}
```

### Data Providers

```php
/**
 * @dataProvider invalidCourseDataProvider
 */
public function test_course_validation_fails_with_invalid_data($data, $errorField): void
{
    $response = $this->postJson('/api/courses', $data);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors([$errorField]);
}

public static function invalidCourseDataProvider(): array
{
    return [
        'missing title' => [
            ['description' => 'Test', 'modules' => []],
            'title',
        ],
        'empty modules' => [
            ['title' => 'Test', 'modules' => []],
            'modules',
        ],
        'invalid content type' => [
            [
                'title' => 'Test',
                'modules' => [
                    [
                        'title' => 'Module',
                        'contents' => [
                            ['title' => 'Content', 'type' => 'invalid'],
                        ],
                    ],
                ],
            ],
            'modules.0.contents.0.type',
        ],
    ];
}
```

## Testing AJAX/JSON Requests

```php
/**
 * Test AJAX course creation.
 */
public function test_ajax_course_creation(): void
{
    $response = $this->postJson('/api/courses', [
        'title' => 'Test Course',
        'modules' => [
            [
                'title' => 'Module 1',
                'contents' => [
                    ['title' => 'Content', 'type' => 'text'],
                ],
            ],
        ],
    ]);

    $response->assertStatus(201);
    $response->assertHeader('Content-Type', 'application/json');
}
```

## Performance Testing

```php
/**
 * Test eager loading prevents N+1 queries.
 */
public function test_course_list_uses_eager_loading(): void
{
    Course::factory()
        ->has(Module::factory()->count(5))
        ->count(10)
        ->create();

    \DB::enableQueryLog();

    $this->getJson('/api/courses');

    $queries = \DB::getQueryLog();

    // Should be 1 query for courses + 1 for modules (eager loaded)
    $this->assertLessThanOrEqual(3, count($queries));
}
```

## Common Testing Patterns for This Project

### Testing Course Creation with Nested Content

```php
public function test_course_with_deeply_nested_content(): void
{
    $courseData = [
        'title' => 'Advanced Tax Course',
        'modules' => [
            [
                'title' => 'Module 1',
                'contents' => [
                    [
                        'title' => 'Level 1',
                        'type' => 'text',
                        'children' => [
                            [
                                'title' => 'Level 2',
                                'type' => 'text',
                                'children' => [
                                    [
                                        'title' => 'Level 3',
                                        'type' => 'text',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $response = $this->postJson('/api/courses', $courseData);

    $response->assertStatus(201);

    $this->assertDatabaseHas('contents', ['title' => 'Level 1']);
    $this->assertDatabaseHas('contents', ['title' => 'Level 2']);
    $this->assertDatabaseHas('contents', ['title' => 'Level 3']);
}
```

## Debugging Tests

```php
// Output response content
dump($response->getContent());
dd($response->json());

// Check database state
dump(\DB::table('courses')->get());

// View query log
\DB::enableQueryLog();
// ... execute code ...
dd(\DB::getQueryLog());
```

## Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/CourseTest.php

# Run specific test method
php artisan test --filter test_course_can_be_created

# Run with coverage
php artisan test --coverage

# Run tests in parallel
php artisan test --parallel

# Run tests with debugging
php artisan test --stop-on-failure
```

## Code Coverage

```bash
# Generate coverage report
php artisan test --coverage --min=80

# Coverage for specific directory
php artisan test --coverage --coverage-html=coverage
```

## Continuous Integration

```yaml
# .github/workflows/tests.yml
- name: Execute tests
  run: php artisan test
  
- name: Check coverage
  run: php artisan test --coverage --min=80
```

## Best Practices Summary

1. **Use descriptive test names** that explain what is being tested
2. **Test one thing per test** - keep tests focused and simple
3. **Use factories** for creating test data
4. **Always use RefreshDatabase** trait for feature tests
5. **Test both success and failure scenarios**
6. **Test edge cases** and boundary conditions
7. **Keep tests independent** - tests should not rely on each other
8. **Use appropriate assertions** - be specific about what you're testing
9. **Mock external dependencies** when necessary
10. **Write tests before or alongside code** (TDD approach)
11. **Keep tests maintainable** - refactor tests as you refactor code
12. **Aim for high coverage** but focus on meaningful tests

## Resources

- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Laravel Factories](https://laravel.com/docs/eloquent-factories)
- [HTTP Tests](https://laravel.com/docs/http-tests)
