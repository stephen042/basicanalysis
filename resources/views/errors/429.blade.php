@extends('errors::illustrated-layout')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('status', __('Rate Limited'))
@section('icon')
    <i class="fas fa-tachometer-alt text-6xl text-danger"></i>
@endsection
@section('message', __('You have made too many requests in a short period. Please slow down and try again in a few moments. This helps us maintain optimal performance for all users.'))
@section('details')
    <strong>What can I do?</strong><br>
    Wait a few minutes before trying again. If you continue to see this message, please contact support.
@endsection
