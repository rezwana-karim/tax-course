<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $courses = Course::with(['modules.allContents'])->get();
        return response()->json($courses);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('courses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'modules' => 'required|array|min:1',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
            'modules.*.contents' => 'required|array|min:1',
            'modules.*.contents.*.title' => 'required|string|max:255',
            'modules.*.contents.*.body' => 'nullable|string',
            'modules.*.contents.*.type' => 'required|string|in:text,video,document,quiz',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $course = Course::create([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            foreach ($request->modules as $moduleIndex => $moduleData) {
                $module = Module::create([
                    'course_id' => $course->id,
                    'title' => $moduleData['title'],
                    'description' => $moduleData['description'] ?? null,
                    'order' => $moduleIndex,
                ]);

                foreach ($moduleData['contents'] as $contentIndex => $contentData) {
                    $this->createContentRecursive($module->id, $contentData, $contentIndex);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Course created successfully',
                'course' => $course->load(['modules.allContents'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper function to create content recursively
     */
    private function createContentRecursive($moduleId, $contentData, $order, $parentId = null)
    {
        $content = Content::create([
            'module_id' => $moduleId,
            'parent_id' => $parentId,
            'title' => $contentData['title'],
            'body' => $contentData['body'] ?? null,
            'type' => $contentData['type'] ?? 'text',
            'order' => $order,
        ]);

        if (isset($contentData['children']) && is_array($contentData['children'])) {
            foreach ($contentData['children'] as $childIndex => $childData) {
                $this->createContentRecursive($moduleId, $childData, $childIndex, $content->id);
            }
        }

        return $content;
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $course = Course::with(['modules.allContents.children'])->findOrFail($id);
        return response()->json($course);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $course = Course::with(['modules.allContents'])->findOrFail($id);
        return view('courses.edit', compact('course'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'modules' => 'required|array|min:1',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
            'modules.*.contents' => 'required|array|min:1',
            'modules.*.contents.*.title' => 'required|string|max:255',
            'modules.*.contents.*.body' => 'nullable|string',
            'modules.*.contents.*.type' => 'required|string|in:text,video,document,quiz',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $course = Course::findOrFail($id);
            $course->update([
                'title' => $request->title,
                'description' => $request->description,
            ]);

            // Delete existing modules and contents (cascade will handle contents)
            $course->modules()->delete();

            foreach ($request->modules as $moduleIndex => $moduleData) {
                $module = Module::create([
                    'course_id' => $course->id,
                    'title' => $moduleData['title'],
                    'description' => $moduleData['description'] ?? null,
                    'order' => $moduleIndex,
                ]);

                foreach ($moduleData['contents'] as $contentIndex => $contentData) {
                    $this->createContentRecursive($module->id, $contentData, $contentIndex);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Course updated successfully',
                'course' => $course->load(['modules.allContents'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update course',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $course = Course::findOrFail($id);
            $course->delete();

            return response()->json([
                'success' => true,
                'message' => 'Course deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete course',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
