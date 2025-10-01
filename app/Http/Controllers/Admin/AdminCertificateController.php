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
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class AdminCertificateController
 *
 * Manages all certificate-related operations in the admin panel, including creation,
 * viewing, updating, and deletion of certificates, as well as template management.
 *
 * @package App\Http\Controllers\Admin
 */
class AdminCertificateController extends Controller
{
    /**
     * Display a listing of certificates.
     *
     * Retrieves and displays a paginated list of certificates, with filtering and search functionality.
     *
     * @param Request $request The request object containing filter and search parameters.
     * @return View Returns the view with the list of certificates.
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
     * Show the form for creating a new certificate.
     *
     * @return View Returns the view for creating a new certificate.
     */
    public function create(): View
    {
        $courses = Course::active()->get();
        $users = User::where('role', '!=', 'admin')->get();
        return view('admin.certificates.create', compact('courses', 'users'));
    }

    /**
     * Store a newly created certificate in the database.
     *
     * Validates the request data, checks for course completion and existing certificates,
     * then creates a new certificate and generates its PDF.
     *
     * @param Request $request The request object containing the certificate data.
     * @return RedirectResponse Redirects to the certificate index with a success message or back with errors.
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
     * Display the specified certificate.
     *
     * @param Certificate $certificate The certificate to be displayed.
     * @return View Returns the view with the certificate details.
     */
    public function show(Certificate $certificate): View
    {
        $certificate->load(['user', 'course']);
        return view('admin.certificates.show', compact('certificate'));
    }

    /**
     * Show the form for editing the specified certificate.
     *
     * @param Certificate $certificate The certificate to be edited.
     * @return View Returns the view for editing the certificate.
     */
    public function edit(Certificate $certificate): View
    {
        $courses = Course::active()->get();
        $users = User::where('role', '!=', 'admin')->get();
        return view('admin.certificates.edit', compact('certificate', 'courses', 'users'));
    }

    /**
     * Update the specified certificate in the database.
     *
     * Validates the request data and updates the certificate. If the template data is changed,
     * it regenerates the certificate PDF.
     *
     * @param Request $request The request object containing the updated certificate data.
     * @param Certificate $certificate The certificate to be updated.
     * @return RedirectResponse Redirects to the certificate index with a success message.
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
     * Remove the specified certificate from the database.
     *
     * Deletes the certificate and its associated PDF file from storage.
     *
     * @param Certificate $certificate The certificate to be deleted.
     * @return RedirectResponse Redirects to the certificate index with a success message.
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
     * Revoke a certificate.
     *
     * Marks the certificate as inactive.
     *
     * @param Certificate $certificate The certificate to be revoked.
     * @return RedirectResponse Redirects back with a success message.
     */
    public function revoke(Certificate $certificate): RedirectResponse
    {
        $certificate->update(['is_active' => false]);

        return redirect()->back()
            ->with('success', 'Certificate revoked successfully.');
    }

    /**
     * Reactivate a certificate.
     *
     * Marks the certificate as active.
     *
     * @param Certificate $certificate The certificate to be reactivated.
     * @return RedirectResponse Redirects back with a success message.
     */
    public function reactivate(Certificate $certificate): RedirectResponse
    {
        $certificate->update(['is_active' => true]);

        return redirect()->back()
            ->with('success', 'Certificate reactivated successfully.');
    }

    /**
     * Download the certificate PDF.
     *
     * @param Certificate $certificate The certificate to be downloaded.
     * @return StreamedResponse|RedirectResponse Returns the PDF file for download or redirects back with an error.
     */
    public function download(Certificate $certificate): StreamedResponse|RedirectResponse
    {
        if (!$certificate->certificate_path || !Storage::disk('public')->exists($certificate->certificate_path)) {
            return redirect()->back()
                ->with('error', 'Certificate file not found.');
        }

        return Storage::disk('public')->download($certificate->certificate_path, 
            "certificate_{$certificate->certificate_number}.pdf");
    }

    /**
     * Show the certificate templates management page.
     *
     * @return View Returns the view for managing certificate templates.
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
     * Update the certificate templates settings.
     *
     * Validates the request data and updates the certificate template settings, including uploading a new background image if provided.
     *
     * @param Request $request The request object containing the template settings.
     * @return RedirectResponse Redirects back with a success message.
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
     * Generate a PDF for the certificate (placeholder implementation).
     *
     * This method is responsible for generating and saving the certificate PDF file.
     * The current implementation is a placeholder and should be replaced with a proper
     * PDF generation library (e.g., TCPDF, DomPDF, mPDF).
     *
     * @param Certificate $certificate The certificate for which to generate the PDF.
     * @return void
     */
    private function generateCertificatePDF(Certificate $certificate): void
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
