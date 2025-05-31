@extends('layouts.app')

@section('title', 'Edit Custom Field')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h1>Edit Custom Field</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('admin.custom-fields.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Custom Field Details</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.custom-fields.update', $customField) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="label" class="form-label">Label *</label>
                    <input type="text" class="form-control @error('label') is-invalid @enderror" id="label" name="label" value="{{ old('label', $customField->label) }}" required>
                    <div class="form-text">Human-readable label shown to users (e.g., "Birthday", "Company Name")</div>
                    @error('label')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="name" class="form-label">Technical Name *</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $customField->name) }}" required>
                    <div class="form-text">Machine-readable name (lowercase, underscores, no spaces, e.g., "birthday", "company_name")</div>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="type" class="form-label">Field Type *</label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="">Select a type...</option>
                        <option value="text" {{ old('type', $customField->type) == 'text' ? 'selected' : '' }}>Text</option>
                        <option value="textarea" {{ old('type', $customField->type) == 'textarea' ? 'selected' : '' }}>Text Area</option>
                        <option value="number" {{ old('type', $customField->type) == 'number' ? 'selected' : '' }}>Number</option>
                        <option value="date" {{ old('type', $customField->type) == 'date' ? 'selected' : '' }}>Date</option>
                        <option value="email" {{ old('type', $customField->type) == 'email' ? 'selected' : '' }}>Email</option>
                        <option value="phone" {{ old('type', $customField->type) == 'phone' ? 'selected' : '' }}>Phone</option>
                    </select>
                    <div class="form-text">Determines the input type and validation rules</div>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input @error('is_filterable') is-invalid @enderror" type="checkbox" id="is_filterable" name="is_filterable" value="1" {{ old('is_filterable', $customField->is_filterable) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_filterable">
                            Allow filtering by this field
                        </label>
                        <div class="form-text">If checked, this field will appear in the contact filters</div>
                        @error('is_filterable')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Update Custom Field</button>
                <a href="{{ route('admin.custom-fields.index') }}" class="btn btn-link">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
