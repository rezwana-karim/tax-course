@extends('layouts.app')

@section('title', 'Create Course')

@section('content')
<h1>Create New Course</h1>

<div id="message-container"></div>

<form id="courseForm" enctype="multipart/form-data">
    <div class="form-group">
        <label for="courseTitle">Course Title *</label>
        <input type="text" id="courseTitle" name="title" required>
    </div>

    <div class="form-group">
        <label for="courseDescription">Course Description</label>
        <textarea id="courseDescription" name="description" rows="3"></textarea>
    </div>

    <div class="form-group">
        <label for="courseCategory">Category</label>
        <input type="text" id="courseCategory" name="category" placeholder="e.g., Tax, Finance, Technology">
        <small class="form-text">Enter the course category (e.g., Tax, Finance, Technology)</small>
    </div>

    <div class="form-group">
        <label for="courseThumbnail">Course Thumbnail</label>
        <input type="file" id="courseThumbnail" name="thumbnail" accept="image/jpeg,image/png,image/jpg,image/gif">
        <small class="form-text">Max size: 2MB. Accepted formats: JPEG, PNG, JPG, GIF</small>
    </div>

    <div class="form-group">
        <label for="courseFeatureVideo">Feature Video</label>
        <input type="file" id="courseFeatureVideo" name="feature_video" accept="video/mp4,video/x-msvideo,video/quicktime,video/x-ms-wmv">
        <small class="form-text">Max size: 50MB. Accepted formats: MP4, MOV, AVI, WMV</small>
    </div>

    <h2 style="margin-top: 30px; margin-bottom: 20px; color: #667eea;">Modules</h2>
    <div id="modulesContainer"></div>
    
    <div class="add-btn-container">
        <button type="button" class="btn btn-primary" id="addModuleBtn">+ Add Module</button>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-success" id="submitBtn">
            <span class="btn-text">Create Course</span>
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let moduleCounter = 0;
    let contentCounters = {};

    // Setup CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Add module
    $('#addModuleBtn').click(function() {
        addModule();
    });

    function addModule() {
        moduleCounter++;
        contentCounters[moduleCounter] = 0;

        const moduleHtml = `
            <div class="module" data-module-id="${moduleCounter}">
                <div class="module-header">
                    <span class="module-title">Module ${moduleCounter}</span>
                    <button type="button" class="btn btn-danger btn-sm remove-module">Remove Module</button>
                </div>
                
                <div class="form-group">
                    <label>Module Title *</label>
                    <input type="text" name="modules[${moduleCounter}][title]" required>
                </div>
                
                <div class="form-group">
                    <label>Module Description</label>
                    <textarea name="modules[${moduleCounter}][description]" rows="2"></textarea>
                </div>
                
                <h3 style="margin-top: 20px; margin-bottom: 15px; font-size: 16px; color: #555;">Contents</h3>
                <div class="contents-container" data-module-id="${moduleCounter}"></div>
                
                <div class="add-btn-container">
                    <button type="button" class="btn btn-secondary btn-sm add-content-btn" data-module-id="${moduleCounter}">+ Add Content</button>
                </div>
            </div>
        `;

        $('#modulesContainer').append(moduleHtml);
        addContent(moduleCounter); // Add first content by default
    }

    // Remove module
    $(document).on('click', '.remove-module', function() {
        if (confirm('Are you sure you want to remove this module?')) {
            $(this).closest('.module').remove();
            updateModuleNumbers();
        }
    });

    // Add content
    $(document).on('click', '.add-content-btn', function() {
        const moduleId = $(this).data('module-id');
        addContent(moduleId);
    });

    function addContent(moduleId, parentContainer = null) {
        contentCounters[moduleId]++;
        const contentId = contentCounters[moduleId];
        const nestLevel = parentContainer ? parentContainer.parents('.content-item').length : 0;

        const contentHtml = `
            <div class="content-item ${nestLevel > 0 ? 'nested' : ''}" data-content-id="${contentId}">
                <div class="content-header">
                    <strong>Content ${contentId}</strong>
                    <div class="btn-group">
                        <button type="button" class="btn btn-secondary btn-sm add-nested-content-btn" data-module-id="${moduleId}">+ Add Nested</button>
                        <button type="button" class="btn btn-danger btn-sm remove-content-btn">Remove</button>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Content Title *</label>
                    <input type="text" class="content-title" required>
                </div>
                
                <div class="form-group">
                    <label>Content Type *</label>
                    <select class="content-type" required>
                        <option value="text">Text</option>
                        <option value="video">Video</option>
                        <option value="document">Document</option>
                        <option value="quiz">Quiz</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Content Body</label>
                    <textarea class="content-body" rows="3"></textarea>
                </div>
                
                <div class="form-group file-upload-group" style="display: none;">
                    <label>Upload File</label>
                    <input type="file" class="content-file" accept="*/*">
                    <small class="form-text">Max size: 50MB</small>
                </div>
                
                <div class="nested-content"></div>
            </div>
        `;

        if (parentContainer) {
            parentContainer.find('> .nested-content').append(contentHtml);
        } else {
            $(`.contents-container[data-module-id="${moduleId}"]`).append(contentHtml);
        }
    }

    // Add nested content
    $(document).on('click', '.add-nested-content-btn', function() {
        const moduleId = $(this).data('module-id');
        const parentContent = $(this).closest('.content-item');
        addContent(moduleId, parentContent);
    });

    // Handle content type change to show/hide file upload
    $(document).on('change', '.content-type', function() {
        const contentType = $(this).val();
        const fileUploadGroup = $(this).closest('.content-item').find('> .form-group.file-upload-group');
        const fileInput = fileUploadGroup.find('.content-file');
        
        if (contentType === 'video' || contentType === 'document') {
            fileUploadGroup.show();
            if (contentType === 'video') {
                fileInput.attr('accept', 'video/mp4,video/x-msvideo,video/quicktime,video/x-ms-wmv');
            } else {
                fileInput.attr('accept', '.pdf,.doc,.docx,.txt');
            }
        } else {
            fileUploadGroup.hide();
            fileInput.val('');
        }
    });

    // Remove content
    $(document).on('click', '.remove-content-btn', function() {
        const contentItem = $(this).closest('.content-item');
        const moduleContainer = contentItem.closest('.module');
        const allContents = moduleContainer.find('.content-item').length;
        
        if (allContents <= 1) {
            alert('Each module must have at least one content item.');
            return;
        }
        
        if (confirm('Are you sure you want to remove this content? All nested content will also be removed.')) {
            contentItem.remove();
        }
    });

    function updateModuleNumbers() {
        $('.module').each(function(index) {
            $(this).find('.module-title').text('Module ' + (index + 1));
        });
    }

    // Form submission
    $('#courseForm').submit(function(e) {
        e.preventDefault();

        // Validate form
        if (!validateForm()) {
            return;
        }

        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="loading"></span> Creating...');

        const formData = collectFormDataWithFiles();

        $.ajax({
            url: '/api/courses',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showMessage('Course created successfully! Redirecting...', 'success');
                setTimeout(() => {
                    if (response.course && response.course.id) {
                        window.location.href = '/courses/' + response.course.id;
                    } else {
                        window.location.href = '/courses';
                    }
                }, 1500);
            },
            error: function(xhr) {
                let errorMessage = 'Failed to create course. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showMessage(errorMessage, 'error');
                submitBtn.prop('disabled', false);
                submitBtn.html('<span class="btn-text">Create Course</span>');
            }
        });
    });

    function validateForm() {
        const title = $('#courseTitle').val().trim();
        const modules = $('.module').length;

        if (!title) {
            showMessage('Course title is required.', 'error');
            return false;
        }

        if (modules === 0) {
            showMessage('At least one module is required.', 'error');
            return false;
        }

        // Validate each module
        let valid = true;
        $('.module').each(function() {
            const moduleTitle = $(this).find('input[name*="[title]"]').val().trim();
            const contents = $(this).find('.content-item').length;

            if (!moduleTitle) {
                showMessage('All module titles are required.', 'error');
                valid = false;
                return false;
            }

            if (contents === 0) {
                showMessage('Each module must have at least one content item.', 'error');
                valid = false;
                return false;
            }
        });

        return valid;
    }

    function collectFormData() {
        const data = {
            title: $('#courseTitle').val().trim(),
            description: $('#courseDescription').val().trim(),
            modules: []
        };

        $('.module').each(function() {
            const moduleData = {
                title: $(this).find('input[name*="[title]"]').val().trim(),
                description: $(this).find('textarea[name*="[description]"]').val().trim(),
                contents: []
            };

            // Collect top-level contents
            $(this).find('.contents-container > .content-item').each(function() {
                moduleData.contents.push(collectContentData($(this)));
            });

            data.modules.push(moduleData);
        });

        return data;
    }

    function collectFormDataWithFiles() {
        const formData = new FormData();
        
        // Add course fields
        formData.append('title', $('#courseTitle').val().trim());
        formData.append('description', $('#courseDescription').val().trim());
        formData.append('category', $('#courseCategory').val().trim());
        
        // Add course files
        const thumbnailFile = $('#courseThumbnail')[0].files[0];
        if (thumbnailFile) {
            formData.append('thumbnail', thumbnailFile);
        }
        
        const videoFile = $('#courseFeatureVideo')[0].files[0];
        if (videoFile) {
            formData.append('feature_video', videoFile);
        }
        
        // Collect modules data
        const modulesData = [];
        $('.module').each(function() {
            const moduleData = {
                title: $(this).find('input[name*="[title]"]').val().trim(),
                description: $(this).find('textarea[name*="[description]"]').val().trim(),
                contents: []
            };

            // Collect top-level contents
            $(this).find('.contents-container > .content-item').each(function() {
                moduleData.contents.push(collectContentDataWithFiles($(this), formData));
            });

            modulesData.push(moduleData);
        });
        
        formData.append('modules', JSON.stringify(modulesData));
        
        return formData;
    }

    function collectContentData(contentElement) {
        const contentData = {
            title: contentElement.find('> .form-group > .content-title').val().trim(),
            type: contentElement.find('> .form-group > .content-type').val(),
            body: contentElement.find('> .form-group > .content-body').val().trim(),
            children: []
        };

        // Collect nested contents
        contentElement.find('> .nested-content > .content-item').each(function() {
            contentData.children.push(collectContentData($(this)));
        });

        return contentData;
    }

    function collectContentDataWithFiles(contentElement, formData) {
        const contentData = {
            title: contentElement.find('> .form-group > .content-title').val().trim(),
            type: contentElement.find('> .form-group > .content-type').val(),
            body: contentElement.find('> .form-group > .content-body').val().trim(),
            children: []
        };

        // Handle file upload if present
        const fileInput = contentElement.find('> .form-group.file-upload-group > .content-file')[0];
        if (fileInput && fileInput.files.length > 0) {
            // For now, we'll skip file uploads for content items
            // This can be enhanced later with a more sophisticated approach
            // contentData.hasFile = true;
        }

        // Collect nested contents
        contentElement.find('> .nested-content > .content-item').each(function() {
            contentData.children.push(collectContentDataWithFiles($(this), formData));
        });

        return contentData;
    }

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

    // Initialize with one module
    addModule();
});
</script>
@endsection
