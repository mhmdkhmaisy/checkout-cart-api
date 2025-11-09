@extends('admin.layout')

@section('header', 'Edit Wiki Page')

@section('content')
<div class="max-w-5xl">
    <form action="{{ route('admin.wiki.update', $wikiPage) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-dragon-surface border border-dragon-border rounded-lg p-6">
            <h3 class="text-xl font-semibold text-dragon-silver mb-4">Basic Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-dragon-silver mb-2">
                        Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" value="{{ old('title', $wikiPage->title) }}" required
                           class="w-full px-4 py-2 bg-dragon-accent border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:outline-none">
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-dragon-silver mb-2">
                        Slug <small class="text-dragon-silver-dark">(leave empty to auto-generate)</small>
                    </label>
                    <input type="text" name="slug" value="{{ old('slug', $wikiPage->slug) }}"
                           class="w-full px-4 py-2 bg-dragon-accent border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:outline-none">
                    @error('slug')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-dragon-silver mb-2">Category</label>
                    <input type="text" name="category" value="{{ old('category', $wikiPage->category) }}" list="categories"
                           class="w-full px-4 py-2 bg-dragon-accent border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:outline-none"
                           placeholder="e.g., Getting Started, Game Mechanics">
                    <datalist id="categories">
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}">
                        @endforeach
                    </datalist>
                </div>

                <div>
                    <label class="block text-sm font-medium text-dragon-silver mb-2">Icon <small class="text-dragon-silver-dark">(FontAwesome class)</small></label>
                    <input type="text" name="icon" value="{{ old('icon', $wikiPage->icon) }}"
                           class="w-full px-4 py-2 bg-dragon-accent border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:outline-none"
                           placeholder="e.g., fas fa-book">
                </div>

                <div>
                    <label class="block text-sm font-medium text-dragon-silver mb-2">Order</label>
                    <input type="number" name="order" value="{{ old('order', $wikiPage->order) }}"
                           class="w-full px-4 py-2 bg-dragon-accent border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:outline-none">
                </div>

                <div class="flex items-center">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="published" value="1" {{ old('published', $wikiPage->published) ? 'checked' : '' }}
                               class="w-5 h-5 text-dragon-red bg-dragon-accent border-dragon-border rounded focus:ring-dragon-red">
                        <span class="ml-2 text-dragon-silver">Published</span>
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-dragon-silver mb-2">Description</label>
                <textarea name="description" rows="2"
                          class="w-full px-4 py-2 bg-dragon-accent border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:outline-none"
                          placeholder="Brief description (shown in search results)">{{ old('description', $wikiPage->description) }}</textarea>
            </div>
        </div>

        <div class="bg-dragon-surface border border-dragon-border rounded-lg p-6">
            <h3 class="text-xl font-semibold text-dragon-silver mb-4">Content</h3>
            <textarea name="content" id="wiki-editor" rows="20" required
                      class="w-full px-4 py-2 bg-dragon-accent border border-dragon-border rounded-lg text-dragon-silver focus:border-dragon-red focus:outline-none font-mono"
                      placeholder="Write your wiki content here...">{{ old('content', $wikiPage->content) }}</textarea>
            @error('content')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex gap-4">
            <button type="submit" class="bg-dragon-red hover:bg-dragon-red-dark text-white px-6 py-3 rounded-lg transition-colors">
                <i class="fas fa-save mr-2"></i>Update Wiki Page
            </button>
            <a href="{{ route('admin.wiki.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>Cancel
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.tiny.cloud/1/ea6lmdx6nvv98d56f09lpj5uw8uamujpo66dpsll9w9c8hge/tinymce/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#wiki-editor',
    height: 600,
    menubar: true,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
        'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
        'insertdatetime', 'media', 'table', 'help', 'wordcount', 'codesample'
    ],
    toolbar: 'undo redo | formatselect | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image codesample | removeformat | help',
    content_style: 'body { font-family: Inter, sans-serif; font-size: 14px; background: #141414; color: #f0f0f0; }',
    skin: 'oxide-dark',
    content_css: 'dark'
});
</script>
@endsection
