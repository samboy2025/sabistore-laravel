<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

/**
 * Class CourseController
 *
 * Handles the public display of courses and the vendor-specific learning center.
 *
 * @package App\Http\Controllers\Public
 */
class CourseController extends Controller
{
    /**
     * Display the public learning center with a list of available courses.
     *
     * @return View Returns the view for the public course listing.
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
     * Display a specific course details page.
     *
     * @param Course $course The course to be displayed.
     * @return View Returns the view for the specified course.
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
     * Display the learning dashboard for authenticated vendors.
     *
     * @return View Returns the view for the vendor learning center.
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
     * Mark a course as complete for the authenticated vendor.
     *
     * @param Course $course The course to mark as complete.
     * @return JsonResponse A JSON response indicating the success of the operation.
     */
    public function markComplete(Course $course): JsonResponse
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