@extends('errors::illustrated-layout')

@section('title', __('Internal Server Error'))
@section('code', '500')
@section('status', __('Server Error'))
@section('icon')
    <i class="fas fa-server text-6xl text-danger"></i>
@endsection
@section('message', __('Something went wrong on our end. Our team has been notified and is working to fix the issue. Please try refreshing the page or come back in a few minutes.'))
@section('details')
    <strong>What you can do:</strong><br>
    • Try refreshing the page<br>
    • Clear your browser cache<br>
    • Contact support if the problem persists<br>
    • Check our status page for any ongoing issues
@endsection
