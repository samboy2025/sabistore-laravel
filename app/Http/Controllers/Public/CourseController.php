<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    /**
     * Display the public learning center
     */
    public function index(): View
    {
        $courses = Course::where('is_active', true)
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        $featuredCourses = Course::where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('public.courses.index', compact('courses', 'featuredCourses'));
    }

    /**
     * Display a specific course
     */
    public function show(Course $course): View
    {
        // Ensure course is active
        if (!$course->is_active) {
            abort(404, 'Course not found or not available');
        }

        // Get related courses
        $relatedCourses = Course::where('is_active', true)
            ->where('id', '!=', $course->id)
            ->where('category', $course->category)
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        return view('public.courses.show', compact('course', 'relatedCourses'));
    }

    /**
     * Display vendor learning dashboard
     */
    public function vendorIndex(): View
    {
        $user = auth()->user();
        
        $courses = Course::where('is_active', true)
            ->orderBy('order')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get completion status for vendor
        $completedCourses = collect();
        if ($user && $user->role === 'vendor') {
            // This would typically come from a pivot table or separate model
            // For now, we'll use a simple approach
            $completedCourses = collect(); // placeholder
        }

        return view('vendor.learning.index', compact('courses', 'completedCourses'));
    }

    /**
     * Mark course as complete for vendor
     */
    public function markComplete(Course $course)
    {
        $user = auth()->user();
        
        if (!$user || $user->role !== 'vendor') {
            abort(403, 'Unauthorized');
        }

        // Here you would typically save to a completion table
        // For now, we'll just return success
        
        return response()->json([
            'success' => true,
            'message' => 'Course marked as complete!'
        ]);
    }
} 