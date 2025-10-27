<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test course creation page loads successfully for authenticated instructor.
     */
    public function test_course_creation_page_loads(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        
        $response = $this->actingAs($user)->get('/courses/create');
        $response->assertStatus(200);
        $response->assertSee('Create New Course');
    }

    /**
     * Test course can be created via API by authenticated instructor.
     */
    public function test_course_can_be_created(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        
        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test Description',
            'modules' => [
                [
                    'title' => 'Test Module',
                    'description' => 'Module Description',
                    'contents' => [
                        [
                            'title' => 'Test Content',
                            'type' => 'text',
                            'body' => 'Content Body',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson('/api/courses', $courseData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Course created successfully',
        ]);

        $this->assertDatabaseHas('courses', [
            'title' => 'Test Course',
            'description' => 'Test Description',
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('modules', [
            'title' => 'Test Module',
        ]);

        $this->assertDatabaseHas('contents', [
            'title' => 'Test Content',
            'type' => 'text',
        ]);
    }

    /**
     * Test course creation requires title.
     */
    public function test_course_creation_requires_title(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        
        $courseData = [
            'description' => 'Test Description',
            'modules' => [
                [
                    'title' => 'Test Module',
                    'contents' => [
                        [
                            'title' => 'Test Content',
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson('/api/courses', $courseData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    /**
     * Test course creation requires at least one module.
     */
    public function test_course_creation_requires_modules(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        
        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test Description',
            'modules' => [],
        ];

        $response = $this->actingAs($user)->postJson('/api/courses', $courseData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('modules');
    }

    /**
     * Test nested content can be created.
     */
    public function test_nested_content_can_be_created(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        
        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test Description',
            'modules' => [
                [
                    'title' => 'Test Module',
                    'description' => 'Module Description',
                    'contents' => [
                        [
                            'title' => 'Parent Content',
                            'type' => 'text',
                            'body' => 'Parent Body',
                            'children' => [
                                [
                                    'title' => 'Child Content',
                                    'type' => 'video',
                                    'body' => 'Child Body',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson('/api/courses', $courseData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('contents', [
            'title' => 'Parent Content',
            'parent_id' => null,
        ]);

        $parent = Content::where('title', 'Parent Content')->first();

        $this->assertDatabaseHas('contents', [
            'title' => 'Child Content',
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Test courses can be listed.
     */
    public function test_courses_can_be_listed(): void
    {
        $course = Course::factory()->create(['title' => 'Test Course']);

        $response = $this->getJson('/api/courses');

        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Test Course']);
    }

    /**
     * Test courses index view loads successfully.
     */
    public function test_courses_index_view_loads(): void
    {
        $response = $this->get('/courses');
        $response->assertStatus(200);
        $response->assertSee('All Courses');
    }

    /**
     * Test course show view loads successfully.
     */
    public function test_course_show_view_loads(): void
    {
        $course = Course::factory()
            ->has(Module::factory()->count(1))
            ->create();

        $response = $this->get("/courses/{$course->id}");
        $response->assertStatus(200);
        $response->assertSee($course->title);
    }

    /**
     * Test course can be deleted.
     */
    public function test_course_can_be_deleted(): void
    {
        $user = User::factory()->create(['role' => 'instructor']);
        $course = Course::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->deleteJson("/api/courses/{$course->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Course deleted successfully',
        ]);

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}
