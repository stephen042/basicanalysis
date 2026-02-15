@extends('errors::illustrated-layout')

@section('title', __('Session Expired'))
@section('code', '419')
@section('status', __('Page Expired'))
@section('icon')
    <i class="fas fa-clock text-6xl text-warning"></i>
@endsection
@section('message', __('Your session has expired due to inactivity. For your security, please refresh the page and try again. This helps keep your trading account safe.'))
@section('details')
    <strong>Why did this happen?</strong><br>
    Your session expired for security reasons after a period of inactivity. Simply refresh the page to continue.
@endsection
