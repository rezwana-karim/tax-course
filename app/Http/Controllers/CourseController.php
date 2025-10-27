<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
     * Show the courses index view
     */
    public function indexView()
    {
        return view('courses.index');
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
        // Parse modules JSON if it's a string (from FormData)
        $modulesData = $request->modules;
        if (is_string($modulesData)) {
            $modulesData = json_decode($modulesData, true);
            $request->merge(['modules' => $modulesData]);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'feature_video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:51200',
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
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Handle file uploads for course
            $thumbnailPath = null;
            $featureVideoPath = null;

            if ($request->hasFile('thumbnail')) {
                $thumbnailPath = $request->file('thumbnail')->store('courses/thumbnails', 'public');
            }

            if ($request->hasFile('feature_video')) {
                $featureVideoPath = $request->file('feature_video')->store('courses/videos', 'public');
            }

            $course = Course::create([
                'title' => $request->title,
                'description' => $request->description,
                'thumbnail' => $thumbnailPath,
                'feature_video' => $featureVideoPath,
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
                'course' => $course->load(['modules.allContents']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper function to create content recursively
     */
    private function createContentRecursive($moduleId, $contentData, $order, $parentId = null)
    {
        // Handle file upload for content if present
        $filePath = null;
        if (isset($contentData['file']) && $contentData['file'] instanceof \Illuminate\Http\UploadedFile) {
            $type = $contentData['type'] ?? 'text';
            $folder = match ($type) {
                'video' => 'contents/videos',
                'document' => 'contents/documents',
                default => 'contents/files',
            };
            $filePath = $contentData['file']->store($folder, 'public');
        }

        $content = Content::create([
            'module_id' => $moduleId,
            'parent_id' => $parentId,
            'title' => $contentData['title'],
            'body' => $contentData['body'] ?? null,
            'type' => $contentData['type'] ?? 'text',
            'order' => $order,
            'file_path' => $filePath,
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
     * Show the view for displaying a course
     */
    public function showView(string $id)
    {
        $course = Course::with(['modules.contents.children'])->findOrFail($id);

        return view('courses.show', compact('course'));
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
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'feature_video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:51200',
            'modules' => 'required|array|min:1',
            'modules.*.title' => 'required|string|max:255',
            'modules.*.description' => 'nullable|string',
            'modules.*.contents' => 'required|array|min:1',
            'modules.*.contents.*.title' => 'required|string|max:255',
            'modules.*.contents.*.body' => 'nullable|string',
            'modules.*.contents.*.type' => 'required|string|in:text,video,document,quiz',
            'modules.*.contents.*.file' => 'nullable|file|max:51200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $course = Course::findOrFail($id);
            
            // Handle file uploads
            $updateData = [
                'title' => $request->title,
                'description' => $request->description,
            ];

            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail if exists
                if ($course->thumbnail) {
                    \Storage::disk('public')->delete($course->thumbnail);
                }
                $updateData['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
            }

            if ($request->hasFile('feature_video')) {
                // Delete old video if exists
                if ($course->feature_video) {
                    \Storage::disk('public')->delete($course->feature_video);
                }
                $updateData['feature_video'] = $request->file('feature_video')->store('courses/videos', 'public');
            }

            $course->update($updateData);

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
                'course' => $course->load(['modules.allContents']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to update course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $course = Course::with('modules.allContents')->findOrFail($id);
            
            // Delete course files
            if ($course->thumbnail) {
                \Storage::disk('public')->delete($course->thumbnail);
            }
            if ($course->feature_video) {
                \Storage::disk('public')->delete($course->feature_video);
            }

            // Delete content files
            foreach ($course->modules as $module) {
                foreach ($module->allContents as $content) {
                    if ($content->file_path) {
                        \Storage::disk('public')->delete($content->file_path);
                    }
                }
            }

            $course->delete();

            return response()->json([
                'success' => true,
                'message' => 'Course deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete course',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
