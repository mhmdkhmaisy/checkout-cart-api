@extends('layouts.admin')

@section('title', 'Edit Update')

@section('content')
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="{{ route('admin.updates.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Back to Updates
        </a>
    </div>

    <h1 class="h3 mb-4">Edit Update: {{ $update->title }}</h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.updates.update', $update) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="title" class="form-label">Title *</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                           id="title" name="title" value="{{ old('title', $update->title) }}" required>
                    <small class="form-text text-muted">Slug: <code>{{ $update->slug }}</code> (auto-generated)</small>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Content (JSON) *</label>
                    <textarea class="form-control @error('content') is-invalid @enderror" 
                              id="content" name="content" rows="15" required>{{ old('content', $update->content) }}</textarea>
                    <small class="form-text text-muted">
                        Supported block types: header, paragraph, list, code, image, alert
                        <br>
                        <a href="#" onclick="document.getElementById('json-help').style.display='block'; return false;">Show JSON Examples</a>
                    </small>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div id="json-help" style="display: none;" class="mb-3 p-3 bg-light border rounded">
                    <h6>JSON Block Examples:</h6>
                    <pre><code>Header: {"type": "header", "data": {"text": "Title", "level": 2}}
Paragraph: {"type": "paragraph", "data": {"text": "Text content"}}
List: {"type": "list", "data": {"style": "unordered", "items": ["Item 1", "Item 2"]}}
Code: {"type": "code", "data": {"code": "console.log('hello');"}}
Alert: {"type": "alert", "data": {"type": "info", "message": "Important note"}}</code></pre>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="client_update" name="client_update" value="1" {{ old('client_update', $update->client_update) ? 'checked' : '' }}>
                    <label class="form-check-label" for="client_update">
                        This update requires a client update
                    </label>
                    <small class="form-text text-muted d-block">Check this if users need to download a new client version</small>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update
                    </button>
                    <a href="{{ route('admin.updates.index') }}" class="btn btn-secondary">Cancel</a>
                    <a href="{{ route('updates.show', $update->slug) }}" class="btn btn-outline-secondary" target="_blank">
                        <i class="fas fa-eye"></i> Preview
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
