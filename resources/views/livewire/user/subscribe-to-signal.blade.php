<div>
    <style>
        /* Compact Flex Grid */
        .signal-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .compact-plan-card {
            flex: 1 1 calc(33.333% - 15px);
            /* 3 per row */
            min-width: 200px;
            background: #1a1d24;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .compact-plan-card:hover {
            border-color: #3b82f6;
            background: #1e222b;
            transform: translateY(-3px);
        }

        /* Small Text Styling */
        .plan-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .plan-price {
            font-size: 1.4rem;
            font-weight: 800;
            color: #3b82f6;
            margin: 10px 0;
        }

        .plan-duration {
            font-size: 0.7rem;
            color: #94a3b8;
            background: rgba(255, 255, 255, 0.05);
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        /* Action Button */
        .btn-select-plan {
            background: rgba(59, 130, 246, 0.1);
            color: #3b82f6;
            border: 1px solid rgba(59, 130, 246, 0.2);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 8px;
            border-radius: 8px;
            width: 100%;
            margin-top: 10px;
            transition: 0.2s;
        }

        .compact-plan-card:hover .btn-select-plan {
            background: #3b82f6;
            color: white;
        }

        /* Payment UI - Address Input for Copy */
        .copy-group {
            background: #000;
            border-radius: 8px;
            display: flex;
            overflow: hidden;
            border: 1px solid #333;
        }

        .copy-group input {
            background: transparent;
            border: none;
            color: #00ff88;
            padding: 10px;
            font-family: monospace;
            font-size: 0.85rem;
            flex: 1;
        }

        .btn-copy-addon {
            background: #333;
            border: none;
            color: white;
            padding: 0 15px;
            cursor: pointer;
        }

        .qr-frame {
            background: white;
            padding: 10px;
            border-radius: 8px;
            width: 140px;
            height: 140px;
            margin: 0 auto;
        }
    </style>

    {{-- STEP 1: Compact 3-Per-Row Grid --}}

    <div class="signal-grid">
        @forelse($signals as $signal)
            <div class="compact-plan-card {{ $loop->index == 1 ? 'active-featured' : '' }}">
                {{-- Tag 'Popular' on the second item as an example --}}
                @if ($loop->index == 1)
                    <div
                        style="position: absolute; top: 0; left: 50%; transform: translateX(-50%); background: #3b82f6; color: white; font-size: 0.6rem; padding: 1px 10px; border-radius: 0 0 5px 5px;">
                        POPULAR</div>
                @endif

                <div>
                    <span class="plan-duration">{{ $signal->duration }} Days</span>
                    <h5 class="plan-title">{{ $signal->name }}</h5>
                    <div class="plan-price">
                        {{ $settings->currency }}{{ number_format($signal->amount, 2) }}
                    </div>
                </div>

                @php
                    $hasActive = auth()->user()?->hasActiveSignalFor($signal->id);
                @endphp

                @if ($hasActive)
                    <div class="btn-select-plan"
                        style="text-align:center; background:#10b981; color:white; padding:10px; border-radius:8px; font-weight:600;">
                        Ongoing
                    </div>
                @else
                    <a class="btn-select-plan" href="{{ route('purchase-signal', [$signal->id]) }}"
                        style="text-decoration: none; display: block; text-align: center;">
                        Purchase
                    </a>
                @endif
            </div>
        @empty
            <div class="col-12 text-center text-muted">
                <p>No signal plans available at the moment.</p>
            </div>
        @endforelse

        <style>
            /* Ensuring the active-featured card stands out */
            .active-featured {
                border-color: rgba(59, 130, 246, 0.4) !important;
            }

            .btn-select-plan:hover {
                color: #fff;
                background: #3b82f6;
            }
        </style>
    </div>
</div>
