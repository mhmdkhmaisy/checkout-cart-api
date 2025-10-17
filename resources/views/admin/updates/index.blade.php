@extends('layouts.admin')

@section('title', 'Updates Management')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Updates Management</h1>
        <a href="{{ route('admin.updates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Update
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            @if($updates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Slug</th>
                                <th>Client Update</th>
                                <th>Published</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($updates as $update)
                                <tr>
                                    <td>{{ $update->id }}</td>
                                    <td>{{ $update->title }}</td>
                                    <td><code>{{ $update->slug }}</code></td>
                                    <td>
                                        @if($update->client_update)
                                            <span class="badge bg-info">Yes</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $update->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('updates.show', $update->slug) }}" class="btn btn-outline-secondary" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.updates.edit', $update) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.updates.destroy', $update) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this update?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $updates->links() }}
                </div>
            @else
                <p class="text-center text-muted py-4">No updates created yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection
