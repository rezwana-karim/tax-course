<div class="content-item {{ $level > 0 ? 'nested' : '' }}" style="margin-left: {{ $level * 30 }}px;">
    <div class="content-header">
        <div class="content-title-section">
            <span class="content-type-badge content-type-{{ $content->type }}">{{ ucfirst($content->type) }}</span>
            <h4>{{ $content->title }}</h4>
        </div>
        
        @if($content->children->count() > 0)
        <button type="button" class="btn-toggle content-toggle" data-target="content-children-{{ $content->id }}" aria-expanded="true">
            <span class="toggle-icon">▼</span>
        </button>
        @endif
    </div>

    @if($content->body)
    <div class="content-body">
        <p>{{ $content->body }}</p>
    </div>
    @endif

    @if($content->file_path)
    <div class="content-file">
        @if($content->type === 'video')
        <video controls width="100%">
            <source src="{{ asset('storage/' . $content->file_path) }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
        @elseif($content->type === 'document')
        <div class="document-link">
            <a href="{{ asset('storage/' . $content->file_path) }}" target="_blank" class="btn btn-sm btn-secondary">
                📄 View Document
            </a>
        </div>
        @else
        <div class="file-link">
            <a href="{{ asset('storage/' . $content->file_path) }}" target="_blank" class="btn btn-sm btn-secondary">
                📎 Download File
            </a>
        </div>
        @endif
    </div>
    @endif

    @if($content->children->count() > 0)
    <div class="nested-contents" id="content-children-{{ $content->id }}">
        @foreach($content->children as $childContent)
            @include('courses.partials.content-item', ['content' => $childContent, 'level' => $level + 1])
        @endforeach
    </div>
    @endif
</div>
