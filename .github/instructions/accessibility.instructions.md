# Accessibility Instructions for Blade Templates

applyTo:
  - "resources/views/**/*.blade.php"

## Purpose

These instructions guide GitHub Copilot to generate accessible, inclusive Blade templates that follow WCAG (Web Content Accessibility Guidelines) standards.

## WCAG Compliance Level

Target: **WCAG 2.1 Level AA** compliance as a minimum

## Core Accessibility Principles (POUR)

1. **Perceivable** - Information must be presentable to users in ways they can perceive
2. **Operable** - Interface components must be operable by all users
3. **Understandable** - Information and operation must be understandable
4. **Robust** - Content must be robust enough for interpretation by assistive technologies

## Semantic HTML Guidelines

### Use Proper HTML5 Semantic Elements

```blade
{{-- Good: Semantic structure --}}
<header>
    <nav aria-label="Main navigation">
        <ul>
            <li><a href="{{ route('home') }}">Home</a></li>
        </ul>
    </nav>
</header>

<main id="main-content">
    <article>
        <h1>{{ $course->title }}</h1>
        <section>
            <h2>Course Modules</h2>
        </section>
    </article>
</main>

<footer>
    <p>&copy; {{ date('Y') }} Tax Course Platform</p>
</footer>

{{-- Bad: Non-semantic divs --}}
<div class="header">
    <div class="nav">
        <div class="nav-item">Home</div>
    </div>
</div>
```

### Heading Hierarchy

```blade
{{-- Good: Proper heading hierarchy --}}
<h1>Create New Course</h1>
<section>
    <h2>Course Information</h2>
    <h3>Course Title</h3>
</section>
<section>
    <h2>Modules</h2>
    <h3>Module 1</h3>
    <h4>Module Content</h4>
</section>

{{-- Bad: Skipping heading levels --}}
<h1>Create Course</h1>
<h3>Course Info</h3> {{-- Skipped h2 --}}
```

## Form Accessibility

### Labels and Inputs

```blade
{{-- Good: Proper label association --}}
<div class="form-group">
    <label for="courseTitle">Course Title <span aria-label="required">*</span></label>
    <input 
        type="text" 
        id="courseTitle" 
        name="title" 
        required 
        aria-required="true"
        aria-describedby="titleHelp"
    >
    <small id="titleHelp" class="form-text">
        Enter a descriptive title for your course (max 255 characters)
    </small>
</div>

{{-- Bad: No label or poor association --}}
<input type="text" placeholder="Course Title"> {{-- No label --}}
<label>Title</label><input name="title"> {{-- No association --}}
```

### Required Fields

```blade
{{-- Good: Multiple indicators for required fields --}}
<label for="email">
    Email Address
    <span class="required" aria-label="required">*</span>
</label>
<input 
    type="email" 
    id="email" 
    name="email" 
    required 
    aria-required="true"
>

{{-- Good: Legend for required field explanation --}}
<p class="form-note">
    <span class="required" aria-hidden="true">*</span>
    <span>indicates required field</span>
</p>
```

### Error Messages

```blade
{{-- Good: Accessible error messages --}}
<div class="form-group @error('title') has-error @enderror">
    <label for="courseTitle">Course Title *</label>
    <input 
        type="text" 
        id="courseTitle" 
        name="title"
        aria-invalid="@error('title') true @else false @enderror"
        aria-describedby="@error('title') titleError @enderror titleHelp"
    >
    @error('title')
        <div id="titleError" class="error-message" role="alert">
            {{ $message }}
        </div>
    @enderror
    <small id="titleHelp" class="form-text">Maximum 255 characters</small>
</div>
```

### Fieldsets and Groups

```blade
{{-- Good: Grouping related inputs --}}
<fieldset>
    <legend>Course Information</legend>
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" id="title" name="title">
    </div>
    <div class="form-group">
        <label for="description">Description</label>
        <textarea id="description" name="description"></textarea>
    </div>
</fieldset>
```

### Select and Radio Buttons

```blade
{{-- Good: Accessible select --}}
<label for="contentType">Content Type</label>
<select id="contentType" name="type" aria-describedby="typeHelp">
    <option value="">Choose a type</option>
    <option value="text">Text</option>
    <option value="video">Video</option>
    <option value="document">Document</option>
    <option value="quiz">Quiz</option>
</select>
<small id="typeHelp">Select the type of content for this module</small>

{{-- Good: Accessible radio buttons --}}
<fieldset>
    <legend>Content Type</legend>
    <div class="radio-group">
        <input type="radio" id="typeText" name="type" value="text">
        <label for="typeText">Text</label>
    </div>
    <div class="radio-group">
        <input type="radio" id="typeVideo" name="type" value="video">
        <label for="typeVideo">Video</label>
    </div>
</fieldset>
```

## Button Accessibility

```blade
{{-- Good: Descriptive button text --}}
<button type="submit" class="btn btn-primary">
    Create Course
</button>

<button type="button" aria-label="Add new module" class="btn btn-add">
    <span aria-hidden="true">+</span>
    <span>Add Module</span>
</button>

{{-- Good: Icon-only button --}}
<button 
    type="button" 
    aria-label="Delete module" 
    class="btn btn-danger"
>
    <svg aria-hidden="true" focusable="false">
        <use xlink:href="#icon-delete"></use>
    </svg>
</button>

{{-- Bad: Non-descriptive button --}}
<button>Click here</button>
<button><i class="icon-plus"></i></button> {{-- No label --}}
```

## Link Accessibility

```blade
{{-- Good: Descriptive link text --}}
<a href="{{ route('courses.show', $course->id) }}">
    View {{ $course->title }} course details
</a>

{{-- Good: Link with context --}}
<a href="{{ route('courses.edit', $course->id) }}" aria-label="Edit {{ $course->title }}">
    Edit
</a>

{{-- Bad: Non-descriptive links --}}
<a href="/course/1">Click here</a>
<a href="/more">Read more</a> {{-- No context --}}
```

## Images and Media

```blade
{{-- Good: Informative alt text --}}
<img 
    src="{{ asset('images/course-banner.jpg') }}" 
    alt="Students learning tax fundamentals in a classroom setting"
>

{{-- Good: Decorative images --}}
<img 
    src="{{ asset('images/decoration.svg') }}" 
    alt="" 
    role="presentation"
>

{{-- Good: Complex images with description --}}
<figure>
    <img 
        src="{{ asset('images/tax-chart.png') }}" 
        alt="Tax calculation flowchart"
        aria-describedby="chartDescription"
    >
    <figcaption id="chartDescription">
        This flowchart shows the steps to calculate income tax...
    </figcaption>
</figure>

{{-- Good: Video with captions --}}
<video controls>
    <source src="{{ asset('videos/intro.mp4') }}" type="video/mp4">
    <track 
        kind="captions" 
        src="{{ asset('videos/intro-captions.vtt') }}" 
        srclang="en" 
        label="English"
    >
    Your browser does not support the video tag.
</video>
```

## Dynamic Content and ARIA

### Live Regions

```blade
{{-- Good: Status messages --}}
<div 
    id="message-container" 
    role="status" 
    aria-live="polite" 
    aria-atomic="true"
    class="sr-only"
>
    {{-- JavaScript will update this with messages --}}
</div>

{{-- Good: Alert messages --}}
<div 
    role="alert" 
    aria-live="assertive"
    class="alert alert-error"
>
    {{ session('error') }}
</div>
```

### Loading States

```blade
{{-- Good: Loading indicator --}}
<button type="submit" id="submitBtn" aria-busy="false">
    <span class="btn-text">Create Course</span>
    <span class="spinner" aria-hidden="true" hidden></span>
</button>

<script>
// When loading
document.getElementById('submitBtn').setAttribute('aria-busy', 'true');
document.querySelector('.spinner').removeAttribute('hidden');
</script>
```

### Expandable Content

```blade
{{-- Good: Accordion/collapsible --}}
<div class="accordion">
    <h3>
        <button 
            id="module1-btn"
            aria-expanded="false" 
            aria-controls="module1-content"
            class="accordion-trigger"
        >
            Module 1: Introduction
        </button>
    </h3>
    <div 
        id="module1-content" 
        role="region" 
        aria-labelledby="module1-btn"
        hidden
    >
        <p>Module content goes here...</p>
    </div>
</div>
```

### Modal Dialogs

```blade
{{-- Good: Modal dialog --}}
<div 
    id="deleteModal" 
    role="dialog" 
    aria-labelledby="modalTitle"
    aria-describedby="modalDescription"
    aria-modal="true"
    hidden
>
    <div class="modal-content">
        <h2 id="modalTitle">Confirm Deletion</h2>
        <p id="modalDescription">
            Are you sure you want to delete this course? This action cannot be undone.
        </p>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                Cancel
            </button>
            <button type="button" class="btn btn-danger">
                Delete Course
            </button>
        </div>
    </div>
</div>
```

## Tables

```blade
{{-- Good: Accessible data table --}}
<table>
    <caption>List of courses and their modules</caption>
    <thead>
        <tr>
            <th scope="col">Course Title</th>
            <th scope="col">Number of Modules</th>
            <th scope="col">Created Date</th>
            <th scope="col">Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($courses as $course)
        <tr>
            <th scope="row">{{ $course->title }}</th>
            <td>{{ $course->modules->count() }}</td>
            <td>{{ $course->created_at->format('M d, Y') }}</td>
            <td>
                <a href="{{ route('courses.edit', $course->id) }}" aria-label="Edit {{ $course->title }}">
                    Edit
                </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Bad: Layout table (use CSS instead) --}}
{{-- Don't use tables for layout purposes --}}
```

## Lists

```blade
{{-- Good: Proper list structure --}}
<nav aria-label="Course modules">
    <ul>
        @foreach($course->modules as $module)
        <li>
            <a href="#module-{{ $module->id }}">{{ $module->title }}</a>
            @if($module->contents->count() > 0)
            <ul>
                @foreach($module->contents as $content)
                <li>{{ $content->title }}</li>
                @endforeach
            </ul>
            @endif
        </li>
        @endforeach
    </ul>
</nav>
```

## Color and Contrast

```blade
{{-- Good: Don't rely solely on color --}}
<div class="alert alert-error" role="alert">
    <svg aria-hidden="true" focusable="false">
        <use xlink:href="#icon-error"></use>
    </svg>
    <span>Error: Course title is required</span>
</div>

{{-- Good: Color contrast --}}
<style>
/* Ensure minimum 4.5:1 contrast ratio for normal text */
.btn-primary {
    background-color: #0066cc; /* Dark blue */
    color: #ffffff; /* White */
}

/* Ensure minimum 3:1 contrast ratio for large text (18pt+ or 14pt+ bold) */
.heading {
    color: #333333;
    background-color: #ffffff;
}
</style>
```

## Focus Management

```blade
{{-- Good: Skip to main content link --}}
<a href="#main-content" class="skip-link">
    Skip to main content
</a>

<main id="main-content" tabindex="-1">
    <h1>Create Course</h1>
</main>

<style>
.skip-link {
    position: absolute;
    top: -40px;
    left: 0;
    background: #000;
    color: #fff;
    padding: 8px;
    text-decoration: none;
    z-index: 100;
}

.skip-link:focus {
    top: 0;
}
</style>

{{-- Good: Visible focus indicators --}}
<style>
:focus {
    outline: 2px solid #0066cc;
    outline-offset: 2px;
}

/* Don't remove outlines without providing an alternative */
button:focus {
    outline: 2px solid #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.3);
}
</style>
```

## Screen Reader Support

```blade
{{-- Good: Screen reader only text --}}
<style>
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}
</style>

<span class="sr-only">This text is only for screen readers</span>

{{-- Good: Hide decorative elements from screen readers --}}
<button>
    <svg aria-hidden="true" focusable="false">
        <use xlink:href="#icon-save"></use>
    </svg>
    <span>Save Course</span>
</button>
```

## Keyboard Navigation

```blade
{{-- Good: Keyboard accessible custom controls --}}
<div 
    role="button" 
    tabindex="0"
    @keydown.enter="handleClick"
    @keydown.space.prevent="handleClick"
>
    Custom Button
</div>

{{-- Good: Logical tab order --}}
<form>
    <input type="text" name="title" tabindex="1">
    <textarea name="description" tabindex="2"></textarea>
    <button type="submit" tabindex="3">Submit</button>
</form>

{{-- Avoid: Positive tabindex values (let natural DOM order prevail) --}}
```

## Language and Direction

```blade
{{-- Good: Language declaration --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Tax Course Platform')</title>
</head>

{{-- Good: Content in different language --}}
<p>The course title in French: <span lang="fr">Cours de fiscalité</span></p>

{{-- Good: Right-to-left support --}}
<html lang="ar" dir="rtl">
```

## Progressive Enhancement

```blade
{{-- Good: Works without JavaScript --}}
<form action="{{ route('courses.store') }}" method="POST">
    @csrf
    {{-- Form fields --}}
    <button type="submit">Create Course</button>
</form>

{{-- Then enhance with JavaScript --}}
@section('scripts')
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();
    // AJAX submission
});
</script>
@endsection
```

## Mobile Accessibility

```blade
{{-- Good: Touch target size (minimum 44x44 pixels) --}}
<style>
.btn {
    min-height: 44px;
    min-width: 44px;
    padding: 12px 24px;
}
</style>

{{-- Good: Responsive meta tag --}}
<meta name="viewport" content="width=device-width, initial-scale=1">

{{-- Good: Prevent zoom disable --}}
{{-- Bad: <meta name="viewport" content="user-scalable=no"> --}}
```

## Testing Guidelines

### Manual Testing Checklist

- [ ] Navigate entire interface using only keyboard (Tab, Shift+Tab, Enter, Space, Arrow keys)
- [ ] Test with screen reader (NVDA, JAWS, VoiceOver)
- [ ] Verify color contrast ratios meet WCAG AA standards
- [ ] Resize text to 200% and verify readability
- [ ] Test on mobile devices and tablets
- [ ] Verify focus indicators are visible
- [ ] Check that all images have appropriate alt text
- [ ] Ensure forms can be completed without mouse

### Automated Testing Tools

- axe DevTools browser extension
- WAVE browser extension
- Lighthouse accessibility audit
- pa11y or similar CI tools

## Common Blade Patterns for This Project

### Course Form
```blade
<form id="courseForm" method="POST" action="{{ route('courses.store') }}">
    @csrf
    
    <fieldset>
        <legend>Course Information</legend>
        
        <div class="form-group @error('title') has-error @enderror">
            <label for="courseTitle">
                Course Title
                <span class="required" aria-label="required">*</span>
            </label>
            <input 
                type="text" 
                id="courseTitle" 
                name="title" 
                value="{{ old('title') }}"
                required 
                aria-required="true"
                aria-invalid="@error('title') true @else false @enderror"
                aria-describedby="@error('title') titleError @enderror titleHelp"
            >
            @error('title')
                <div id="titleError" role="alert" class="error-message">
                    {{ $message }}
                </div>
            @enderror
            <small id="titleHelp" class="form-text">
                Enter a descriptive title (max 255 characters)
            </small>
        </div>
    </fieldset>
    
    <div class="form-actions">
        <button type="submit" class="btn btn-primary">
            Create Course
        </button>
        <a href="{{ route('courses.index') }}" class="btn btn-secondary">
            Cancel
        </a>
    </div>
</form>
```

## Resources

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [MDN Accessibility](https://developer.mozilla.org/en-US/docs/Web/Accessibility)
- [WebAIM](https://webaim.org/)
- [A11y Project](https://www.a11yproject.com/)
- [Laravel Accessibility Best Practices](https://laravel.com/docs/blade#accessibility)

## Remember

- Accessibility is not optional - it's a requirement
- Test with real users who rely on assistive technologies
- Accessibility benefits everyone, not just users with disabilities
- Include accessibility considerations from the start of development
- Regular audits and testing are essential
- Stay updated with WCAG guidelines and best practices
