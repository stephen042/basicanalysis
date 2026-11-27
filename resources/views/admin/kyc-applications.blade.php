<?php
if (Auth('admin')->User()->dashboard_style == 'light') {
    $text = 'dark';
    $bg = 'light';
} else {
    $bg = 'dark';
    $text = 'light';
}
?>
@extends('layouts.app')
@section('content')
    @include('admin.topmenu')
    @include('admin.sidebar')
    <div class="main-panel ">
        <div class="content ">
            <div class="page-inner">
                <p>
                    <a href="{{ route('kyc') }}">
                        <i class="p-2 rounded-lg fa fa-arrow-circle-left fa-2x bg-light"></i>
                    </a>
                </p>

                <div class="mt-2 mb-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="title1 text-{{ $text }}">{{ $kyc->user->name }} KYC Application</h1>
                        @if ($kyc->status == 'Verified')
                            <span class="badge badge-success">Verified</span>
                        @else
                            <span class="badge badge-danger">{{ $kyc->status }}</span>
                        @endif
                    </div>
                    <a href="#" data-toggle="modal" data-target="#action" class="btn btn-primary btn-sm">Action</a>
                </div>
                <div id="action" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header ">
                                <h3 class="mb-2 d-inline text-{{ $text }}">Process KYC</h3>
                                <button type="button" class="close text-{{ $text }}" data-dismiss="modal"
                                    aria-h6="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body ">
                                <form action="{{ route('processkyc') }}" method="post">
                                    @csrf
                                    <div class="form-group">
                                        <select name="action" id="" class="form-control  text-{{ $text }}"
                                            required>
                                            <option value="Accept">Accept and verify user</option>
                                            <option value="Reject">Reject and remain unverified</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <textarea name="message" placeholder="Enter Message " class="form-control  text-{{ $text }}" required>This is to inform you that following the documents you submited, your account have been verified. You can now enjoy all our services without restrictions. Cheers!!</textarea>
                                    </div>
                                    <div class="form-group">
                                        <h5 class="text-{{ $text }}">Email subject</h5>
                                        <input type="text" name="subject" id=""
                                            class="form-control  text-{{ $text }}"
                                            placeholder="Account is verified successfully" required>
                                    </div>
                                    <input type="hidden" name="kyc_id" value="{{ $kyc->id }}">
                                    <div class="form-group">
                                        <button type="submit" class="px-4 btn btn-primary"> Confirm </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /view KYC ID Modal -->
                <x-danger-alert />
                <x-success-alert />
                <div class="mb-5 row">
                    <div class="col-md-12">
                        <div class="card p-md-4 p-2 ">
                            <div class="card-body">
                                <div class="row">
                                    <div class="mb-3 col-md-12 border-bottom">
                                        <small class="text-primary">Personal Information</small>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <h2 class="text-{{ $text }}">{{ $kyc->first_name }}</h2>
                                        <small class="text-muted">First name</small>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <h2 class="text-{{ $text }}">{{ $kyc->last_name }}</h2>
                                        <small class="text-muted">Last name</small>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <h2 class="text-{{ $text }}">{{ $kyc->email }}</h2>
                                        <small class="text-muted">Email</small>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <h2 class="text-{{ $text }}">{{ $kyc->phone_number }}</h2>
                                        <small class="text-muted">Phone Number</small>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <h2 class="text-{{ $text }}">{{ $kyc->dob }}</h2>
                                        <small class="text-muted">Date of Birth</small>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <h2 class="text-{{ $text }}">{{ $kyc->social_media }}</h2>
                                        <small class="text-muted">Social Media username</small>
                                    </div>
                                    <div class="my-3 border-bottom col-md-12">
                                        <small class="text-primary">Address Information</small>
                                    </div>

                                    <div class="mb-3 col-md-6">
                                        <h2 class="text-{{ $text }}">{{ $kyc->address }}</h2>
                                        <small class="text-muted">Address Line</small>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <h2 class="text-{{ $text }}">{{ $kyc->city }}</h2>
                                        <small class="text-muted">City</small>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <h2 class="text-{{ $text }}">{{ $kyc->state }}</h2>
                                        <small class="text-muted">State</small>
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <h2 class="text-{{ $text }}">{{ $kyc->country }}</h2>
                                        <small class="text-muted">Nationality</small>
                                    </div>
                                    <div class="my-3 border-bottom col-md-12">
                                        <small class="text-primary">Document Information</small>
                                    </div>
                                    <div class="mb-5 col-md-12">
                                        <h2 class="text-{{ $text }}">{{ $kyc->document_type }}</h2>
                                        <small class="text-muted">Document type</small>
                                    </div>
                                    @if($kyc->frontimg)
                                        <div class="mb-4 col-md-6">
                                            @if($kyc->hasDocument('front'))
                                                <a href="{{ $kyc->documentUrl('front') }}" target="_blank" rel="noopener noreferrer" title="Open full-size front image in a new tab">
                                                    <img src="{{ $kyc->documentUrl('front') }}" alt="Front of {{ $kyc->document_type }}" class="img-fluid d-block rounded border" style="width:100%;max-height:650px;object-fit:contain;background:#111">
                                                </a>
                                                <small class="mt-2 d-block text-muted">Front of document · Click to open full size</small>
                                            @else
                                                <div class="alert alert-warning mb-0">
                                                    <strong>Front document file is missing from storage.</strong>
                                                    The database record is intact, but the original file could not be found on the server. Ask the user to resubmit only if verification cannot proceed.
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    @if($kyc->backimg)
                                        <div class="mb-4 col-md-6">
                                            @if($kyc->hasDocument('back'))
                                                <a href="{{ $kyc->documentUrl('back') }}" target="_blank" rel="noopener noreferrer" title="Open full-size back image in a new tab">
                                                    <img src="{{ $kyc->documentUrl('back') }}" alt="Back of {{ $kyc->document_type }}" class="img-fluid d-block rounded border" style="width:100%;max-height:650px;object-fit:contain;background:#111">
                                                </a>
                                                <small class="mt-2 d-block text-muted">Back of document · Click to open full size</small>
                                            @else
                                                <div class="alert alert-warning mb-0">
                                                    <strong>Back document file is missing from storage.</strong>
                                                    The database record is intact, but the original file could not be found on the server. Ask the user to resubmit only if verification cannot proceed.
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
