@extends('emails.layout')

@section('content')
<h1>{{ $salutaion ? $salutaion : "Hello" }} {{ $recipient }},</h1>

@if (isset($attachment) && $attachment != null)
<div style="text-align: center; margin: 20px 0;">
    <img src="{{ $message->embed(asset('storage/'. $attachment)) }}" alt="Notification Image" style="max-width: 100%; height: auto; border-radius: 8px;">
</div>
@endif

<div style="margin: 20px 0;">
{!! $body !!}
</div>

@if (isset($action_url) && $action_url)
<div class="text-center">
    <a href="{{ $action_url }}" class="button">{{ $action_text ?? 'View Details' }}</a>
</div>
@endif

<p style="margin-top: 30px;">Best regards,<br><strong>The {{ $settings->site_name ?? config('app.name') }} Team</strong></p>

@if (isset($footer_note) && $footer_note)
<div class="divider"></div>
<p class="text-muted" style="font-size: 13px;">{{ $footer_note }}</p>
@endif
@endsection
