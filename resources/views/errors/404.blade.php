@extends('errors::illustrated-layout')

@section('title', __('Page Not Found'))
@section('code', '404')
@section('status', __('Not Found'))
@section('icon')
    <i class="fas fa-search text-6xl text-primary"></i>
@endsection
@section('message', __('The page you are looking for might have been removed, had its name changed, or is temporarily unavailable. Please check the URL or navigate back to our homepage.'))
