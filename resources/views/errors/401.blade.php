@extends('errors::illustrated-layout')

@section('title', __('Unauthorized Access'))
@section('code', '401')
@section('status', __('Unauthorized'))
@section('icon')
    <i class="fas fa-lock text-6xl text-warning"></i>
@endsection
@section('message', __('You are not authorized to access this page. Please log in with appropriate credentials or contact support if you believe this is an error.'))
