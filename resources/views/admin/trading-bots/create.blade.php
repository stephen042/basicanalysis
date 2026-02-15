<?php
if (Auth('admin')->User()->dashboard_style == 'light') {
    $text = 'dark';
} else {
    $text = 'light';
}
?>
@extends('layouts.app')
@section('content')
    @include('admin.topmenu')
    @include('admin.sidebar')
    <div class="main-panel">
        <div class="content ">
            <div class="page-inner">
                <div class="mt-2 mb-4">
                    <h1 class="title1 ">{{ $title }}</h1>
                </div>
                <x-danger-alert />
                <x-success-alert />

                <!-- Validation Errors Alert -->
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5><i class="fa fa-exclamation-triangle"></i> Please fix the following errors:</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <!-- Session Status Messages -->
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa fa-times-circle"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fa fa-exclamation-triangle"></i> {{ session('warning') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fa fa-info-circle"></i> {{ session('info') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="mb-5 row">
                    <div class="col-md-8 card p-4 shadow">
                        <form action="{{ route('admin.trading-bots.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="name">Bot Name</label>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" required>
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="3" required>{{ old('description') }}</textarea>
                                @error('description')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="min_amount">Minimum Amount ($)</label>
                                        <input type="number" name="min_amount" id="min_amount" class="form-control" step="0.01" min="1" value="{{ old('min_amount') }}" required>
                                        @error('min_amount')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_amount">Maximum Amount ($)</label>
                                        <input type="number" name="max_amount" id="max_amount" class="form-control" step="0.01" min="1" value="{{ old('max_amount') }}" required>
                                        @error('max_amount')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="duration">Duration (Hours)</label>
                                        <input type="number" name="duration" id="duration" class="form-control" min="1" value="{{ old('duration') }}" required>
                                        @error('duration')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="profit_rate">Profit Rate (%)</label>
                                        <input type="number" name="profit_rate" id="profit_rate" class="form-control" step="0.01" min="0.01" max="100" value="{{ old('profit_rate') }}" required>
                                        @error('profit_rate')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- Trading Assets Selection -->
                            <div class="form-group">
                                <label>Trading Assets</label>
                                <small class="text-muted">Select assets this bot will trade and set allocation percentages</small>

                                <div class="row mt-3" id="assets-container">
                                    @php $allocationIndex = 0; @endphp
                                    @foreach($tradingAssets->groupBy('type') as $type => $assets)
                                        <div class="col-12 mb-3">
                                            <h6 class="text-primary">{{ ucfirst($type) }}</h6>
                                            @foreach($assets as $asset)
                                                <div class="form-check mb-2 p-2 border rounded {{ in_array($asset->id, old('assets', [])) ? 'border-primary bg-light' : 'border-secondary' }}" data-asset-id="{{ $asset->id }}" style="cursor: pointer;">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center flex-grow-1" onclick="toggleAssetCheckbox({{ $asset->id }})">
                                                            <input class="form-check-input asset-checkbox mr-2" type="checkbox" name="assets[]" value="{{ $asset->id }}" id="asset_{{ $asset->id }}" {{ in_array($asset->id, old('assets', [])) ? 'checked' : '' }} onclick="event.stopPropagation();">
                                                            <label class="form-check-label d-flex align-items-center mb-0" for="asset_{{ $asset->id }}" style="cursor: pointer;">
                                                                <img src="{{ $asset->icon_url }}" alt="{{ $asset->symbol }}" style="width: 20px; height: 20px; margin-right: 8px;" onerror="this.src='{{ asset('dash/default-crypto.png') }}'">
                                                                <span>
                                                                    <strong>{{ $asset->name }}</strong> ({{ $asset->symbol }})
                                                                    @if($asset->current_price)
                                                                        <br><small class="text-muted">${{ $asset->formatted_price }}</small>
                                                                    @endif
                                                                </span>
                                                            </label>
                                                        </div>
                                                        <div class="allocation-container" style="display: {{ in_array($asset->id, old('assets', [])) ? 'block' : 'none' }};">
                                                            <div class="input-group input-group-sm" style="width: 100px;">
                                                                <input type="number" name="allocations[{{ $asset->id }}]" class="form-control allocation-input" placeholder="%" min="1" max="100" step="0.01" value="{{ old('allocations.' . $asset->id) }}" onclick="event.stopPropagation();" {{ in_array($asset->id, old('assets', [])) ? '' : 'disabled' }}>
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">%</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @php $allocationIndex++; @endphp
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                                @error('assets')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                @error('allocations')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                                @error('allocations.*')
                                    <small class="text-danger">Please check allocation percentages.</small>
                                @enderror
                            </div>

                            <script>
                                // Global function to toggle checkbox from clicking on container
                                function toggleAssetCheckbox(assetId) {
                                    console.log('Container clicked for asset:', assetId);
                                    const checkbox = document.getElementById('asset_' + assetId);
                                    if (checkbox) {
                                        checkbox.checked = !checkbox.checked;
                                        console.log('Checkbox toggled:', checkbox.checked);
                                        // Trigger the change event
                                        checkbox.dispatchEvent(new Event('change'));
                                    }
                                }

                                document.addEventListener('DOMContentLoaded', function() {
                                    console.log('DOM loaded, initializing asset selection...');
                                    const checkboxes = document.querySelectorAll('.asset-checkbox');
                                    console.log('Found checkboxes:', checkboxes.length);

                                    // Function to toggle allocation input
                                    function toggleAllocationInput(checkbox) {
                                        console.log('Toggling checkbox:', checkbox.value, 'checked:', checkbox.checked);
                                        const assetContainer = checkbox.closest('[data-asset-id]');
                                        const allocationContainer = assetContainer.querySelector('.allocation-container');
                                        const allocationInput = allocationContainer.querySelector('.allocation-input');

                                        if (checkbox.checked) {
                                            assetContainer.classList.add('border-primary', 'bg-light');
                                            assetContainer.classList.remove('border-secondary');
                                            allocationContainer.style.display = 'block';
                                            allocationInput.required = true;
                                            if (!allocationInput.value) {
                                                allocationInput.value = '10'; // Default allocation
                                            }
                                            // Enable the input so it gets submitted
                                            allocationInput.disabled = false;
                                        } else {
                                            assetContainer.classList.remove('border-primary', 'bg-light');
                                            assetContainer.classList.add('border-secondary');
                                            allocationContainer.style.display = 'none';
                                            allocationInput.required = false;
                                            allocationInput.value = '';
                                            // Disable the input so it doesn't get submitted
                                            allocationInput.disabled = true;
                                        }

                                        updateAllocationTotal();
                                    }

                                    // Function to update allocation total display
                                    function updateAllocationTotal() {
                                        let totalAllocation = 0;
                                        const checkedBoxes = document.querySelectorAll('.asset-checkbox:checked');

                                        checkedBoxes.forEach(function(checkbox) {
                                            const allocationInput = checkbox.closest('[data-asset-id]').querySelector('.allocation-input');
                                            if (allocationInput && allocationInput.value && !allocationInput.disabled) {
                                                totalAllocation += parseFloat(allocationInput.value) || 0;
                                            }
                                        });

                                        // Update or create total display
                                        let totalDisplay = document.getElementById('allocation-total');
                                        if (!totalDisplay) {
                                            totalDisplay = document.createElement('div');
                                            totalDisplay.id = 'allocation-total';
                                            totalDisplay.className = 'mt-2 p-2 rounded text-center';
                                            document.getElementById('assets-container').appendChild(totalDisplay);
                                        }

                                        totalDisplay.innerHTML = '<strong>Total Allocation: ' + totalAllocation.toFixed(2) + '%</strong>';

                                        if (totalAllocation > 100) {
                                            totalDisplay.className = 'mt-2 p-2 rounded text-center bg-danger text-white';
                                        } else if (totalAllocation === 100) {
                                            totalDisplay.className = 'mt-2 p-2 rounded text-center bg-success text-white';
                                        } else if (totalAllocation > 0) {
                                            totalDisplay.className = 'mt-2 p-2 rounded text-center bg-info text-white';
                                        } else {
                                            totalDisplay.className = 'mt-2 p-2 rounded text-center bg-secondary text-white';
                                        }
                                    }

                                    // Initialize form state (preserve state on validation errors)
                                    checkboxes.forEach(function(checkbox, index) {
                                        console.log('Initializing checkbox', index, 'checked:', checkbox.checked);
                                        // Initialize state based on checkbox
                                        toggleAllocationInput(checkbox);

                                        // Add event listener for changes
                                        checkbox.addEventListener('change', function() {
                                            console.log('Checkbox changed:', this.value, 'checked:', this.checked);
                                            toggleAllocationInput(this);
                                        });
                                    });

                                    // Add event listeners to allocation inputs for real-time total update
                                    document.addEventListener('input', function(e) {
                                        if (e.target.classList.contains('allocation-input')) {
                                            updateAllocationTotal();
                                        }
                                    });

                                    // Enhanced validation helper with better error notifications
                                    const form = document.querySelector('form');
                                    if (form) {
                                        form.addEventListener('submit', function(e) {
                                            // Clear any existing error alerts
                                            const existingErrors = document.querySelectorAll('.js-validation-error');
                                            existingErrors.forEach(error => error.remove());

                                            const checkedBoxes = document.querySelectorAll('.asset-checkbox:checked');

                                            if (checkedBoxes.length === 0) {
                                                e.preventDefault();
                                                showValidationError('Please select at least one trading asset before creating the bot.', 'warning');
                                                return false;
                                            }

                                            // Validate total allocation
                                            let totalAllocation = 0;
                                            let hasEmptyAllocations = false;

                                            checkedBoxes.forEach(function(checkbox) {
                                                const allocationInput = checkbox.closest('[data-asset-id]').querySelector('.allocation-input');
                                                if (allocationInput && !allocationInput.disabled) {
                                                    if (!allocationInput.value || allocationInput.value === '') {
                                                        hasEmptyAllocations = true;
                                                    } else {
                                                        totalAllocation += parseFloat(allocationInput.value) || 0;
                                                    }
                                                }
                                            });

                                            if (hasEmptyAllocations) {
                                                e.preventDefault();
                                                showValidationError('Please set allocation percentages for all selected assets.', 'warning');
                                                return false;
                                            }

                                            if (totalAllocation > 100) {
                                                e.preventDefault();
                                                showValidationError('Total allocation cannot exceed 100%. Current total: ' + totalAllocation.toFixed(2) + '%', 'danger');
                                                return false;
                                            }

                                            if (totalAllocation === 0) {
                                                e.preventDefault();
                                                showValidationError('Please set allocation percentages for selected assets.', 'warning');
                                                return false;
                                            }

                                            // Show success message for valid submission
                                            if (totalAllocation === 100) {
                                                showValidationError('Perfect! All allocations add up to 100%. Submitting...', 'success');
                                            }
                                        });
                                    }

                                    // Function to show validation errors with Bootstrap styling
                                    function showValidationError(message, type = 'danger') {
                                        const alertDiv = document.createElement('div');
                                        alertDiv.className = `alert alert-${type} alert-dismissible fade show js-validation-error`;
                                        alertDiv.innerHTML = `
                                            <i class="fa fa-${type === 'danger' ? 'times-circle' : type === 'warning' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                                            ${message}
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        `;

                                        // Insert at the top of the form
                                        const form = document.querySelector('form');
                                        form.insertBefore(alertDiv, form.firstChild);

                                        // Auto dismiss success messages after 3 seconds
                                        if (type === 'success') {
                                            setTimeout(() => {
                                                if (alertDiv.parentNode) {
                                                    alertDiv.remove();
                                                }
                                            }, 3000);
                                        }

                                        // Scroll to top to show the error
                                        alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }

                                    // Initial total calculation
                                    updateAllocationTotal();
                                });
                            </script>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-save"></i> Create Trading Bot
                                </button>
                                <a href="{{ route('admin.trading-bots.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <div class="card p-3">
                            <h5>Trading Bot Guidelines</h5>
                            <ul class="list-unstyled">
                                <li><i class="fa fa-check text-success"></i> Set realistic profit rates</li>
                                <li><i class="fa fa-check text-success"></i> Define clear min/max amounts</li>
                                <li><i class="fa fa-check text-success"></i> Provide detailed descriptions</li>
                                <li><i class="fa fa-check text-success"></i> Monitor bot performance</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
