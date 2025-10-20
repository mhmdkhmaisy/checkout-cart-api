@extends('admin.layout')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Deals & Promotions</h1>
        <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Promotion
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Min Amount</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Period</th>
                            <th>Claims</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promotions as $promo)
                        <tr>
                            <td>{{ $promo->id }}</td>
                            <td>
                                <strong>{{ $promo->title }}</strong>
                                @if($promo->isUpcoming())
                                    <span class="badge bg-info">Upcoming</span>
                                @elseif($promo->isCurrentlyActive())
                                    <span class="badge bg-success">Active</span>
                                @elseif($promo->isExpired())
                                    <span class="badge bg-secondary">Expired</span>
                                @endif
                            </td>
                            <td>${{ number_format($promo->min_amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $promo->bonus_type == 'recurrent' ? 'primary' : 'warning' }}">
                                    {{ ucfirst($promo->bonus_type) }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('admin.promotions.toggle-active', $promo) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-{{ $promo->is_active ? 'success' : 'secondary' }}">
                                        {{ $promo->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>
                            <td>
                                <small>
                                    {{ $promo->start_at->format('M d, Y H:i') }}<br>
                                    to {{ $promo->end_at->format('M d, Y H:i') }}
                                </small>
                            </td>
                            <td>
                                {{ $promo->claimed_global }}
                                @if($promo->global_claim_limit)
                                    / {{ $promo->global_claim_limit }}
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.promotions.show', $promo) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.promotions.edit', $promo) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.promotions.destroy', $promo) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No promotions found. <a href="{{ route('admin.promotions.create') }}">Create your first promotion</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
