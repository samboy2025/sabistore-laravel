@extends('layouts.app')

@section('title', 'Learning Center - SabiStore')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-gray-900 mb-4">Learning Center</h1>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Enhance your business skills with our comprehensive collection of courses designed specifically for vendors and entrepreneurs.
                </p>
            </div>
        </div>
    </div>

    <!-- Featured Courses Section -->
    @if($featuredCourses->count() > 0)
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="mb-8">
            <h2 class="text-3xl font-bold text-gray-900 mb-4">Featured Courses</h2>
            <p class="text-gray-600">Start with our most popular and essential courses</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-16">
            @foreach($featuredCourses as $course)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            Featured
                        </span>
                        @if($course->category)
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($course->category) }}
                        </span>
                        @endif
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $course->title }}</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $course->description }}</p>
                    
                    <div class="flex items-center justify-between">
                        @if($course->duration_minutes)
                        <span class="text-sm text-gray-500">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $course->formatted_duration }}
                        </span>
                        @endif
                        
                        <a href="{{ route('courses.show', $course->slug) }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            Start Learning
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- All Courses Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 mb-4">All Courses</h2>
                <p class="text-gray-600">Explore our complete library of business courses</p>
            </div>
            
            <!-- Filters -->
            <div class="mt-4 md:mt-0">
                <form method="GET" class="flex flex-col sm:flex-row gap-4">
                    <select name="category" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 focus:border-red-500" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="marketing" {{ request('category') == 'marketing' ? 'selected' : '' }}>Marketing</option>
                        <option value="sales" {{ request('category') == 'sales' ? 'selected' : '' }}>Sales</option>
                        <option value="business" {{ request('category') == 'business' ? 'selected' : '' }}>Business</option>
                        <option value="technology" {{ request('category') == 'technology' ? 'selected' : '' }}>Technology</option>
                        <option value="finance" {{ request('category') == 'finance' ? 'selected' : '' }}>Finance</option>
                        <option value="communication" {{ request('category') == 'communication' ? 'selected' : '' }}>Communication</option>
                    </select>
                </form>
            </div>
        </div>

        @if($courses->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($courses as $course)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-3">
                        @if($course->category)
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($course->category) }}
                        </span>
                        @endif
                        
                        @if($course->is_featured)
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            Featured
                        </span>
                        @endif
                    </div>
                    
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $course->title }}</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ Str::limit($course->description, 100) }}</p>
                    
                    <div class="flex items-center justify-between">
                        @if($course->duration_minutes)
                        <span class="text-sm text-gray-500">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $course->formatted_duration }}
                        </span>
                        @endif
                        
                        <a href="{{ route('courses.show', $course->slug) }}" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                            View
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($courses->hasPages())
        <div class="mt-12">
            {{ $courses->appends(request()->query())->links() }}
        </div>
        @endif

        @else
        <div class="text-center py-16">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">No courses found</h3>
            <p class="mt-2 text-gray-500">No courses match your current filter criteria.</p>
        </div>
        @endif
    </div>

    <!-- CTA Section -->
    @guest
    <div class="bg-red-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h2 class="text-3xl font-bold text-white mb-4">Ready to Start Your Vendor Journey?</h2>
                <p class="text-xl text-red-100 mb-8 max-w-2xl mx-auto">
                    Join thousands of successful vendors who have transformed their businesses with our platform.
                </p>
                <div class="space-x-4">
                    <a href="{{ route('register') }}" class="inline-flex items-center px-6 py-3 bg-white text-red-600 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                        Become a Vendor
                    </a>
                    <a href="{{ route('vendors.directory') }}" class="inline-flex items-center px-6 py-3 border border-white text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">
                        Browse Vendors
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endguest
</div>

<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection 