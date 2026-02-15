<div x-data="{
    otpsend: '{{ Auth::user()->sendotpemail ?? 'No' }}',
    roiemail: '{{ Auth::user()->sendroiemail ?? 'No' }}',
    invplanemail: '{{ Auth::user()->invplanemail ?? 'No' }}'
}">
    <form method="POST" action="{{ route('updateemail') }}" id="updateemailpref" class="space-y-8">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- OTP on Withdrawal -->
            <div class="flex items-center justify-between p-4 rounded-lg bg-dark-300">
                <div>
                    <h4 class="font-semibold text-white">OTP on Withdrawal</h4>
                    <p class="text-sm text-gray-400">Send a confirmation OTP to my email when withdrawing funds.</p>
                </div>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="otpsend" value="Yes" class="hidden" x-model="otpsend">
                        <span class="px-4 py-2 text-sm font-medium rounded-md"
                            :class="{ 'bg-primary-600 text-white': otpsend === 'Yes', 'bg-dark-100 text-gray-300': otpsend !== 'Yes' }">Yes</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="otpsend" value="No" class="hidden" x-model="otpsend">
                        <span class="px-4 py-2 text-sm font-medium rounded-md"
                            :class="{ 'bg-red-600 text-white': otpsend === 'No', 'bg-dark-100 text-gray-300': otpsend !== 'No' }">No</span>
                    </label>
                </div>
            </div>

            <!-- Profit Notifications -->
            <div class="flex items-center justify-between p-4 rounded-lg bg-dark-300">
                <div>
                    <h4 class="font-semibold text-white">Profit Notifications</h4>
                    <p class="text-sm text-gray-400">Send me an email when I receive profit from an investment.</p>
                </div>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="roiemail" value="Yes" class="hidden" x-model="roiemail">
                        <span class="px-4 py-2 text-sm font-medium rounded-md"
                            :class="{ 'bg-primary-600 text-white': roiemail === 'Yes', 'bg-dark-100 text-gray-300': roiemail !== 'Yes' }">Yes</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="roiemail" value="No" class="hidden" x-model="roiemail">
                        <span class="px-4 py-2 text-sm font-medium rounded-md"
                            :class="{ 'bg-red-600 text-white': roiemail === 'No', 'bg-dark-100 text-gray-300': roiemail !== 'No' }">No</span>
                    </label>
                </div>
            </div>

            <!-- Plan Expiry Notifications -->
            <div class="flex items-center justify-between p-4 rounded-lg bg-dark-300">
                <div>
                    <h4 class="font-semibold text-white">Plan Expiry Notifications</h4>
                    <p class="text-sm text-gray-400">Send me an email when my investment plan expires.</p>
                </div>
                <div class="flex items-center space-x-4">
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="invplanemail" value="Yes" class="hidden" x-model="invplanemail">
                        <span class="px-4 py-2 text-sm font-medium rounded-md"
                            :class="{ 'bg-primary-600 text-white': invplanemail === 'Yes', 'bg-dark-100 text-gray-300': invplanemail !== 'Yes' }">Yes</span>
                    </label>
                    <label class="flex items-center cursor-pointer">
                        <input type="radio" name="invplanemail" value="No" class="hidden" x-model="invplanemail">
                        <span class="px-4 py-2 text-sm font-medium rounded-md"
                            :class="{ 'bg-red-600 text-white': invplanemail === 'No', 'bg-dark-100 text-gray-300': invplanemail !== 'No' }">No</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="flex justify-end mt-8">
            <button type="button" id="securitySubmitBtn"
                class="px-6 py-2.5 font-semibold text-center text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <span id="securityBtnText">Save Preferences</span>
                <i id="securityBtnSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
            </button>
        </div>
    </form>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('updateemailpref');
    const submitBtn = document.getElementById('securitySubmitBtn');
    const btnText = document.getElementById('securityBtnText');
    const btnSpinner = document.getElementById('securityBtnSpinner');

    function showLoading() {
        submitBtn.disabled = true;
        btnText.textContent = 'Saving...';
        btnSpinner.classList.remove('hidden');
    }

    function hideLoading() {
        submitBtn.disabled = false;
        btnText.textContent = 'Save Preferences';
        btnSpinner.classList.add('hidden');
    }

    function submitForm() {
        showLoading();

        // Get Alpine.js data
        const alpineComponent = form.closest('[x-data]');
        if (!alpineComponent || !alpineComponent._x_dataStack) {
            console.error('Alpine.js data not found');
            hideLoading();
            return;
        }

        const alpineData = alpineComponent._x_dataStack[0];

        // Prepare form data
        const formData = new FormData();
        formData.append('_token', form.querySelector('[name=_token]').value);
        formData.append('_method', form.querySelector('[name=_method]').value);
        formData.append('otpsend', alpineData.otpsend);
        formData.append('roiemail', alpineData.roiemail);
        formData.append('invplanemail', alpineData.invplanemail);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(text);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Success response:', data);

            if (data.status === 200) {
                // Show success message using SweetAlert2
                Swal.fire({
                    title: 'Success!',
                    text: data.success || 'Email preferences updated successfully!',
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end',
                    timerProgressBar: true,
                    background: '#10b981',
                    color: '#ffffff'
                });
            }
        })
        .catch(error => {
            console.log('Error:', error.message);

            let errorMessage = 'An error occurred while saving your preferences.';

            try {
                const errorData = JSON.parse(error.message);
                if (errorData.message) {
                    errorMessage = errorData.message;
                } else if (errorData.errors) {
                    const errors = Object.values(errorData.errors).flat();
                    errorMessage = errors.join('<br>');
                }
            } catch (e) {
                errorMessage = error.message || errorMessage;
            }

            // Show error message using SweetAlert2
            Swal.fire({
                title: 'Error!',
                html: errorMessage,
                icon: 'error',
                confirmButtonText: 'OK',
                confirmButtonColor: '#dc2626'
            });
        })
        .finally(() => {
            hideLoading();
        });
    }

    // Handle button click
    submitBtn.addEventListener('click', function(e) {
        e.preventDefault();
        submitForm();
    });

    // Handle form submission (backup)
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        submitForm();
    });
});
</script>
