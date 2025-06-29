@extends('layouts.admin')

@section('title', 'Login Monitor')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Login Monitor</h1>
        <div>
            <a href="{{ route('admin.login-monitor.export') }}" class="btn btn-info">
                <i class="fas fa-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Logins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_logins']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-sign-in-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Unique Users</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['unique_users']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Suspicious</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['suspicious_logins']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Mobile Logins</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['mobile_logins']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-mobile-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['today_logins']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">Countries</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['unique_countries']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-globe fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.login-monitor.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Search IP, user agent, or user...">
                </div>
                <div class="col-md-2">
                    <label for="user_id" class="form-label">User</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="country" class="form-label">Country</label>
                    <select class="form-select" id="country" name="country">
                        <option value="">All Countries</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" {{ request('country') === $country ? 'selected' : '' }}>
                                {{ $country }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="device_type" class="form-label">Device</label>
                    <select class="form-select" id="device_type" name="device_type">
                        <option value="">All Devices</option>
                        @foreach($deviceTypes as $deviceType)
                            <option value="{{ $deviceType }}" {{ request('device_type') === $deviceType ? 'selected' : '' }}>
                                {{ ucfirst($deviceType) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.login-monitor.index') }}" class="btn btn-outline-secondary">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Login History Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-success">Login History ({{ $logins->total() }})</h6>
        </div>
        <div class="card-body">
            @if($logins->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>IP Address</th>
                                <th>Location</th>
                                <th>Device</th>
                                <th>Login Time</th>
                                <th>Session</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logins as $login)
                                <tr class="{{ $login->is_suspicious ? 'table-warning' : '' }}">
                                    <td>
                                        <div>{{ $login->user->name }}</div>
                                        <small class="text-muted">{{ $login->user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ $login->ip_address }}</span>
                                        @if($login->isFromNewLocation())
                                            <span class="badge bg-info ms-1">New Location</span>
                                        @endif
                                    </td>
                                    <td>{{ $login->location ?: 'Unknown' }}</td>
                                    <td>
                                        <div>{{ $login->device_info }}</div>
                                        @if($login->is_mobile)
                                            <span class="badge bg-secondary">Mobile</span>
                                        @endif
                                    </td>
                                    <td>{{ $login->login_at->format('M d, Y H:i:s') }}</td>
                                    <td>
                                        @if($login->logout_at)
                                            <span class="text-success">{{ $login->session_duration }}m</span>
                                        @else
                                            <span class="text-warning">Active</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($login->is_suspicious)
                                            <span class="badge bg-danger">Suspicious</span>
                                        @else
                                            <span class="badge bg-success">Normal</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.login-monitor.show', $login->user) }}" 
                                               class="btn btn-sm btn-outline-primary">View User</a>
                                            
                                            @if($login->is_suspicious)
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                        onclick="toggleSuspicious({{ $login->id }}, false)">
                                                    Clear Flag
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-outline-warning"
                                                        onclick="toggleSuspicious({{ $login->id }}, true)">
                                                    Mark Suspicious
                                                </button>
                                            @endif
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
                        Showing {{ $logins->firstItem() }} to {{ $logins->lastItem() }} of {{ $logins->total() }} results
                    </div>
                    {{ $logins->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5>No login records found</h5>
                    <p class="text-muted">No login records match your current filters.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function toggleSuspicious(loginId, suspicious) {
    const action = suspicious ? 'mark-suspicious' : 'remove-suspicious';
    const url = `/admin/login-monitor/logins/${loginId}/${action}`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred');
    });
}
</script>
@endsection
