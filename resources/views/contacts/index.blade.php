@extends('layouts.app')

@section('title', 'Contacts')

@section('content')
<div class="row mb-4">
    <div class="col-md-6">
        <h1>Contacts</h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" id="createContactBtn">
            <i class="fas fa-plus"></i> Add Contact
        </button>
        <button type="button" class="btn btn-warning" id="mergeContactsBtn" disabled>
            <i class="fas fa-object-group"></i> Merge Selected
        </button>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form id="filterForm">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="nameFilter" class="form-label">Name</label>
                    <input type="text" class="form-control" id="nameFilter" name="name">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="emailFilter" class="form-label">Email</label>
                    <input type="text" class="form-control" id="emailFilter" name="email">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="genderFilter" class="form-label">Gender</label>
                    <select class="form-select" id="genderFilter" name="gender">
                        <option value="">All</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
            </div>

            <!-- Custom Field Filters (Dynamic) -->
            @if(isset($filterableCustomFields) && $filterableCustomFields->count() > 0)
            <div class="row" id="customFieldFilters">
                @foreach($filterableCustomFields as $field)
                <div class="col-md-4 mb-3">
                    <label for="customFilter{{ $field->id }}" class="form-label">{{ $field->label }}</label>
                    <input type="{{ $field->type === 'date' ? 'date' : ($field->type === 'number' ? 'number' : 'text') }}" class="form-control custom-filter"
                        id="customFilter{{ $field->id }}"
                        name="custom_filters[{{ $field->id }}]"
                        data-field-id="{{ $field->id }}">
                </div>
                @endforeach
            </div>
            @endif

            <div class="text-end">
                <button type="button" class="btn btn-secondary" id="resetFiltersBtn">Reset</button>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>
</div>

<!-- Contacts Table -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Contact List</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th width="40px">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAllContacts">
                            </div>
                        </th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Gender</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="contactsTableBody">
                    <!-- Contacts will be loaded here via AJAX -->
                    <tr>
                        <td colspan="6" class="text-center">Loading contacts...</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Contacts pagination" id="paginationContainer" class="d-none">
            <ul class="pagination justify-content-center" id="pagination">
                <!-- Pagination will be generated via JavaScript -->
            </ul>
        </nav>

        <!-- Empty State -->
        <div id="emptyState" class="text-center py-5 d-none">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h4>No contacts found</h4>
            <p class="text-muted">Try adjusting your filters or add a new contact.</p>
        </div>
    </div>
</div>

<!-- Create/Edit Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalTitle">Add New Contact</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="contactForm" enctype="multipart/form-data">
                    <input type="hidden" id="contactId" name="contact_id">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                            <div class="invalid-feedback" id="emailError"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone">
                            <div class="invalid-feedback" id="phoneError"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="genderMale" value="male">
                                    <label class="form-check-label" for="genderMale">Male</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="genderFemale" value="female">
                                    <label class="form-check-label" for="genderFemale">Female</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="gender" id="genderOther" value="other">
                                    <label class="form-check-label" for="genderOther">Other</label>
                                </div>
                            </div>
                            <div class="invalid-feedback" id="genderError"></div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="profileImage" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="profileImage" name="profile_image" accept="image/*">
                            <div class="invalid-feedback" id="profileImageError"></div>
                            <div id="currentProfileImage" class="mt-2 d-none">
                                <img src="" alt="Current profile" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="additionalFile" class="form-label">Additional File</label>
                            <input type="file" class="form-control" id="additionalFile" name="additional_file">
                            <div class="invalid-feedback" id="additionalFileError"></div>
                            <div id="currentAdditionalFile" class="mt-2 d-none">
                                <a href="#" target="_blank" id="additionalFileLink">View current file</a>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Fields Section (Dynamic) -->
                    <h5 class="mt-4 mb-3">Custom Fields</h5>
                    <div id="customFieldsContainer">
                        <!-- Custom fields will be loaded here dynamically -->
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="ms-2">Loading custom fields...</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveContactBtn">Save Contact</button>
            </div>
        </div>
    </div>
</div>

<!-- Merge Contacts Modal -->
<div class="modal fade" id="mergeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Merge Contacts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    Select which contact should be the <strong>master record</strong>. The other contact will be marked as merged.
                    Data from the secondary contact will be added to the master where it doesn't conflict.
                </div>

                <div class="row">
                    <!-- Contact 1 -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0" id="contact1Name">Contact 1</h5>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="masterContact" id="masterContact1" value="">
                                    <label class="form-check-label" for="masterContact1">
                                        Make Master
                                    </label>
                                </div>
                            </div>
                            <div class="card-body" id="contact1Details">
                                <!-- Contact 1 details will be loaded here -->
                                <div class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact 2 -->
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0" id="contact2Name">Contact 2</h5>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="masterContact" id="masterContact2" value="">
                                    <label class="form-check-label" for="masterContact2">
                                        Make Master
                                    </label>
                                </div>
                            </div>
                            <div class="card-body" id="contact2Details">
                                <!-- Contact 2 details will be loaded here -->
                                <div class="text-center py-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmMergeBtn" disabled>Confirm Merge</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Global variables
    let currentPage = 1;
    let contacts = [];
    let selectedContacts = [];

    // DOM Elements
    const contactsTableBody = document.getElementById('contactsTableBody');
    const paginationContainer = document.getElementById('paginationContainer');
    const pagination = document.getElementById('pagination');
    const emptyState = document.getElementById('emptyState');
    const filterForm = document.getElementById('filterForm');
    const resetFiltersBtn = document.getElementById('resetFiltersBtn');
    const selectAllContacts = document.getElementById('selectAllContacts');
    const mergeContactsBtn = document.getElementById('mergeContactsBtn');

    // Contact Modal Elements
    const contactModal = new bootstrap.Modal(document.getElementById('contactModal'));
    const contactForm = document.getElementById('contactForm');
    const contactModalTitle = document.getElementById('contactModalTitle');
    const contactId = document.getElementById('contactId');
    const saveContactBtn = document.getElementById('saveContactBtn');
    const createContactBtn = document.getElementById('createContactBtn');

    // Merge Modal Elements
    const mergeModal = new bootstrap.Modal(document.getElementById('mergeModal'));
    const masterContact1 = document.getElementById('masterContact1');
    const masterContact2 = document.getElementById('masterContact2');
    const confirmMergeBtn = document.getElementById('confirmMergeBtn');

    // Delete Modal Elements
    const deleteConfirmModal = new bootstrap.Modal(document.getElementById("deleteConfirmModal"));
    const contactToDeleteIdInput = document.getElementById("contactToDeleteId");
    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");

    // Initialize the page
    document.addEventListener('DOMContentLoaded', function() {
        fetchContacts();
        setupEventListeners();
    });

    // Setup Event Listeners
    function setupEventListeners() {
        // Filter form submission
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            currentPage = 1;
            fetchContacts();
        });

        // Reset filters
        resetFiltersBtn.addEventListener('click', function() {
            filterForm.reset();
            currentPage = 1;
            fetchContacts();
        });

        // Select all contacts
        selectAllContacts.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.contact-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllContacts.checked;
                const contactId = parseInt(checkbox.value);

                if (selectAllContacts.checked) {
                    if (!selectedContacts.includes(contactId)) {
                        selectedContacts.push(contactId);
                    }
                } else {
                    selectedContacts = selectedContacts.filter(id => id !== contactId);
                }
            });

            updateMergeButtonState();
        });

        // Create contact button
        createContactBtn.addEventListener('click', function() {
            resetContactForm();
            contactModalTitle.textContent = 'Add New Contact';
            loadCustomFields();
            contactModal.show();
        });

        // Save contact button
        saveContactBtn.addEventListener('click', function() {
            saveContact();
        });

        // Merge contacts button
        mergeContactsBtn.addEventListener('click', function() {
            if (selectedContacts.length === 2) {
                prepareMerge();
            } else {
                showToast('Please select exactly 2 contacts to merge.', 'error');
            }
        });

        // Master contact selection
        document.querySelectorAll('input[name="masterContact"]').forEach(radio => {
            radio.addEventListener('change', function() {
                confirmMergeBtn.disabled = false;
            });
        });

        // Confirm merge button
        confirmMergeBtn.addEventListener("click", function() {
            executeMerge();
        });

        // Confirm delete button
        confirmDeleteBtn.addEventListener("click", function() {
            executeDelete();
        });
    }
    // Fetch contacts with filters and pagination
    async function fetchContacts() {
        try {
            showLoading();

            // Build query string from filter form
            const formData = new FormData(filterForm);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value) {
                    if (key.includes('custom_filters[')) {
                        // Handle custom field filters
                        const matches = key.match(/custom_filters\[(\d+)\]/);
                        if (matches && matches[1]) {
                            params.append(`custom_filters[${matches[1]}]`, value);
                        }
                    } else {
                        params.append(key, value);
                    }
                }
            }

            // Add pagination
            params.append('page', currentPage);

            const response = await fetch(`/contacts/fetch?${params.toString()}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                contacts = data.contacts;
                renderContacts();
                renderPagination(data.pagination);
            } else {
                showToast('Failed to load contacts.', 'error');
            }
        } catch (error) {
            console.error('Error fetching contacts:', error);
            showToast('An error occurred while loading contacts.', 'error');
        } finally {
            hideLoading();
        }
    }

    // Render contacts table
    function renderContacts() {
        if (contacts.length === 0) {
            contactsTableBody.innerHTML = '';
            paginationContainer.classList.add('d-none');
            emptyState.classList.remove('d-none');
            return;
        }

        emptyState.classList.add('d-none');
        paginationContainer.classList.remove('d-none');

        let html = '';
        contacts.forEach(contact => {
            const isSelected = selectedContacts.includes(contact.id);

            html += `
                <tr>
                    <td>
                        <div class="form-check">
                            <input class="form-check-input contact-checkbox" type="checkbox" value="${contact.id}" 
                                ${isSelected ? 'checked' : ''} onchange="toggleContactSelection(${contact.id})">
                        </div>
                    </td>
                    <td>${contact.name}</td>
                    <td>${contact.email || '-'}</td>
                    <td>${contact.phone || '-'}</td>
                    <td>${contact.gender ? contact.gender.charAt(0).toUpperCase() + contact.gender.slice(1) : '-'}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary btn-action" onclick="editContact(${contact.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-action" onclick="deleteContact(${contact.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        contactsTableBody.innerHTML = html;
    }

    // Render pagination
    function renderPagination(paginationData) {
        if (!paginationData || paginationData.lastPage <= 1) {
            paginationContainer.classList.add('d-none');
            return;
        }

        paginationContainer.classList.remove('d-none');

        let html = '';

        // Previous button
        html += `
            <li class="page-item ${paginationData.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${paginationData.currentPage - 1}); return false;">Previous</a>
            </li>
        `;

        // Page numbers
        const startPage = Math.max(1, paginationData.currentPage - 2);
        const endPage = Math.min(paginationData.lastPage, paginationData.currentPage + 2);

        if (startPage > 1) {
            html += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="changePage(1); return false;">1</a>
                </li>
            `;

            if (startPage > 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === paginationData.currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${i}); return false;">${i}</a>
                </li>
            `;
        }

        if (endPage < paginationData.lastPage) {
            if (endPage < paginationData.lastPage - 1) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }

            html += `
                <li class="page-item">
                    <a class="page-link" href="#" onclick="changePage(${paginationData.lastPage}); return false;">${paginationData.lastPage}</a>
                </li>
            `;
        }

        // Next button
        html += `
            <li class="page-item ${paginationData.currentPage === paginationData.lastPage ? 'disabled' : ''}">
                <a class="page-link" href="#" onclick="changePage(${paginationData.currentPage + 1}); return false;">Next</a>
            </li>
        `;

        pagination.innerHTML = html;
    }

    // Change page
    function changePage(page) {
        currentPage = page;
        fetchContacts();
    }

    // Toggle contact selection
    function toggleContactSelection(id) {
        const index = selectedContacts.indexOf(id);

        if (index === -1) {
            selectedContacts.push(id);
        } else {
            selectedContacts.splice(index, 1);
        }

        updateMergeButtonState();
    }

    // Update merge button state
    function updateMergeButtonState() {
        mergeContactsBtn.disabled = selectedContacts.length !== 2;

        // Update "select all" checkbox state
        const checkboxes = document.querySelectorAll('.contact-checkbox');
        const checkedCount = document.querySelectorAll('.contact-checkbox:checked').length;

        if (checkboxes.length > 0) {
            selectAllContacts.checked = checkedCount === checkboxes.length;
            selectAllContacts.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
        }
    }

    // Load custom fields for contact form
    async function loadCustomFields(contactData = null) {
        try {
            const customFieldsContainer = document.getElementById('customFieldsContainer');

            // If editing, fetch contact data
            let contactCustomFields = {};
            if (contactData) {
                contactCustomFields = contactData.custom_field_values.reduce((acc, field) => {
                    acc[field.custom_field_id] = field.value;
                    return acc;
                }, {});
            }

            // Fetch all custom fields
            const response = await fetch('/contacts/create-data', {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success && data.customFields) {
                let html = '';

                if (data.customFields.length === 0) {
                    html = `
                        <div class="alert alert-info">
                            No custom fields defined. <a href="${route('admin.custom-fields.create')}">Create custom fields</a> to add more data to contacts.
                        </div>
                    `;
                } else {
                    html = '<div class="row">';

                    data.customFields.forEach(field => {
                        const fieldValue = contactCustomFields[field.id] || '';

                        html += `
                            <div class="col-md-6 mb-3 custom-field">
                                <label for="customField${field.id}" class="form-label">${field.label}</label>
                        `;

                        // Render different input types based on field type
                        switch (field.type) {
                            case 'textarea':
                                html += `
                                    <textarea class="form-control" id="customField${field.id}" 
                                        name="custom_fields[${field.id}]" rows="3">${fieldValue}</textarea>
                                `;
                                break;

                            case 'date':
                                html += `
                                    <input type="date" class="form-control" id="customField${field.id}" 
                                        name="custom_fields[${field.id}]" value="${fieldValue}">
                                `;
                                break;

                            case 'number':
                                html += `
                                    <input type="number" class="form-control" id="customField${field.id}" 
                                        name="custom_fields[${field.id}]" value="${fieldValue}">
                                `;
                                break;

                            default: // text, email, phone, etc.
                                html += `
                                    <input type="${field.type === 'email' ? 'email' : 'text'}" class="form-control" 
                                        id="customField${field.id}" name="custom_fields[${field.id}]" value="${fieldValue}">
                                `;
                        }

                        html += `
                                <div class="invalid-feedback" id="customField${field.id}Error"></div>
                            </div>
                        `;
                    });

                    html += '</div>';
                }

                customFieldsContainer.innerHTML = html;
            } else {
                customFieldsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        Failed to load custom fields.
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading custom fields:', error);
            document.getElementById('customFieldsContainer').innerHTML = `
                <div class="alert alert-danger">
                    An error occurred while loading custom fields.
                </div>
            `;
        }
    }

    // Reset contact form
    function resetContactForm() {
        contactForm.reset();
        contactId.value = '';

        // Clear validation errors
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });

        // Hide current images/files
        document.getElementById('currentProfileImage').classList.add('d-none');
        document.getElementById('currentAdditionalFile').classList.add('d-none');
    }

    // Edit contact
    async function editContact(id) {
        try {
            showLoading();

            const response = await fetch(`/contacts/${id}/edit-data`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                resetContactForm();
                contactModalTitle.textContent = 'Edit Contact';

                // Set form values
                contactId.value = data.contact.id;
                document.getElementById('name').value = data.contact.name;
                document.getElementById('email').value = data.contact.email || '';
                document.getElementById('phone').value = data.contact.phone || '';

                // Set gender radio
                if (data.contact.gender) {
                    document.getElementById(`gender${data.contact.gender.charAt(0).toUpperCase() + data.contact.gender.slice(1)}`).checked = true;
                }

                // Show current profile image if exists
                if (data.contact.profile_image_path) {
                    const imgElement = document.querySelector('#currentProfileImage img');
                    imgElement.src = `/storage/${data.contact.profile_image_path}`;
                    document.getElementById('currentProfileImage').classList.remove('d-none');
                }

                // Show current additional file if exists
                if (data.contact.additional_file_path) {
                    const linkElement = document.getElementById('additionalFileLink');
                    linkElement.href = `/storage/${data.contact.additional_file_path}`;
                    document.getElementById('currentAdditionalFile').classList.remove('d-none');
                }

                // Load custom fields with contact data
                await loadCustomFields(data.contact);

                contactModal.show();
            } else {
                showToast('Failed to load contact data.', 'error');
            }
        } catch (error) {
            console.error('Error editing contact:', error);
            showToast('An error occurred while loading contact data.', 'error');
        } finally {
            hideLoading();
        }
    }

    // Save contact (create or update)
    async function saveContact() {
        try {
            showLoading();

            // Clear previous validation errors
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });

            const isEdit = contactId.value !== '';
            const url = isEdit ? `/contacts/${contactId.value}` : '/contacts';
            const method = isEdit ? 'PUT' : 'POST';

            // Create FormData object
            const formData = new FormData(contactForm);

            // If editing, add _method field for Laravel to recognize as PUT
            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            const response = await fetch(url, {
                method: 'POST', // Always POST for FormData with files
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                contactModal.hide();
                showToast(isEdit ? 'Contact updated successfully.' : 'Contact created successfully.');
                fetchContacts(); // Refresh the contacts list
            } else {
                // Handle validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        const errorMsg = data.errors[key][0];
                        const inputField = document.getElementById(key);
                        const errorElement = document.getElementById(`${key}Error`);

                        if (inputField) {
                            inputField.classList.add('is-invalid');
                        }

                        if (errorElement) {
                            errorElement.textContent = errorMsg;
                        }

                        // Handle custom fields errors
                        if (key.startsWith('custom_fields.')) {
                            const fieldId = key.split('.')[1];
                            const customField = document.getElementById(`customField${fieldId}`);
                            const customFieldError = document.getElementById(`customField${fieldId}Error`);

                            if (customField) {
                                customField.classList.add('is-invalid');
                            }

                            if (customFieldError) {
                                customFieldError.textContent = errorMsg;
                            }
                        }
                    });
                } else {
                    showToast('Failed to save contact.', 'error');
                }
            }
        } catch (error) {
            console.error('Error saving contact:', error);
            showToast('An error occurred while saving the contact.', 'error');
        } finally {
            hideLoading();
        }
    }

    // Delete contact - Show confirmation modal
    function deleteContact(id) {
        contactToDeleteIdInput.value = id;
        deleteConfirmModal.show();
    }

    // Execute the actual deletion after modal confirmation
    async function executeDelete() {
        const id = contactToDeleteIdInput.value;
        if (!id) return;

        deleteConfirmModal.hide(); // Hide modal immediately

        try {
            showLoading();

            const response = await fetch(`/contacts/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast('Contact deleted successfully.');

                // Remove from selected contacts if present
                selectedContacts = selectedContacts.filter(contactId => contactId !== parseInt(id));
                updateMergeButtonState();

                fetchContacts(); // Refresh the contacts list
            } else {
                showToast(data.message || 'Failed to delete contact.', 'error');
            }
        } catch (error) {
            console.error('Error deleting contact:', error);
            showToast('An error occurred while deleting the contact.', 'error');
        } finally {
            hideLoading();
            contactToDeleteIdInput.value = ''; // Clear the ID
        }
    }

    // Prepare merge
    async function prepareMerge() {
        try {
            showLoading();

            const response = await fetch('/contacts/merge/prepare', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    contact_ids: selectedContacts
                })
            });

            const data = await response.json();

            if (data.success) {
                // Reset merge form
                document.querySelectorAll('input[name="masterContact"]').forEach(radio => {
                    radio.checked = false;
                });
                confirmMergeBtn.disabled = true;

                // Set contact data
                const contact1 = data.data.contact1;
                const contact2 = data.data.contact2;

                // Set radio values
                masterContact1.value = contact1.id;
                masterContact2.value = contact2.id;

                // Set contact names
                document.getElementById('contact1Name').textContent = contact1.name;
                document.getElementById('contact2Name').textContent = contact2.name;

                // Render contact details
                document.getElementById('contact1Details').innerHTML = renderContactDetails(contact1);
                document.getElementById('contact2Details').innerHTML = renderContactDetails(contact2);

                mergeModal.show();
            } else {
                showToast(data.message || 'Failed to prepare merge.', 'error');
            }
        } catch (error) {
            console.error('Error preparing merge:', error);
            showToast('An error occurred while preparing the merge.', 'error');
        } finally {
            hideLoading();
        }
    }

    // Render contact details for merge modal
    function renderContactDetails(contact) {
        let html = `
            <dl class="row">
                <dt class="col-sm-4">Email</dt>
                <dd class="col-sm-8">${contact.email || '-'}</dd>
                
                <dt class="col-sm-4">Phone</dt>
                <dd class="col-sm-8">${contact.phone || '-'}</dd>
                
                <dt class="col-sm-4">Gender</dt>
                <dd class="col-sm-8">${contact.gender ? (contact.gender.charAt(0).toUpperCase() + contact.gender.slice(1)) : '-'}</dd>
            </dl>
        `;

        // Add custom fields if any
        if (Object.keys(contact.custom_fields).length > 0) {
            html += `<h6 class="mt-3">Custom Fields</h6><dl class="row">`;

            Object.entries(contact.custom_fields).forEach(([key, field]) => {
                html += `
                    <dt class="col-sm-4">${field.label}</dt>
                    <dd class="col-sm-8">${field.value || '-'}</dd>
                `;
            });

            html += `</dl>`;
        }

        return html;
    }

    // Execute merge
    async function executeMerge() {
        const masterContactId = document.querySelector('input[name="masterContact"]:checked')?.value;

        if (!masterContactId) {
            showToast('Please select a master contact.', 'error');
            return;
        }

        try {
            showLoading();

            const secondaryContactId = selectedContacts.find(id => id != masterContactId);

            const response = await fetch('/contacts/merge/execute', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    master_contact_id: masterContactId,
                    secondary_contact_id: secondaryContactId
                })
            });

            const data = await response.json();

            if (data.success) {
                mergeModal.hide();
                showToast('Contacts merged successfully.');

                // Reset selected contacts
                selectedContacts = [];
                updateMergeButtonState();

                fetchContacts(); // Refresh the contacts list
            } else {
                showToast(data.message || 'Failed to merge contacts.', 'error');
            }
        } catch (error) {
            console.error('Error executing merge:', error);
            showToast('An error occurred while merging the contacts.', 'error');
        } finally {
            hideLoading();
        }
    }
</script>
@endsection


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this contact? This action cannot be undone.
                <input type="hidden" id="contactToDeleteId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Contact</button>
            </div>
        </div>
    </div>
</div>