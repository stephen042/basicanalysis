<form method="POST" action="javascript:void(0)" id="updateprofileform" class="space-y-6">
    @csrf
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
            <label for="name" class="block mb-2 text-sm font-medium text-gray-300">Full Name</label>
            <input type="text" id="name" name="name" value="{{ Auth::user()->name }}"
                class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none">
        </div>
        <div>
            <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Email Address</label>
            <input type="email" id="email" name="email" value="{{ Auth::user()->email }}" readonly
                class="w-full px-4 py-3 text-gray-400 border-transparent rounded-lg bg-dark-100 cursor-not-allowed focus:outline-none">
        </div>
        <div>
            <label for="phone" class="block mb-2 text-sm font-medium text-gray-300">Phone Number</label>
            <input type="text" id="phone" name="phone" value="{{ Auth::user()->phone }}"
                class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none">
        </div>
        <div>
            <label for="dob" class="block mb-2 text-sm font-medium text-gray-300">Date of Birth</label>
            <input type="date" id="dob" name="dob" value="{{ Auth::user()->dob }}"
                class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none">
        </div>
        <div>
            <label for="country" class="block mb-2 text-sm font-medium text-gray-300">Country</label>
            <input type="text" id="country" name="country" value="{{ Auth::user()->country }}" readonly
                class="w-full px-4 py-3 text-gray-400 border-transparent rounded-lg bg-dark-100 cursor-not-allowed focus:outline-none">
        </div>
        <div>
            <label for="address" class="block mb-2 text-sm font-medium text-gray-300">Address</label>
            <textarea id="address" name="address" rows="3"
                class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                placeholder="Your full address">{{ Auth::user()->address }}</textarea>
        </div>
    </div>
    <div class="flex justify-end">
        <button type="submit"
            class="px-6 py-2.5 font-semibold text-center text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
            Update Profile
        </button>
    </div>
</form>

<script>
document.getElementById('updateprofileform').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.textContent;

    // Disable button and show loading state
    submitButton.disabled = true;
    submitButton.textContent = 'Updating...';

    // Send AJAX request
    fetch('{{ route("profile.update") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message || 'Profile updated successfully!',
                    background: 'rgb(35, 38, 39)',
                    color: 'rgb(254, 254, 254)',
                    confirmButtonColor: 'rgb(154, 217, 83)',
                    timer: 3000,
                    timerProgressBar: true
                });
            } else {
                alert('Profile updated successfully!');
            }
        } else {
            throw new Error(data.message || 'Update failed');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: error.message || 'Failed to update profile. Please try again.',
                background: 'rgb(35, 38, 39)',
                color: 'rgb(254, 254, 254)',
                confirmButtonColor: 'rgb(154, 217, 83)'
            });
        } else {
            alert('Error: ' + (error.message || 'Failed to update profile. Please try again.'));
        }
    })
    .finally(() => {
        // Re-enable button
        submitButton.disabled = false;
        submitButton.textContent = originalText;
    });
});
</script>
