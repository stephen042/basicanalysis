@extends('layouts.dash')

@section('title', 'Copy Trading History')

@section('content')
    @include('user.copy._history_section', [
        'copyHistoryTitle' => 'Copy Trading History',
        'copyHistorySubtitle' => 'Complete history of your copy trading activities',
    ])
@endsection
