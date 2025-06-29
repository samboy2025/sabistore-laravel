@extends('layouts.admin')

@section('title', 'Certificate Management')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Certificate Management</h1>
        <div>
            <a href="{{ route('admin.certificates.templates') }}" class="btn btn-info me-2">
                <i class="fas fa-palette"></i> Templates
            </a>
            <a href="{{ route('admin.certificates.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Issue Certificate
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.certificates.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Search certificates...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="course_id" class="form-label">Course</label>
                    <select class="form-select" id="course_id" name="course_id">
                        <option value="">All Courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Certificates Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">Certificates ({{ $certificates->total() }})</h6>
        </div>
        <div class="card-body">
            @if($certificates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Certificate #</th>
                                <th>User</th>
                                <th>Course</th>
                                <th>Issued Date</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($certificates as $certificate)
                                <tr>
                                    <td>
                                        <span class="fw-bold">{{ $certificate->certificate_number }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $certificate->user->name }}</div>
                                        <small class="text-muted">{{ $certificate->user->email }}</small>
                                    </td>
                                    <td>{{ $certificate->course->title }}</td>
                                    <td>{{ $certificate->issued_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($certificate->expires_at)
                                            {{ $certificate->expires_at->format('M d, Y') }}
                                            @if($certificate->isExpired())
                                                <span class="badge bg-danger ms-1">Expired</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($certificate->isExpired())
                                            <span class="badge bg-danger">Expired</span>
                                        @elseif($certificate->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Revoked</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.certificates.show', $certificate) }}" 
                                               class="btn btn-sm btn-outline-primary">View</a>
                                            
                                            @if($certificate->certificate_path)
                                                <a href="{{ route('admin.certificates.download', $certificate) }}" 
                                                   class="btn btn-sm btn-outline-info">Download</a>
                                            @endif
                                            
                                            @if($certificate->is_active && !$certificate->isExpired())
                                                <form action="{{ route('admin.certificates.revoke', $certificate) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Revoke this certificate?')">
                                                        Revoke
                                                    </button>
                                                </form>
                                            @elseif(!$certificate->is_active && !$certificate->isExpired())
                                                <form action="{{ route('admin.certificates.reactivate', $certificate) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-success">
                                                        Reactivate
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <a href="{{ route('admin.certificates.edit', $certificate) }}" 
                                               class="btn btn-sm btn-outline-warning">Edit</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        Showing {{ $certificates->firstItem() }} to {{ $certificates->lastItem() }} of {{ $certificates->total() }} results
                    </div>
                    {{ $certificates->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                    <h5>No certificates found</h5>
                    <p class="text-muted">No certificates match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
