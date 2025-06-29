<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Models\CourseEnrollment;
use App\Models\LearningProgress;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Show course details
     */
    public function show(Course $course): View
    {
        $user = Auth::user();
        
        // Check if user is enrolled
        $enrollment = $user->courseEnrollments()
            ->where('course_id', $course->id)
            ->first();

        // Get course lessons
        $lessons = $course->lessons()->active()->ordered()->get();

        // Get progress if enrolled
        $progress = [];
        if ($enrollment) {
            $progress = $user->learningProgress()
                ->where('course_id', $course->id)
                ->get()
                ->keyBy('lesson_id');
        }

        return view('buyer.courses.show', compact('course', 'enrollment', 'lessons', 'progress'));
    }

    /**
     * Enroll in a course
     */
    public function enroll(Request $request, Course $course): JsonResponse
    {
        $user = Auth::user();

        // Check if already enrolled
        if ($user->isEnrolledInCourse($course->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already enrolled in this course.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            if ($course->is_free) {
                // Free enrollment
                $enrollment = $user->enrollInCourse($course->id, 0, 'free');
            } else {
                // Paid enrollment - check wallet balance
                if (!$user->hasSufficientWalletBalance($course->price)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Insufficient wallet balance. Please fund your wallet first.'
                    ], 400);
                }

                // Debit wallet
                $wallet = $user->getOrCreateWallet();
                $wallet->debit(
                    $course->price,
                    'purchase',
                    "Course enrollment: {$course->title}",
                    'COURSE-' . $course->id . '-' . time()
                );

                // Create enrollment
                $enrollment = $user->enrollInCourse($course->id, $course->price, 'wallet');
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Successfully enrolled in the course!',
                'enrollment_id' => $enrollment->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to enroll in course. Please try again.'
            ], 500);
        }
    }

    /**
     * Show lesson content
     */
    public function lesson(Course $course, CourseLesson $lesson): View
    {
        $user = Auth::user();

        // Check if user is enrolled or lesson is preview
        $enrollment = $user->courseEnrollments()
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment && !$lesson->is_preview) {
            abort(403, 'You must be enrolled in this course to access this lesson.');
        }

        // Get or create progress record
        $progress = null;
        if ($enrollment) {
            $progress = LearningProgress::firstOrCreate([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'lesson_id' => $lesson->id,
            ]);
        }

        // Get all lessons for navigation
        $allLessons = $course->lessons()->active()->ordered()->get();
        
        // Find current lesson index
        $currentIndex = $allLessons->search(function ($item) use ($lesson) {
            return $item->id === $lesson->id;
        });

        $previousLesson = $currentIndex > 0 ? $allLessons[$currentIndex - 1] : null;
        $nextLesson = $currentIndex < $allLessons->count() - 1 ? $allLessons[$currentIndex + 1] : null;

        return view('buyer.courses.lesson', compact(
            'course', 
            'lesson', 
            'progress', 
            'allLessons', 
            'previousLesson', 
            'nextLesson',
            'enrollment'
        ));
    }

    /**
     * Mark lesson as completed
     */
    public function completeLesson(Request $request, Course $course, CourseLesson $lesson): JsonResponse
    {
        $user = Auth::user();

        // Check enrollment
        $enrollment = $user->courseEnrollments()
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course.'
            ], 403);
        }

        // Get or create progress
        $progress = LearningProgress::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);

        // Mark as completed
        $progress->markCompleted();

        return response()->json([
            'success' => true,
            'message' => 'Lesson marked as completed!',
            'progress_percentage' => $enrollment->fresh()->progress_percentage
        ]);
    }

    /**
     * Update video watch time
     */
    public function updateWatchTime(Request $request, Course $course, CourseLesson $lesson): JsonResponse
    {
        $request->validate([
            'watch_time' => 'required|integer|min:0'
        ]);

        $user = Auth::user();

        // Check enrollment
        $enrollment = $user->courseEnrollments()
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'You are not enrolled in this course.'
            ], 403);
        }

        // Get or create progress
        $progress = LearningProgress::firstOrCreate([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);

        // Update watch time
        $progress->updateWatchTime($request->watch_time);

        return response()->json([
            'success' => true,
            'is_completed' => $progress->fresh()->is_completed
        ]);
    }
}
