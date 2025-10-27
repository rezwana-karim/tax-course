<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test course creation page loads successfully.
     */
    public function test_course_creation_page_loads(): void
    {
        $response = $this->get('/courses/create');
        $response->assertStatus(200);
        $response->assertSee('Create New Course');
    }

    /**
     * Test course can be created via API.
     */
    public function test_course_can_be_created(): void
    {
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

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Course created successfully',
        ]);

        $this->assertDatabaseHas('courses', [
            'title' => 'Test Course',
            'description' => 'Test Description',
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

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('title');
    }

    /**
     * Test course creation requires at least one module.
     */
    public function test_course_creation_requires_modules(): void
    {
        $courseData = [
            'title' => 'Test Course',
            'description' => 'Test Description',
            'modules' => [],
        ];

        $response = $this->postJson('/api/courses', $courseData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('modules');
    }

    /**
     * Test nested content can be created.
     */
    public function test_nested_content_can_be_created(): void
    {
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

        $response = $this->postJson('/api/courses', $courseData);

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
     * Test course can be deleted.
     */
    public function test_course_can_be_deleted(): void
    {
        $course = Course::factory()->create();

        $response = $this->deleteJson("/api/courses/{$course->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Course deleted successfully',
        ]);

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}
