@extends('layouts.dash')
@section('title', $title)
@section('content')
<div class="container px-4 py-10 mx-auto">
    <div class="max-w-4xl mx-auto" x-data="{
        step: 1,
        totalSteps: 3,
        documentType: 'Int\'l Passport',
        frontPreview: '',
        backPreview: ''
    }">
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-white md:text-4xl">Identity Verification (KYC)</h1>
            <p class="mt-2 text-gray-400">Please fill out the form carefully. You can't edit these details once submitted.</p>
        </div>

        <x-danger-alert />
        <x-success-alert />
        <x-error-alert />

        <div class="p-8 rounded-lg shadow-lg bg-dark-200">
            <!-- Progress Bar -->
            <div class="mb-8">
                <div class="relative">
                    <div class="absolute top-0 left-0 w-full h-1 rounded-full bg-dark-100"></div>
                    <div class="absolute top-0 left-0 h-1 rounded-full bg-primary" :style="`width: ${((step - 1) / (totalSteps - 1)) * 100}%;`"></div>
                    <div class="flex justify-between">
                        <div class="flex flex-col items-center" :class="{'text-primary': step >= 1, 'text-gray-500': step < 1}">
                            <div class="flex items-center justify-center w-8 h-8 border-2 rounded-full" :class="{'bg-primary border-primary': step >= 1, 'bg-dark-200 border-gray-500': step < 1}">
                                <i class="fas fa-user" x-show="step > 1"></i>
                                <span x-show="step <= 1">1</span>
                            </div>
                            <p class="mt-2 text-xs">Personal</p>
                        </div>
                        <div class="flex flex-col items-center" :class="{'text-primary': step >= 2, 'text-gray-500': step < 2}">
                            <div class="flex items-center justify-center w-8 h-8 border-2 rounded-full" :class="{'bg-primary border-primary': step >= 2, 'bg-dark-200 border-gray-500': step < 2}">
                                <i class="fas fa-map-marker-alt" x-show="step > 2"></i>
                                <span x-show="step <= 2">2</span>
                            </div>
                            <p class="mt-2 text-xs">Address</p>
                        </div>
                        <div class="flex flex-col items-center" :class="{'text-primary': step >= 3, 'text-gray-500': step < 3}">
                            <div class="flex items-center justify-center w-8 h-8 border-2 rounded-full" :class="{'bg-primary border-primary': step >= 3, 'bg-dark-200 border-gray-500': step < 3}">
                                <span>3</span>
                            </div>
                            <p class="mt-2 text-xs">Documents</p>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('kycsubmit') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Step 1: Personal Details -->
                <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100">
                    <h3 class="mb-6 text-xl font-bold text-white">Personal Details</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label for="first_name" class="block mb-2 text-sm font-medium text-gray-300">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div>
                            <label for="last_name" class="block mb-2 text-sm font-medium text-gray-300">Last Name <span class="text-red-500">*</span></label>
                            <input type="text" name="last_name" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div>
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-300">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ auth()->user()->email }}" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div>
                            <label for="phone_number" class="block mb-2 text-sm font-medium text-gray-300">Phone Number <span class="text-red-500">*</span></label>
                            <input type="text" name="phone_number" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div>
                            <label for="dob" class="block mb-2 text-sm font-medium text-gray-300">Date of Birth <span class="text-red-500">*</span></label>
                            <input type="date" name="dob" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div>
                            <label for="social_media" class="block mb-2 text-sm font-medium text-gray-300">Twitter or Facebook Username <span class="text-red-500">*</span></label>
                            <input type="text" name="social_media" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                    </div>
                    <div class="flex justify-end mt-8">
                        <button type="button" @click="step++" class="px-6 py-2 font-semibold text-white rounded-lg bg-primary hover:bg-primary-dark">Next Step &rarr;</button>
                    </div>
                </div>

                <!-- Step 2: Address -->
                <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100">
                    <h3 class="mb-6 text-xl font-bold text-white">Your Address</h3>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label for="address" class="block mb-2 text-sm font-medium text-gray-300">Address Line <span class="text-red-500">*</span></label>
                            <input type="text" name="address" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div>
                            <label for="city" class="block mb-2 text-sm font-medium text-gray-300">City <span class="text-red-500">*</span></label>
                            <input type="text" name="city" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div>
                            <label for="state" class="block mb-2 text-sm font-medium text-gray-300">State <span class="text-red-500">*</span></label>
                            <input type="text" name="state" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                        <div class="md:col-span-2">
                            <label for="country" class="block mb-2 text-sm font-medium text-gray-300">Nationality <span class="text-red-500">*</span></label>
                            <input type="text" name="country" class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary" required>
                        </div>
                    </div>
                    <div class="flex justify-between mt-8">
                        <button type="button" @click="step--" class="px-6 py-2 font-semibold text-white rounded-lg bg-dark-300 hover:bg-dark-100">&larr; Previous</button>
                        <button type="button" @click="step++" class="px-6 py-2 font-semibold text-white rounded-lg bg-primary hover:bg-primary-dark">Next Step &rarr;</button>
                    </div>
                </div>

                <!-- Step 3: Document Upload -->
                <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-90" x-transition:enter-end="opacity-100 transform scale-100">
                    <h3 class="mb-6 text-xl font-bold text-white">Document Upload</h3>
                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-300">Document Type</label>
                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <label :class="{'bg-primary text-white': documentType === 'Int\'l Passport', 'bg-dark-300 text-gray-300': documentType !== 'Int\'l Passport'}" class="flex flex-col items-center justify-center p-4 text-center border rounded-lg cursor-pointer border-dark-100 hover:bg-primary hover:text-white">
                                <input type="radio" name="document_type" value="Int'l Passport" class="hidden" x-model="documentType">
                                <i class="mb-2 text-2xl fas fa-passport"></i>
                                <span class="text-sm">Passport</span>
                            </label>
                            <label :class="{'bg-primary text-white': documentType === 'National ID', 'bg-dark-300 text-gray-300': documentType !== 'National ID'}" class="flex flex-col items-center justify-center p-4 text-center border rounded-lg cursor-pointer border-dark-100 hover:bg-primary hover:text-white">
                                <input type="radio" name="document_type" value="National ID" class="hidden" x-model="documentType">
                                <i class="mb-2 text-2xl fas fa-id-card"></i>
                                <span class="text-sm">National ID</span>
                            </label>
                            <label :class="{'bg-primary text-white': documentType === 'Drivers License', 'bg-dark-300 text-gray-300': documentType !== 'Drivers License'}" class="flex flex-col items-center justify-center p-4 text-center border rounded-lg cursor-pointer border-dark-100 hover:bg-primary hover:text-white">
                                <input type="radio" name="document_type" value="Drivers License" class="hidden" x-model="documentType">
                                <i class="mb-2 text-2xl fas fa-address-card"></i>
                                <span class="text-sm">Driver's License</span>
                            </label>
                        </div>
                    </div>
                    <div class="p-4 mb-6 rounded-lg bg-dark-100">
                        <h6 class="font-semibold text-white">Document Requirements:</h6>
                        <ul class="mt-2 space-y-1 text-sm text-gray-400 list-disc list-inside">
                            <li>Chosen credential must not be expired.</li>
                            <li>Document should be in good condition and clearly visible.</li>
                            <li>Make sure that there is no light glare on the document.</li>
                        </ul>
                    </div>
                    <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-300">Document Front Side <span class="text-red-500">*</span></label>
                            <input type="file" name="frontimg" @change="frontPreview = URL.createObjectURL($event.target.files[0])" class="hidden" id="frontimg" required>
                            <label for="frontimg" class="flex flex-col items-center justify-center w-full p-6 text-center border-2 border-dashed rounded-lg cursor-pointer border-dark-100 hover:border-primary">
                                <img :src="frontPreview" x-show="frontPreview" class="object-cover h-32 max-w-full mb-4 rounded-lg">
                                <div x-show="!frontPreview">
                                    <i class="mb-2 text-3xl text-gray-500 fas fa-cloud-upload-alt"></i>
                                    <p class="text-sm text-gray-400">Click to upload</p>
                                </div>
                            </label>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium text-gray-300">Document Back Side <span class="text-red-500">*</span></label>
                            <input type="file" name="backimg" @change="backPreview = URL.createObjectURL($event.target.files[0])" class="hidden" id="backimg" required>
                            <label for="backimg" class="flex flex-col items-center justify-center w-full p-6 text-center border-2 border-dashed rounded-lg cursor-pointer border-dark-100 hover:border-primary">
                                <img :src="backPreview" x-show="backPreview" class="object-cover h-32 max-w-full mb-4 rounded-lg">
                                <div x-show="!backPreview">
                                    <i class="mb-2 text-3xl text-gray-500 fas fa-cloud-upload-alt"></i>
                                    <p class="text-sm text-gray-400">Click to upload</p>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="flex items-center mt-6">
                        <input class="w-4 h-4 rounded bg-dark-300 border-dark-100 text-primary focus:ring-primary" type="checkbox" value="" id="terms" required>
                        <label class="ml-2 text-sm text-gray-300" for="terms">
                            I confirm that all the information I have entered is correct.
                        </label>
                    </div>
                    <div class="flex items-center justify-between mt-8">
                        <button type="button" @click="step--" class="px-6 py-2 font-semibold text-white rounded-lg bg-dark-300 hover:bg-dark-100">&larr; Previous</button>
                        @if (Auth::user()->account_verify == 'Under review')
                            <button type="submit" class="px-6 py-2 font-semibold text-gray-500 bg-gray-700 rounded-lg cursor-not-allowed" disabled>Submission Pending</button>
                        @else
                            <button type="submit" class="px-6 py-2 font-semibold text-white rounded-lg bg-primary hover:bg-primary-dark">Submit Application</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

