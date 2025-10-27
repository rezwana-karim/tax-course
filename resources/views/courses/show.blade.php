@extends('layouts.app')

@section('title', $course->title)

@section('content')
<div class="course-detail">
    <div class="course-header">
        <div class="course-header-content">
            <h1>{{ $course->title }}</h1>
            
            @if($course->category)
            <div class="course-category">
                <span class="category-badge">{{ $course->category }}</span>
            </div>
            @endif
            
            @if($course->thumbnail)
            <div class="course-thumbnail">
                <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="{{ $course->title }}" />
            </div>
            @endif

            @if($course->description)
            <div class="course-description">
                <h2>About This Course</h2>
                <p>{{ $course->description }}</p>
            </div>
            @endif

            @if($course->feature_video)
            <div class="feature-video">
                <h2>Feature Video</h2>
                <video controls width="100%">
                    <source src="{{ asset('storage/' . $course->feature_video) }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>
            @endif
        </div>

        <div class="course-actions">
            <a href="/courses" class="btn btn-secondary">← All Courses</a>
            <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-primary">Edit Course</a>
        </div>
    </div>

    <div class="course-content">
        <h2>Course Modules</h2>
        
        @if($course->modules->count() > 0)
        <div class="modules-list">
            @foreach($course->modules as $moduleIndex => $module)
            <div class="module-card" id="module-{{ $module->id }}">
                <div class="module-header">
                    <h3>
                        <span class="module-number">Module {{ $moduleIndex + 1 }}:</span>
                        {{ $module->title }}
                    </h3>
                    <button type="button" class="btn-toggle" data-target="module-content-{{ $module->id }}" aria-expanded="true">
                        <span class="toggle-icon">▼</span>
                    </button>
                </div>

                @if($module->description)
                <div class="module-description">
                    <p>{{ $module->description }}</p>
                </div>
                @endif

                <div class="module-content" id="module-content-{{ $module->id }}">
                    @if($module->contents->count() > 0)
                    <div class="contents-list">
                        @foreach($module->contents as $content)
                            @include('courses.partials.content-item', ['content' => $content, 'level' => 0])
                        @endforeach
                    </div>
                    @else
                    <p class="no-content">No content available in this module.</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="no-modules">No modules available in this course.</p>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Toggle module content
    $('.btn-toggle').click(function() {
        const targetId = $(this).data('target');
        const target = $('#' + targetId);
        const icon = $(this).find('.toggle-icon');
        
        target.slideToggle(300);
        
        if ($(this).attr('aria-expanded') === 'true') {
            $(this).attr('aria-expanded', 'false');
            icon.text('▶');
        } else {
            $(this).attr('aria-expanded', 'true');
            icon.text('▼');
        }
    });

    // Toggle nested content
    $('.content-toggle').click(function() {
        const targetId = $(this).data('target');
        const target = $('#' + targetId);
        const icon = $(this).find('.toggle-icon');
        
        target.slideToggle(300);
        
        if ($(this).attr('aria-expanded') === 'true') {
            $(this).attr('aria-expanded', 'false');
            icon.text('▶');
        } else {
            $(this).attr('aria-expanded', 'true');
            icon.text('▼');
        }
    });
});
</script>
@endsection
