@extends('layouts.dash')

@section('title', 'Notifications')

@section('content')
<!-- Cache Buster v2.0 -->
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-white">Notifications</h1>
                    <p class="mt-1 text-gray-400">Stay updated with your trading activities and account status</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-400">{{ $unreadCount }} unread</span>
                    @if($unreadCount > 0)
                        <button onclick="markAllAsRead()" class="px-4 py-2 text-sm font-medium text-primary-400 bg-primary-500/10 rounded-lg hover:bg-primary-500/20 transition-colors duration-200">
                            Mark all as read
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6">
            <div class="flex flex-wrap gap-3">
                <button onclick="filterNotifications('all')" class="filter-btn active px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200">
                    All
                </button>
                <button onclick="filterNotifications('unread')" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200">
                    Unread
                </button>
                <button onclick="filterNotifications('bot_trade')" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200">
                    Bot Trades
                </button>
                <button onclick="filterNotifications('profit_return')" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200">
                    Profits
                </button>
                <button onclick="filterNotifications('system')" class="filter-btn px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200">
                    System
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div id="notifications-container" class="space-y-4">
            @forelse($notifications as $notification)
                <div class="notification-item bg-dark-200 rounded-xl border border-dark-100 hover:border-primary-500/30 transition-all duration-200 {{ $notification->isUnread() ? 'border-l-4 border-l-primary-500 bg-primary-500/5' : '' }}"
                     data-type="{{ $notification->type }}"
                     data-read="{{ $notification->isRead() ? 'true' : 'false' }}">
                    <div class="p-6">
                        <div class="flex items-start space-x-4">
                            <!-- Icon -->
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 rounded-full flex items-center justify-center {{ $notification->getPriorityColor() }}"
                                     style="background: {{ $notification->priority === 'urgent' ? 'rgba(239, 68, 68, 0.1)' : ($notification->priority === 'high' ? 'rgba(245, 158, 11, 0.1)' : 'rgba(59, 130, 246, 0.1)') }}">
                                    <i class="{{ $notification->getIconClass() }} text-lg"></i>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-white mb-1">{{ $notification->title }}</h3>
                                        <p class="text-gray-400 mb-2">{{ $notification->message }}</p>

                                        <!-- Additional Data -->
                                        @if($notification->data)
                                            <div class="flex flex-wrap gap-2 mb-3">
                                                @if($notification->getData('bot_name'))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-500/10 text-primary-400">
                                                        <i class="fas fa-robot mr-1"></i>
                                                        {{ $notification->getData('bot_name') }}
                                                    </span>
                                                @endif
                                                @if($notification->getData('amount'))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400">
                                                        ${{ number_format($notification->getData('amount'), 2) }}
                                                    </span>
                                                @endif
                                                @if($notification->getData('asset'))
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-500/10 text-blue-400">
                                                        {{ $notification->getData('asset') }}
                                                    </span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="flex items-center text-sm text-gray-500 space-x-4">
                                            <span>{{ $notification->getTimeAgo() }}</span>
                                            <span class="text-xs px-2 py-1 rounded-full {{ $notification->priority === 'urgent' ? 'bg-red-500/10 text-red-400' : ($notification->priority === 'high' ? 'bg-orange-500/10 text-orange-400' : 'bg-gray-500/10 text-gray-400') }}">
                                                {{ ucfirst($notification->priority) }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center space-x-2">
                                        @if($notification->action_url)
                                            <a href="{{ $notification->action_url }}"
                                               class="text-primary-400 hover:text-primary-300 text-sm font-medium">
                                                View
                                            </a>
                                        @endif

                                        @if($notification->isUnread())
                                            <button onclick="markAsRead({{ $notification->id }})"
                                                    class="text-gray-400 hover:text-white text-sm">
                                                Mark read
                                            </button>
                                        @endif

                                        <button onclick="deleteNotificationV2({{ $notification->id }})"
                                                class="text-red-400 hover:text-red-300 text-sm">
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Unread Indicator -->
                            @if($notification->isUnread())
                                <div class="flex-shrink-0">
                                    <div class="w-3 h-3 bg-primary-500 rounded-full"></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <i class="fas fa-bell-slash text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-white mb-2">No notifications yet</h3>
                    <p class="text-gray-400">You'll see notifications about your trading activities here</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="mt-8">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let currentFilter = 'all';

    function filterNotifications(type) {
        currentFilter = type;

        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active', 'bg-primary-500', 'text-white');
            btn.classList.add('text-gray-400', 'bg-dark-100');
        });

        event.target.classList.remove('text-gray-400', 'bg-dark-100');
        event.target.classList.add('active', 'bg-primary-500', 'text-white');

        // Filter notifications
        document.querySelectorAll('.notification-item').forEach(item => {
            const itemType = item.dataset.type;
            const isRead = item.dataset.read === 'true';

            let show = false;

            if (type === 'all') {
                show = true;
            } else if (type === 'unread') {
                show = !isRead;
            } else {
                show = itemType === type;
            }

            item.style.display = show ? 'block' : 'none';
        });
    }

    function markAsRead(notificationId) {
        fetch('{{ url("dashboard/notifications") }}/' + notificationId + '/mark-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to mark notification as read. Please try again.');
        });
    }

    function markAllAsRead() {
        fetch('{{ url("dashboard/notifications/mark-all-read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to mark all notifications as read. Please try again.');
        });
    }

    function deleteNotificationV2(notificationId) {
        if (confirm('Are you sure you want to delete this notification?')) {
            // Use POST alias endpoint to avoid DELETE 405 issues
            const deleteUrl = '{{ url("dashboard/notifications") }}/' + notificationId + '/delete';
            console.log('V2 Deleting (POST alias) notification ID:', notificationId, 'URL:', deleteUrl);

            fetch(deleteUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                console.log('POST alias delete response status:', response.status);
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                console.log('POST alias delete response data:', data);
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to delete notification.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete notification. Please try again.');
            });
        }
    }

    // Initialize active filter button
    document.addEventListener('DOMContentLoaded', function() {
        const activeBtn = document.querySelector('.filter-btn.active');
        if (activeBtn) {
            activeBtn.classList.add('bg-primary-500', 'text-white');
        }
        document.querySelectorAll('.filter-btn:not(.active)').forEach(btn => {
            btn.classList.add('text-gray-400', 'bg-dark-100');
        });
    });
</script>

<style>
    .filter-btn {
        transition: all 0.2s ease;
    }

    .filter-btn:hover {
        transform: translateY(-1px);
    }

    .notification-item {
        transition: all 0.2s ease;
    }

    .notification-item:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
    }
</style>
@endsection
