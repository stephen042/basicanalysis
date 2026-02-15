@extends('errors::illustrated-layout')

@section('title', __('Service Temporarily Unavailable'))
@section('code', '503')
@section('status', __('Maintenance Mode'))
@section('icon')
    <i class="fas fa-tools text-6xl text-warning"></i>
@endsection
@section('message', __('We are currently performing scheduled maintenance to improve our trading platform. We\'ll be back online shortly. Thank you for your patience.'))
@section('details')
    <strong>Maintenance in Progress</strong><br>
    We\'re upgrading our systems to serve you better. Your account and funds are safe. Check back in a few minutes.
@endsection
