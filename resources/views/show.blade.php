@extends('layouts.app')

@section('title', 'Contact Details')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h1>Contact: {{ $contact->name }}</h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="{{ route('contacts.index') }}" class="btn btn-secondary">Back to Contacts</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Contact Details</h5>
    </div>
    <div class="card-body">
        <p><strong>Email:</strong> {{ $contact->email ?? 'N/A' }}</p>
        <p><strong>Phone:</strong> {{ $contact->phone ?? 'N/A' }}</p>
        <p><strong>Status:</strong> {{ ucfirst($contact->status) }}</p>
        <hr>
        <h6>Custom Fields</h6>
        <ul>
            @forelse ($contact->customFieldValues as $value)
            <li><strong>{{ $value->customField->label }}:</strong> {{ $value->value }}</li>
            @empty
            <li class="text-muted">No custom fields assigned</li>
            @endforelse
        </ul>
    </div>
</div>

<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Merge with Another Contact</h5>
    </div>
    <div class="card-body">
        <p>Select another contact to merge with <strong>{{ $contact->name }}</strong>.</p>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mergeCandidates as $candidate)
                    <tr>
                        <td>{{ $candidate->name }}</td>
                        <td>{{ $candidate->email }}</td>
                        <td>{{ $candidate->phone }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-success prepare-merge"
                                data-master="{{ $contact->id }}"
                                data-secondary="{{ $candidate->id }}">
                                <i class="fas fa-compress-alt"></i> Prepare Merge
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Merge Modal (AJAX loaded) -->
<div class="modal fade" id="mergeModal" tabindex="-1" aria-labelledby="mergeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Merge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="mergeModalBody">
                <!-- Content loaded via JS -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.querySelectorAll('.prepare-merge').forEach(button => {
        button.addEventListener('click', function() {
            const masterId = this.dataset.master;
            const secondaryId = this.dataset.secondary;

            fetch('{{ route("contacts.prepare-merge") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        contact_ids: [masterId, secondaryId]
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const contact1 = data.data.contact1;
                        const contact2 = data.data.contact2;
                        let html = `<p>You're about to merge <strong>${contact1.name}</strong> and <strong>${contact2.name}</strong>.</p>`;
                        html += `<pre>${JSON.stringify(data.data, null, 2)}</pre>`;
                        html += `
                        <form method="POST" action="{{ route('contacts.execute-merge') }}">
                            @csrf
                            <input type="hidden" name="master_contact_id" value="${masterId}">
                            <input type="hidden" name="secondary_contact_id" value="${secondaryId}">
                            <button type="submit" class="btn btn-danger">Confirm Merge</button>
                        </form>
                    `;
                        document.getElementById('mergeModalBody').innerHTML = html;
                        new bootstrap.Modal(document.getElementById('mergeModal')).show();
                    } else {
                        alert(data.message || 'Error preparing merge');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Unexpected error occurred.');
                });
        });
    });
</script>
@endpush