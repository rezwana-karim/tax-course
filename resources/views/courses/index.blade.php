@extends('layouts.app')

@section('title', 'All Courses')

@section('content')
<div class="courses-list-page">
    <div class="page-header">
        <h1>All Courses</h1>
        @auth
            @can('create', App\Models\Course::class)
                <a href="{{ route('courses.create') }}" class="btn btn-primary">+ Create New Course</a>
            @endcan
        @endauth
    </div>

    <div id="message-container"></div>

    <div id="coursesContainer" class="courses-grid">
        <div class="loading-spinner">Loading courses...</div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    loadCourses();

    function loadCourses() {
        $.ajax({
            url: '/api/courses',
            method: 'GET',
            success: function(response) {
                displayCourses(response);
            },
            error: function(xhr) {
                showMessage('Failed to load courses.', 'error');
                $('#coursesContainer').html('<p class="error-message">Failed to load courses. Please try again.</p>');
            }
        });
    }

    function displayCourses(courses) {
        const container = $('#coursesContainer');
        
        if (courses.length === 0) {
            container.html('<p class="no-courses">No courses available. <a href="{{ route("courses.create") }}">Create your first course!</a></p>');
            return;
        }

        let html = '';
        courses.forEach(function(course) {
            const thumbnailUrl = course.thumbnail 
                ? '/storage/' + course.thumbnail 
                : '/images/default-course-thumbnail.png';
            
            const moduleCount = course.modules ? course.modules.length : 0;
            const contentCount = course.modules 
                ? course.modules.reduce((sum, module) => sum + (module.all_contents ? module.all_contents.length : 0), 0)
                : 0;

            html += `
                <div class="course-card">
                    ${course.thumbnail ? `
                    <div class="course-card-thumbnail">
                        <img src="${thumbnailUrl}" alt="${course.title}" onerror="this.src='/images/default-course-thumbnail.png'">
                    </div>
                    ` : ''}
                    <div class="course-card-content">
                        <h3>${course.title}</h3>
                        ${course.description ? `<p class="course-description">${truncate(course.description, 150)}</p>` : ''}
                        <div class="course-meta">
                            <span class="meta-item">📚 ${moduleCount} module${moduleCount !== 1 ? 's' : ''}</span>
                            <span class="meta-item">📝 ${contentCount} content item${contentCount !== 1 ? 's' : ''}</span>
                        </div>
                        <div class="course-actions">
                            <a href="/courses/${course.id}" class="btn btn-sm btn-primary">View Course</a>
                            <a href="/courses/${course.id}/edit" class="btn btn-sm btn-secondary">Edit</a>
                            <button class="btn btn-sm btn-danger delete-course" data-id="${course.id}">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        });

        container.html(html);
    }

    function truncate(str, length) {
        if (str.length <= length) return str;
        return str.substring(0, length) + '...';
    }

    // Delete course
    $(document).on('click', '.delete-course', function() {
        const courseId = $(this).data('id');
        
        if (!confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
            return;
        }

        $.ajax({
            url: `/api/courses/${courseId}`,
            method: 'DELETE',
            success: function(response) {
                showMessage('Course deleted successfully!', 'success');
                loadCourses();
            },
            error: function(xhr) {
                showMessage('Failed to delete course.', 'error');
            }
        });
    });

    function showMessage(message, type) {
        const messageClass = type === 'success' ? 'success-message' : 'error-message';
        const messageHtml = `<div class="${messageClass}">${message}</div>`;
        $('#message-container').html(messageHtml);
        $('html, body').animate({ scrollTop: 0 }, 'fast');
        
        if (type === 'success') {
            setTimeout(() => {
                $('#message-container').html('');
            }, 3000);
        }
    }
});
</script>
@endsection
