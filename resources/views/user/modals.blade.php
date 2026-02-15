<!-- Subscription Modal -->
<div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 bg-black bg-opacity-50" style="display: none;">
    <div @click.away="showModal = false" class="w-full max-w-2xl mx-auto rounded-lg shadow-lg bg-dark-200">
        <div class="p-6">
            <div class="flex items-center justify-between pb-3 border-b border-dark-100">
                <h4 class="text-xl font-bold text-white">Subscribe to AI Trading</h4>
                <button @click="showModal = false" class="text-gray-400 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="py-6">
                <form role="form" method="post" action="{{ route('savemt4details') }}">
                    @csrf
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-300">Subscription Duration</label>
                            <select name="duration" onchange="calcAmount(this)" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <option value="default">Select duration</option>
                                <option>Monthly</option>
                                <option>Quaterly</option>
                                <option>Yearly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-300">Amount to Pay</label>
                            <input id="amount" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100" type="text" disabled>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-300">Login*</label>
                            <input name="userid" type="text" required class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-300">Account Password*</label>
                            <input name="pswrd" type="password" required class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-300">Account Name*</label>
                            <input name="name" type="text" required class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-300">Account Type</label>
                            <input name="acntype" type="text" placeholder="E.g. Standard" required class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-300">Currency*</label>
                            <input name="currency" type="text" placeholder="E.g. USD" required class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-300">Leverage*</label>
                            <input name="leverage" type="text" placeholder="E.g. 1:500" required class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block mb-2 text-sm font-medium text-gray-300">Server*</label>
                            <input name="server" type="text" placeholder="E.g. HantecGlobal-live" required class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                    <div class="pt-4 mt-6 border-t border-dark-100">
                        <p class="mb-4 text-sm text-gray-400">The subscription amount will be deducted from your main account balance.</p>
                        <input id="amountpay" type="hidden" name="amount">
                        <button type="submit" class="w-full px-6 py-3 font-semibold text-white rounded-lg bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-dark-200 focus:ring-primary">
                            Subscribe Now
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    function calcAmount(sub) {
        const amountEl = document.getElementById('amount');
        const amountPayEl = document.getElementById('amountpay');
        const currency = '{{ $settings->currency }}';

        let fee = 0;
        if (sub.value === "Quaterly") {
            fee = '{{ $settings->quarterlyfee }}';
        } else if (sub.value === "Yearly") {
            fee = '{{ $settings->yearlyfee }}';
        } else if (sub.value === "Monthly") {
            fee = '{{ $settings->monthlyfee }}';
        }

        if (fee > 0) {
            amountEl.value = currency + fee;
            amountPayEl.value = fee;
        } else {
            amountEl.value = '';
            amountPayEl.value = '';
        }
    }
</script>

