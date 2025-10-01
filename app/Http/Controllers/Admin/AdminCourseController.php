<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

/**
 * Class AdminCourseController
 *
 * Manages the administration of courses, including creation, editing, and deletion.
 *
 * @package App\Http\Controllers\Admin
 */
class AdminCourseController extends Controller
{
    /**
     * Display a listing of the courses.
     *
     * Retrieves and displays a paginated list of courses, with filtering and search functionality.
     *
     * @param Request $request The request object for filtering and searching.
     * @return View Returns the view with the list of courses.
     */
    public function index(Request $request): View
    {
        $query = Course::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $active = $request->status === 'active';
            $query->where('is_active', $active);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $courses = $query->orderBy('order')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.courses.index', compact('courses'));
    }

    /**
     * Show the form for creating a new course.
     *
     * @return View Returns the view for creating a new course.
     */
    public function create(): View
    {
        return view('admin.courses.create');
    }

    /**
     * Store a newly created course in storage.
     *
     * Validates the request data, handles file uploads, and creates a new course.
     *
     * @param Request $request The request object containing the course data.
     * @return RedirectResponse Redirects to the course index with a success message.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'content_type' => 'required|in:video,document',
            'content_url' => 'required_if:content_type,video|nullable|url',
            'file_path' => 'required_if:content_type,document|nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
            'duration' => 'nullable|integer|min:1',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0'
        ]);

        $data = $request->except(['file_path', 'content_type']);
        
        // Generate slug
        $data['slug'] = Str::slug($request->title);
        
        // Handle file upload if document type
        if ($request->content_type === 'document' && $request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('courses', $filename, 'public');
            $data['content_url'] = $path;
            $data['type'] = 'pdf'; // Set type based on file
        } else {
            $data['type'] = 'video';
        }
        
        // Handle duration - map from 'duration' to 'duration_minutes'
        if ($request->filled('duration')) {
            $data['duration_minutes'] = $request->duration;
        }
        unset($data['duration']);
        
        // Handle order field
        if ($request->filled('order')) {
            $data['order'] = $request->order;
        } else {
            $data['order'] = 0;
        }

        // Set defaults for checkboxes
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');

        Course::create($data);

        return redirect()->route('admin.courses.index')->with('success', 'Course created successfully');
    }

    /**
     * Display the specified course.
     *
     * @param Course $course The course to be displayed.
     * @return View Returns the view with the course details.
     */
    public function show(Course $course): View
    {
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Show the form for editing the specified course.
     *
     * @param Course $course The course to be edited.
     * @return View Returns the view for editing the course.
     */
    public function edit(Course $course): View
    {
        return view('admin.courses.edit', compact('course'));
    }

    /**
     * Update the specified course in storage.
     *
     * Validates the request data, handles file uploads, and updates the existing course.
     *
     * @param Request $request The request object containing the updated course data.
     * @param Course $course The course to be updated.
     * @return RedirectResponse Redirects to the course index with a success message.
     */
    public function update(Request $request, Course $course): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string',
            'content_type' => 'required|in:video,document',
            'content_url' => 'required_if:content_type,video|nullable|url',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:10240',
            'duration' => 'nullable|integer|min:1',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'order' => 'nullable|integer|min:0'
        ]);

        $data = $request->except(['file_path', 'content_type']);
        
        // Update slug if title changed
        if ($request->title !== $course->title) {
            $data['slug'] = Str::slug($request->title);
        }
        
        // Handle file upload if document type
        if ($request->content_type === 'document' && $request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('courses', $filename, 'public');
            $data['content_url'] = $path;
            $data['type'] = 'pdf';
        } elseif ($request->content_type === 'video') {
            $data['type'] = 'video';
        }
        
        // Handle duration - map from 'duration' to 'duration_minutes'
        if ($request->filled('duration')) {
            $data['duration_minutes'] = $request->duration;
        }
        unset($data['duration']);
        
        // Handle order field
        if ($request->filled('order')) {
            $data['order'] = $request->order;
        }

        // Set defaults for checkboxes
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');

        $course->update($data);

        return redirect()->route('admin.courses.index')->with('success', 'Course updated successfully');
    }

    /**
     * Remove the specified course from storage.
     *
     * @param Course $course The course to be deleted.
     * @return RedirectResponse Redirects to the course index with a success message.
     */
    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();
        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully');
    }
} 