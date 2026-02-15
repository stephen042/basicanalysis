<form method="POST" action="{{ route('updateuserpass') }}" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="current_password" class="block mb-2 text-sm font-medium text-gray-300">Old Password</label>
            <input type="password" id="current_password" name="current_password" required
                class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                placeholder="Enter your old password">
        </div>
    </div>
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="password" class="block mb-2 text-sm font-medium text-gray-300">New Password</label>
            <input type="password" id="password" name="password" required
                class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                placeholder="Enter a new password">
        </div>
        <div>
            <label for="password_confirmation" class="block mb-2 text-sm font-medium text-gray-300">Confirm New Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                placeholder="Confirm your new password">
        </div>
    </div>

    <div class="flex items-center justify-between">
        <a href="{{ route('twofa') }}" class="text-sm font-medium transition-colors duration-200 text-primary-500 hover:text-primary-400">
            Advanced Security Settings <i class="fas fa-arrow-right ml-1"></i>
        </a>
        <button type="submit"
            class="px-6 py-2.5 font-semibold text-center text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
            Update Password
        </button>
    </div>
</form>
