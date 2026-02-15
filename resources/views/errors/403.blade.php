@extends('errors::illustrated-layout')

@section('title', __('Access Forbidden'))
@section('code', '403')
@section('status', __('Forbidden'))
@section('icon')
    <i class="fas fa-ban text-6xl text-danger"></i>
@endsection
@section('message', __($exception->getMessage() ?: 'Sorry, you don\'t have permission to access this resource. This area is restricted to authorized users only.'))
