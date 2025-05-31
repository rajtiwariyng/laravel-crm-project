@extends('layouts.app')

@section('title', 'Custom Fields')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h1>Custom Fields</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.custom-fields.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Custom Field
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Custom Fields List</h5>
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Filterable</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customFields as $field)
                        <tr>
                            <td>{{ $field->label }}</td>
                            <td><code>{{ $field->name }}</code></td>
                            <td>{{ ucfirst($field->type) }}</td>
                            <td>
                                @if($field->is_filterable)
                                    <span class="badge bg-success">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.custom-fields.edit', $field) }}" class="btn btn-sm btn-outline-primary btn-action">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.custom-fields.destroy', $field) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this custom field?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-action">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <h4>No custom fields found</h4>
                                <p class="text-muted">Create your first custom field to add more data to contacts.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $customFields->links() }}
        </div>
    </div>
</div>
@endsection
