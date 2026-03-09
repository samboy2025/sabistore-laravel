<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    /**
     * Display certificate details
     */
    public function show(Certificate $certificate): View
    {
        $user = Auth::user();

        // Verify ownership
        if ($certificate->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this certificate.');
        }

        return view('buyer.certificates.show', compact('certificate'));
    }

    /**
     * Download certificate PDF
     */
    public function download(Certificate $certificate): Response
    {
        $user = Auth::user();

        // Verify ownership
        if ($certificate->user_id !== $user->id) {
            abort(403, 'Unauthorized access to this certificate.');
        }

        // Check if file exists
        if (!$certificate->fileExists()) {
            // Regenerate certificate if missing
            $certificate->generatePDF();
        }

        $filePath = Storage::disk('public')->path($certificate->file_path);
        $fileName = 'Certificate_' . $certificate->certificate_number . '.pdf';

        return response()->download($filePath, $fileName);
    }

    /**
     * Verify certificate (public endpoint)
     */
    public function verify(string $certificateNumber): View
    {
        $certificate = Certificate::where('certificate_number', $certificateNumber)
            ->where('is_verified', true)
            ->first();

        if (!$certificate) {
            abort(404, 'Certificate not found or not verified.');
        }

        return view('certificates.verify', compact('certificate'));
    }

    /**
     * Share certificate (public view)
     */
    public function share(Certificate $certificate): View
    {
        // Only show verified certificates
        if (!$certificate->is_verified) {
            abort(404, 'Certificate not found or not verified.');
        }

        return view('certificates.share', compact('certificate'));
    }
}
