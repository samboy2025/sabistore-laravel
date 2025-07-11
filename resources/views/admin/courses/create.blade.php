@extends("layouts.app")

@section("title", "Create Course - Admin")

@section("content")
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create New Course</h1>
        <p class="text-gray-600 mt-2">Add a new learning course for vendors</p>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
        <form method="POST" action="{{ route("admin.courses.store") }}" enctype="multipart/form-data">
            @csrf
            
            <div class="grid grid-cols-1 gap-6">
                <!-- Course Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Course Title</label>
                    <input type="text" 
                           name="title" 
                           id="title" 
                           value="{{ old('title') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Course Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" 
                              id="description" 
                              rows="4" 
                              required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category and Duration Row -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                        <select name="category" 
                                id="category" 
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('category') border-red-500 @enderror">
                            <option value="">Select Category</option>
                            <option value="marketing" {{ old('category') == 'marketing' ? 'selected' : '' }}>Marketing</option>
                            <option value="sales" {{ old('category') == 'sales' ? 'selected' : '' }}>Sales</option>
                            <option value="business" {{ old('category') == 'business' ? 'selected' : '' }}>Business</option>
                            <option value="technology" {{ old('category') == 'technology' ? 'selected' : '' }}>Technology</option>
                            <option value="finance" {{ old('category') == 'finance' ? 'selected' : '' }}>Finance</option>
                            <option value="communication" {{ old('category') == 'communication' ? 'selected' : '' }}>Communication</option>
                        </select>
                        @error('category')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700 mb-2">Duration (minutes)</label>
                        <input type="number" 
                               name="duration" 
                               id="duration" 
                               value="{{ old('duration') }}"
                               min="1"
                               placeholder="e.g., 30"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('duration') border-red-500 @enderror">
                        @error('duration')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Content Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-4">Content Type</label>
                    <div class="space-y-3">
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="content_type" 
                                   value="video" 
                                   {{ old('content_type', 'video') == 'video' ? 'checked' : '' }}
                                   class="text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Video (YouTube URL)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" 
                                   name="content_type" 
                                   value="document" 
                                   {{ old('content_type') == 'document' ? 'checked' : '' }}
                                   class="text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Document (PDF/File Upload)</span>
                        </label>
                    </div>
                </div>

                <!-- Video URL (conditional) -->
                <div id="video-section" class="content-section">
                    <label for="content_url" class="block text-sm font-medium text-gray-700 mb-2">YouTube Video URL</label>
                    <input type="url" 
                           name="content_url" 
                           id="content_url" 
                           value="{{ old('content_url') }}"
                           placeholder="https://www.youtube.com/watch?v=..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('content_url') border-red-500 @enderror">
                    @error('content_url')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- File Upload (conditional) -->
                <div id="file-section" class="content-section" style="display: none;">
                    <label for="file_path" class="block text-sm font-medium text-gray-700 mb-2">Upload File</label>
                    <input type="file" 
                           name="file_path" 
                           id="file_path" 
                           accept=".pdf,.doc,.docx,.ppt,.pptx"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('file_path') border-red-500 @enderror">
                    <p class="mt-1 text-sm text-gray-500">Accepted formats: PDF, DOC, DOCX, PPT, PPTX (Max: 10MB)</p>
                    @error('file_path')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Course Options -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                        <input type="number" 
                               name="order" 
                               id="order" 
                               value="{{ old('order', 0) }}"
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        <p class="mt-1 text-sm text-gray-500">Lower numbers appear first</p>
                    </div>

                    <div class="space-y-3 pt-6">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_featured" 
                                   value="1" 
                                   {{ old('is_featured') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Featured Course</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   {{ old('is_active', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.courses.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Create Course
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contentTypeRadios = document.querySelectorAll('input[name="content_type"]');
    const videoSection = document.getElementById('video-section');
    const fileSection = document.getElementById('file-section');

    function toggleContentSections() {
        const selectedType = document.querySelector('input[name="content_type"]:checked').value;
        
        if (selectedType === 'video') {
            videoSection.style.display = 'block';
            fileSection.style.display = 'none';
        } else {
            videoSection.style.display = 'none';
            fileSection.style.display = 'block';
        }
    }

    contentTypeRadios.forEach(radio => {
        radio.addEventListener('change', toggleContentSections);
    });

    // Initialize
    toggleContentSections();
});
</script>
@endsection
