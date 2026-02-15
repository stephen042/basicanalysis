            <!-- Floating Tab Bar Navigation -->
            <div class="fixed bottom-2 left-4 right-4 z-40">
                <div class="bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl rounded-3xl shadow-2xl border border-gray-200/50 dark:border-gray-700/50 p-2">
                    <div class="flex items-center justify-between">
                        @php
                            $mobileMenuItems = [
                                ['route' => 'accounthistory', 'icon' => 'fa-solid fa-chart-line', 'label' => 'Activity'],
                            
                                ['route' => 'dashboard', 'icon' => 'fa-solid fa-house', 'label' => 'Home'],
                               
                                ['route' => 'profile', 'icon' => 'fa-solid fa-user', 'label' => 'Profile'],
                            ];
                        @endphp
                        @foreach($mobileMenuItems as $index => $item)
                            @php
                                $isActive = request()->routeIs($item['route']);
                            @endphp
                            <a href="{{ route($item['route']) }}" 
                               class="relative flex-1 flex flex-col items-center justify-center py-3 px-2 rounded-2xl transition-all duration-300 group">
                                
                                @if($isActive)
                                    <!-- Active State -->
                                    <div class="absolute inset-0 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl shadow-lg"></div>
                                    <div class="relative z-10 flex flex-col items-center">
                                        <div class="w-8 h-8 bg-white/20 dark:bg-white/30 rounded-xl flex items-center justify-center mb-1">
                                            <i class="{{ $item['icon'] }} text-white text-lg"></i>
                                </div>
                                        <span class="text-xs font-semibold text-white">{{ $item['label'] }}</span>
                            </div>
                                @else
                                    <!-- Inactive State -->
                                    <div class="relative z-10 flex flex-col items-center group-hover:scale-110 transition-transform duration-200">
                                        <div class="w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center mb-1 group-hover:bg-primary-100 dark:group-hover:bg-gray-700 transition-colors">
                                            <i class="{{ $item['icon'] }} text-gray-500 dark:text-gray-400 text-lg group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors"></i>
                                </div>
                                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">{{ $item['label'] }}</span>
                            </div>
                                @endif
                            </a>
                        @endforeach
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        @endif