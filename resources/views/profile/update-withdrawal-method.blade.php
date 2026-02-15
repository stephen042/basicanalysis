<form method="post" action="{{ route('updateacount') }}" id="updatewithdrawalinfo" class="space-y-8">
    @csrf
    @method('PUT')

    <fieldset>
        <h3 class="mb-4 text-lg font-semibold text-white">Bank Details</h3>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="bank_name" class="block mb-2 text-sm font-medium text-gray-300">Bank Name</label>
                <input type="text" id="bank_name" name="bank_name" value="{{ Auth::user()->bank_name }}"
                    class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    placeholder="Enter bank name">
            </div>
            <div>
                <label for="account_name" class="block mb-2 text-sm font-medium text-gray-300">Account Name</label>
                <input type="text" id="account_name" name="account_name" value="{{ Auth::user()->account_name }}"
                    class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    placeholder="Enter account name">
            </div>
            <div>
                <label for="account_no" class="block mb-2 text-sm font-medium text-gray-300">Account Number</label>
                <input type="text" id="account_no" name="account_no" value="{{ Auth::user()->account_number }}"
                    class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    placeholder="Enter account number">
            </div>
            <div>
                <label for="swiftcode" class="block mb-2 text-sm font-medium text-gray-300">Swift Code</label>
                <input type="text" id="swiftcode" name="swiftcode" value="{{ Auth::user()->swift_code }}"
                    class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    placeholder="Enter Swift code">
            </div>
        </div>
    </fieldset>

    <fieldset>
        <h3 class="mb-4 text-lg font-semibold text-white">Crypto Wallet Addresses</h3>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="btc_address" class="block mb-2 text-sm font-medium text-gray-300">Bitcoin (BTC)</label>
                <input type="text" id="btc_address" name="btc_address" value="{{ Auth::user()->btc_address }}"
                    class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    placeholder="Enter Bitcoin address">
            </div>
            <div>
                <label for="eth_address" class="block mb-2 text-sm font-medium text-gray-300">Ethereum (ETH)</label>
                <input type="text" id="eth_address" name="eth_address" value="{{ Auth::user()->eth_address }}"
                    class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    placeholder="Enter Ethereum address">
            </div>
            <div>
                <label for="ltc_address" class="block mb-2 text-sm font-medium text-gray-300">Litecoin (LTC)</label>
                <input type="text" id="ltc_address" name="ltc_address" value="{{ Auth::user()->ltc_address }}"
                    class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    placeholder="Enter Litecoin address">
            </div>
            <div>
                <label for="usdt_address" class="block mb-2 text-sm font-medium text-gray-300">USDT (TRC20)</label>
                <input type="text" id="usdt_address" name="usdt_address" value="{{ Auth::user()->usdt_address }}"
                    class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    placeholder="Enter USDT.TRC20 address">
            </div>
        </div>
    </fieldset>

    <div class="flex justify-end">
        <button type="button" id="submitBtn"
            class="px-6 py-2.5 font-semibold text-center text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed">
            <span id="btnText">Save Settings</span>
            <i id="btnSpinner" class="fas fa-spinner fa-spin ml-2 hidden"></i>
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('updatewithdrawalinfo');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    function showLoading() {
        submitBtn.disabled = true;
        btnText.textContent = 'Saving...';
        btnSpinner.classList.remove('hidden');
    }

    function hideLoading() {
        submitBtn.disabled = false;
        btnText.textContent = 'Save Settings';
        btnSpinner.classList.add('hidden');
    }

    function serializeForm(form) {
        const formData = new FormData(form);
        const params = new URLSearchParams();

        for (const [key, value] of formData) {
            params.append(key, value);
        }

        return params.toString();
    }

    function submitForm() {
        showLoading();

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        fetch(form.action, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: serializeForm(form)
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
                    text: data.success || 'Withdrawal information updated successfully!',
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

            let errorMessage = 'An error occurred while updating your information.';

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
