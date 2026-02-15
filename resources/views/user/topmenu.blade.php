<div class="flex items-center justify-between h-16 px-4 sm:px-6">
    <!-- Sidebar Toggle -->
    <button @click="sidebarOpen = !sidebarOpen" class="p-2 text-gray-400 rounded-md lg:hidden hover:bg-dark-100 hover:text-white focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary-500">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Search (Optional) -->
    <div class="hidden lg:block">
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                <i class="fas fa-search text-gray-500"></i>
            </span>
            <input type="text" class="w-full py-2 pl-10 pr-4 text-sm text-white bg-dark-100 border border-transparent rounded-md focus:outline-none focus:bg-dark-200 focus:border-primary-500" placeholder="Search...">
        </div>
    </div>

    <div class="flex items-center space-x-4">
        <!-- KYC Status -->
        @if ($settings->enable_kyc == 'yes')
            <div x-data="{ open: false }" class="relative">
                @if (Auth::user()->account_verify == 'Verified')
                    <div class="flex items-center px-3 py-1.5 text-xs font-medium text-green-400 bg-green-500/10 rounded-full">
                        <i class="mr-1 fas fa-check-circle"></i>
                        Verified
                    </div>
                @else
                    <button @click="open = !open" class="flex items-center px-3 py-1.5 text-xs font-medium text-yellow-400 bg-yellow-500/10 rounded-full">
                        <i class="mr-1 fas fa-exclamation-triangle"></i>
                        Unverified
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-64 p-4 bg-dark-100 rounded-lg shadow-lg" x-cloak>
                        <h6 class="mb-2 text-sm font-semibold text-white">KYC Verification</h6>
                        @if (Auth::user()->account_verify == 'Under review')
                            <p class="text-xs text-gray-400">Your submission is under review.</p>
                        @else
                            <p class="mb-3 text-xs text-gray-400">Please verify your account to access all features.</p>
                            <a href="{{ route('account.verify') }}" class="block w-full px-4 py-2 text-xs font-medium text-center text-white bg-primary-600 rounded-md hover:bg-primary-700">Verify Now</a>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <!-- Notifications -->
        <div x-data="{
            open: false,
            unreadCount: 0,
            notifications: [],
            loading: false,
            loadNotifications() {
                this.loading = true;
                fetch('{{ url('dashboard/notifications/dropdown') }}')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        this.notifications = data.notifications || [];
                        this.unreadCount = data.unread_count || 0;
                        this.loading = false;
                    })
                    .catch(error => {
                        console.error('Error loading notifications:', error);
                        this.notifications = [];
                        this.unreadCount = 0;
                        this.loading = false;
                    });
            },
            markAsRead(notificationId) {
                fetch(`{{ url('dashboard/notifications') }}/${notificationId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                        this.unreadCount = data.unread_count;
                        this.loadNotifications();
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
            },
            markAllAsRead() {
                fetch('{{ url('dashboard/notifications/mark-all-read') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                        this.unreadCount = 0;
                        this.loadNotifications();
                    }
                })
                .catch(error => {
                    console.error('Error marking all notifications as read:', error);
                });
            }
        }"
        x-init="loadNotifications(); setInterval(() => loadNotifications(), 30000);"
        class="relative">
            <button @click="open = !open; if(open) loadNotifications();" class="relative p-2 text-gray-400 rounded-full hover:bg-dark-100 hover:text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <i class="fas fa-bell text-lg"></i>
                <span x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"
                      class="absolute -top-1 -right-1 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full min-w-[18px] h-4"></span>
            </button>

            <div x-show="open" @click.away="open = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-80 bg-dark-100 rounded-xl shadow-lg border border-dark-200 z-50" x-cloak>

                <!-- Header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-dark-200">
                    <h3 class="text-sm font-semibold text-white">Notifications</h3>
                    <div class="flex items-center space-x-2">
                        <span x-show="unreadCount > 0" x-text="`${unreadCount} unread`" class="text-xs text-gray-400"></span>
                        <button @click="markAllAsRead()" x-show="unreadCount > 0" class="text-xs text-primary-400 hover:text-primary-300">Mark all read</button>
                    </div>
                </div>

                <!-- Loading State -->
                <div x-show="loading" class="flex items-center justify-center py-8">
                    <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary-500"></div>
                </div>

                <!-- Notifications List -->
                <div x-show="!loading" class="max-h-96 overflow-y-auto">
                    <template x-if="notifications.length === 0">
                        <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                            <i class="fas fa-bell-slash text-2xl mb-2"></i>
                            <p class="text-sm">No notifications</p>
                        </div>
                    </template>

                    <template x-for="notification in notifications" :key="notification.id">
                        <div class="px-4 py-3 border-b border-dark-200 last:border-b-0 hover:bg-dark-200/50 transition-colors duration-200"
                             :class="!notification.read_at ? 'bg-primary-500/5 border-l-2 border-l-primary-500' : ''">
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                         :class="notification.priority === 'urgent' ? 'bg-red-500/20 text-red-400' :
                                                notification.priority === 'high' ? 'bg-orange-500/20 text-orange-400' :
                                                'bg-primary-500/20 text-primary-400'">
                                        <i :class="notification.icon || 'fas fa-bell'" class="text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white" x-text="notification.title"></p>
                                    <p class="text-xs text-gray-400 mt-1" x-text="notification.message"></p>
                                    <p class="text-xs text-gray-500 mt-1" x-text="notification.time_ago || new Date(notification.created_at).toLocaleDateString()"></p>
                                </div>
                                <div class="flex-shrink-0 flex items-center space-x-2">
                                    <button x-show="!notification.read_at" @click="markAsRead(notification.id)"
                                            class="text-xs text-primary-400 hover:text-primary-300">Mark read</button>
                                    <span x-show="!notification.read_at" class="w-2 h-2 bg-primary-500 rounded-full"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 border-t border-dark-200">
                    <a href="{{ route('user.notifications') }}" class="block text-center text-sm text-primary-400 hover:text-primary-300">View all notifications</a>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="flex items-center text-sm rounded-full focus:outline-none">
                <i class="text-2xl text-gray-400 fas fa-user-circle"></i>
            </button>
            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-dark-100 rounded-md shadow-lg" x-cloak>
                <div class="px-4 py-3 border-b border-dark-200">
                    <p class="text-sm font-semibold text-white">Hi, {{ Auth::user()->name }}</p>
                </div>
                <div class="py-1">
                    <a href="{{ route('profile') }}" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-dark-200">
                        <i class="mr-3 fas fa-user"></i> My Profile
                    </a>
                    <a href="{{ route('logout') }}"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                        class="flex items-center px-4 py-2 text-sm text-red-400 hover:bg-dark-200">
                        <i class="mr-3 fas fa-sign-out-alt"></i> Logout
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                        {{ csrf_field() }}
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
