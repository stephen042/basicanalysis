@extends('layouts.dash')
@section('title', $title)
@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Manual Trading</h1>
        <p class="mt-1 text-sm text-gray-400">Choose an instrument and direction. Trades are settled by the trading team.</p>
    </div>
    <x-danger-alert /><x-success-alert />
    @if($errors->any())<div class="rounded-lg bg-red-500/10 p-3 text-sm text-red-300">{{ $errors->first() }}</div>@endif
    <div class="rounded-xl border border-dark-100 bg-dark-200 p-5 md:p-7">
        <form method="POST" action="{{ route('manual-trading.store') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="submission_key" value="{{ \Illuminate\Support\Str::random(48) }}">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <label class="text-sm text-gray-300">Category
                    <select id="manual-category" name="category" class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white">
                        @foreach($instruments as $category => $items)
                            <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="flex items-center gap-3 rounded-lg border border-dark-100 bg-dark-100 px-4 py-3">
                    <img id="manual-instrument-icon" class="h-10 w-10 rounded-full object-contain" alt="" hidden>
                    <span id="manual-instrument-badge" class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 font-bold text-primary">₿</span>
                    <div><div id="manual-instrument-name" class="font-semibold text-white">BTC/USD</div><div id="manual-instrument-kind" class="text-xs capitalize text-gray-400">crypto instrument</div></div>
                </div>
                <label class="text-sm text-gray-300">Pair / Instrument
                    <select id="manual-instrument" name="instrument" class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white"></select>
                </label>
                <label class="text-sm text-gray-300">Leverage
                    <select id="manual-leverage" name="leverage" class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white"></select>
                </label>
                <label class="text-sm text-gray-300">Duration
                    <select name="duration_minutes" class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white">
                        @foreach([1=>'1 minute',5=>'5 minutes',15=>'15 minutes',30=>'30 minutes',60=>'1 hour',240=>'4 hours',1440=>'1 day'] as $minutes=>$label)<option value="{{ $minutes }}">{{ $label }}</option>@endforeach
                    </select>
                </label>
                <label class="text-sm text-gray-300">Amount
                    <input name="amount" type="number" min="1" step="0.01" value="{{ old('amount') }}" required class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white" placeholder="Amount to trade">
                </label>
                <div class="flex items-end gap-3">
                    <button name="direction" value="BUY" class="w-full rounded-lg bg-green-600 px-4 py-3 font-bold text-white hover:bg-green-700">BUY</button>
                    <button name="direction" value="SELL" class="w-full rounded-lg bg-red-600 px-4 py-3 font-bold text-white hover:bg-red-700">SELL</button>
                </div>
            </div>
            @error('instrument')<p class="text-sm text-red-400">{{ $message }}</p>@enderror
            @error('amount')<p class="text-sm text-red-400">{{ $message }}</p>@enderror
            <p class="text-xs text-gray-500">Trade size is reserved from your account balance when opened. Profit is credited to earnings after admin settlement. Entry prices are captured when a matching market asset is configured.</p>
        </form>
    </div>
    <div class="rounded-xl border border-dark-100 bg-dark-200 p-5">
        <h2 class="mb-4 text-lg font-semibold text-white">Manual Trading History</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-xs uppercase text-gray-400"><tr><th class="p-3">Instrument</th><th class="p-3">Category</th><th class="p-3">Side</th><th class="p-3">Amount</th><th class="p-3">Leverage</th><th class="p-3">Duration</th><th class="p-3">Profit/Loss</th><th class="p-3">Status</th><th class="p-3">Date</th></tr></thead>
                <tbody class="divide-y divide-dark-100 text-gray-200">
                @forelse($trades as $trade)
                    <tr><td class="p-3">{{ $trade->instrument }}</td><td class="p-3 capitalize">{{ $trade->category }}</td><td class="p-3">{{ $trade->direction }}</td><td class="p-3">{{ $settings->currency }}{{ number_format($trade->amount,2) }}</td><td class="p-3">{{ $trade->leverage }}x</td><td class="p-3">{{ $trade->duration_minutes }}m</td><td class="p-3">{{ is_null($trade->result_amount) ? '—' : $settings->currency.number_format($trade->result_amount,2) }}</td><td class="p-3 capitalize">{{ $trade->status }}</td><td class="p-3 whitespace-nowrap">{{ $trade->created_at->format('M j, Y H:i') }}</td></tr>
                @empty <tr><td colspan="9" class="p-8 text-center text-gray-400">No manual trades yet.</td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $trades->links() }}</div>
    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
(function () {
    var instruments = @json($instruments), leverages = @json($leverages), category = document.getElementById('manual-category'), instrument = document.getElementById('manual-instrument');
    var logos = {
        'BTC/USD':'https://assets.coingecko.com/coins/images/1/large/bitcoin.png',
        'BTC/USDT':'https://assets.coingecko.com/coins/images/1/large/bitcoin.png',
        'ETH/USD':'https://assets.coingecko.com/coins/images/279/large/ethereum.png',
        'ETH/USDT':'https://assets.coingecko.com/coins/images/279/large/ethereum.png',
        'SOL/USD':'https://assets.coingecko.com/coins/images/4128/large/solana.png',
        'XRP/USD':'https://assets.coingecko.com/coins/images/44/large/xrp-symbol-white-128.png'
    };
    var flags = {'EUR':'eu','GBP':'gb','USD':'us','JPY':'jp','CHF':'ch','AUD':'au','CAD':'ca'};
    function update() {
        var current = instrument.value, options = instruments[category.value] || [];
        instrument.innerHTML = '';
        options.forEach(function (item) {
            var option = document.createElement('option'); option.value = item; option.textContent = item;
            if (item === current) option.selected = true;
            instrument.appendChild(option);
        });
        var leverage = document.getElementById('manual-leverage'), selected = Number(leverage.value) || 10;
        leverage.innerHTML = '';
        (leverages[category.value] || [1, 2, 5, 10]).forEach(function(value) {
            var option = document.createElement('option'); option.value = value; option.textContent = value + 'x';
            if (value === selected) option.selected = true;
            leverage.appendChild(option);
        });
        updateBadge();
    }
    function updateBadge() {
        var name = instrument.value || '', img = document.getElementById('manual-instrument-icon'), badge = document.getElementById('manual-instrument-badge');
        document.getElementById('manual-instrument-name').textContent = name;
        document.getElementById('manual-instrument-kind').textContent = category.value + ' instrument';
        img.hidden = true; badge.hidden = false; img.onerror = function(){img.hidden=true;badge.hidden=false;};
        if (logos[name]) { img.src=logos[name]; img.hidden=false; badge.hidden=true; }
        else if (category.value === 'forex') {
            var codes=name.replace('/',' ').split(' ');
            badge.textContent=(flags[codes[0]]||'fx').toUpperCase(); badge.hidden=false;
            img.src='https://flagcdn.com/w80/'+flags[codes[0]]+'.png'; img.hidden=false;
            img.alt=name+' flag';
        } else {
            var indexBadges = {'NASDAQ 100':'NDX','S&P 500':'SPX','Dow Jones':'DJI','FTSE 100':'UKX'};
            badge.textContent = category.value === 'indices' ? (indexBadges[name] || 'IDX') : (name.indexOf('BTC') === 0 ? '₿' : '◆');
        }
    }
    category.addEventListener('change', update); instrument.addEventListener('change', updateBadge); update();
})();
</script>
@endsection
