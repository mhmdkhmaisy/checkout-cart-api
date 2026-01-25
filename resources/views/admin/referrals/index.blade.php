@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-white">Referral Management</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRefModal">
            <i class="fas fa-plus"></i> Create Link
        </button>
    </div>

    <div class="card bg-dark border-secondary">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Target URL</th>
                            <th>Total Clicks</th>
                            <th>Unique Clicks</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($links as $link)
                        <tr>
                            <td>{{ $link->name }}</td>
                            <td><code>{{ $link->code }}</code></td>
                            <td><small>{{ $link->target_url }}</small></td>
                            <td>{{ $link->total_clicks }}</td>
                            <td>{{ $link->unique_clicks }}</td>
                            <td>
                                <a href="{{ route('admin.referrals.show', $link) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-chart-line"></i>
                                </a>
                                <form action="{{ route('admin.referrals.destroy', $link) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createRefModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('admin.referrals.store') }}" method="POST" class="modal-content bg-dark text-white border-secondary">
            @csrf
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Create Referral Link</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Name (Internal)</label>
                    <input type="text" name="name" class="form-control bg-secondary text-white border-0" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Target URL</label>
                    <input type="text" name="target_url" class="form-control bg-secondary text-white border-0" value="/" required>
                </div>
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>
@endsection
