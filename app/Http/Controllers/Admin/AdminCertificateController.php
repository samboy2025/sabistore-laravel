<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\User;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class AdminCertificateController extends Controller
{
    /**
     * Display a listing of certificates
     */
    public function index(Request $request): View
    {
        $query = Certificate::with(['user', 'course']);

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->where('expires_at', '<', now());
            }
        }

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('certificate_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('course', function($q) use ($request) {
                      $q->where('title', 'like', '%' . $request->search . '%');
                  });
            });
        }

        $certificates = $query->latest()->paginate(20);
        $courses = Course::active()->get();

        return view('admin.certificates.index', compact('certificates', 'courses'));
    }

    /**
     * Show the form for creating a new certificate
     */
    public function create(): View
    {
        $courses = Course::active()->get();
        $users = User::where('role', '!=', 'admin')->get();
        return view('admin.certificates.create', compact('courses', 'users'));
    }

    /**
     * Store a newly created certificate
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'expires_at' => 'nullable|date|after:today',
            'template_data' => 'nullable|array',
        ]);

        // Check if user has completed the course
        $enrollment = CourseEnrollment::where('user_id', $validated['user_id'])
            ->where('course_id', $validated['course_id'])
            ->where('status', 'completed')
            ->first();

        if (!$enrollment) {
            return redirect()->back()
                ->withErrors(['user_id' => 'User must complete the course before receiving a certificate.'])
                ->withInput();
        }

        // Check if certificate already exists
        $existingCertificate = Certificate::where('user_id', $validated['user_id'])
            ->where('course_id', $validated['course_id'])
            ->first();

        if ($existingCertificate) {
            return redirect()->back()
                ->withErrors(['user_id' => 'Certificate already exists for this user and course.'])
                ->withInput();
        }

        $certificate = Certificate::create($validated);

        // Generate certificate PDF (placeholder - implement actual PDF generation)
        $this->generateCertificatePDF($certificate);

        return redirect()->route('admin.certificates.index')
            ->with('success', 'Certificate created successfully.');
    }

    /**
     * Display the specified certificate
     */
    public function show(Certificate $certificate): View
    {
        $certificate->load(['user', 'course']);
        return view('admin.certificates.show', compact('certificate'));
    }

    /**
     * Show the form for editing the specified certificate
     */
    public function edit(Certificate $certificate): View
    {
        $courses = Course::active()->get();
        $users = User::where('role', '!=', 'admin')->get();
        return view('admin.certificates.edit', compact('certificate', 'courses', 'users'));
    }

    /**
     * Update the specified certificate
     */
    public function update(Request $request, Certificate $certificate): RedirectResponse
    {
        $validated = $request->validate([
            'expires_at' => 'nullable|date|after:today',
            'is_active' => 'boolean',
            'template_data' => 'nullable|array',
        ]);

        $certificate->update($validated);

        // Regenerate certificate PDF if template data changed
        if (isset($validated['template_data'])) {
            $this->generateCertificatePDF($certificate);
        }

        return redirect()->route('admin.certificates.index')
            ->with('success', 'Certificate updated successfully.');
    }

    /**
     * Remove the specified certificate
     */
    public function destroy(Certificate $certificate): RedirectResponse
    {
        // Delete certificate file if exists
        if ($certificate->certificate_path && Storage::disk('public')->exists($certificate->certificate_path)) {
            Storage::disk('public')->delete($certificate->certificate_path);
        }

        $certificate->delete();

        return redirect()->route('admin.certificates.index')
            ->with('success', 'Certificate deleted successfully.');
    }

    /**
     * Revoke certificate
     */
    public function revoke(Certificate $certificate): RedirectResponse
    {
        $certificate->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', 'Certificate revoked successfully.');
    }

    /**
     * Reactivate certificate
     */
    public function reactivate(Certificate $certificate): RedirectResponse
    {
        $certificate->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', 'Certificate reactivated successfully.');
    }

    /**
     * Download certificate
     */
    public function download(Certificate $certificate)
    {
        if (!$certificate->certificate_path || !Storage::disk('public')->exists($certificate->certificate_path)) {
            return redirect()->back()
                ->with('error', 'Certificate file not found.');
        }

        return Storage::disk('public')->download($certificate->certificate_path, 
            "certificate_{$certificate->certificate_number}.pdf");
    }

    /**
     * Show certificate templates management
     */
    public function templates(): View
    {
        // Get template settings
        $templates = [
            'background_image' => get_setting('certificate_background_image'),
            'font_family' => get_setting('certificate_font_family', 'Arial'),
            'font_size' => get_setting('certificate_font_size', '16'),
            'text_color' => get_setting('certificate_text_color', '#000000'),
            'name_position_x' => get_setting('certificate_name_position_x', '50'),
            'name_position_y' => get_setting('certificate_name_position_y', '40'),
            'course_position_x' => get_setting('certificate_course_position_x', '50'),
            'course_position_y' => get_setting('certificate_course_position_y', '60'),
            'date_position_x' => get_setting('certificate_date_position_x', '50'),
            'date_position_y' => get_setting('certificate_date_position_y', '80'),
            'footer_text' => get_setting('certificate_footer_text'),
        ];

        return view('admin.certificates.templates', compact('templates'));
    }

    /**
     * Update certificate templates
     */
    public function updateTemplates(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'font_family' => 'required|string',
            'font_size' => 'required|integer|min:8|max:72',
            'text_color' => 'required|string',
            'name_position_x' => 'required|integer|min:0|max:100',
            'name_position_y' => 'required|integer|min:0|max:100',
            'course_position_x' => 'required|integer|min:0|max:100',
            'course_position_y' => 'required|integer|min:0|max:100',
            'date_position_x' => 'required|integer|min:0|max:100',
            'date_position_y' => 'required|integer|min:0|max:100',
            'footer_text' => 'nullable|string',
        ]);

        // Handle background image upload
        if ($request->hasFile('background_image')) {
            $path = $request->file('background_image')->store('certificates/backgrounds', 'public');
            set_setting('certificate_background_image', $path, 'file');
        }

        // Save other settings
        foreach ($validated as $key => $value) {
            if ($key !== 'background_image') {
                set_setting("certificate_{$key}", $value);
            }
        }

        return redirect()->back()
            ->with('success', 'Certificate templates updated successfully.');
    }

    /**
     * Generate certificate PDF (placeholder implementation)
     */
    private function generateCertificatePDF(Certificate $certificate)
    {
        // This is a placeholder - implement actual PDF generation using libraries like:
        // - TCPDF
        // - DomPDF
        // - mPDF
        
        $filename = "certificate_{$certificate->certificate_number}.pdf";
        $path = "certificates/{$filename}";
        
        // For now, just create a placeholder file
        Storage::disk('public')->put($path, "Certificate placeholder for {$certificate->user->name}");
        
        $certificate->update(['certificate_path' => $path]);
    }
}
