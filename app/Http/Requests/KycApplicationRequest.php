<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KycApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'first_name' => 'required|max:255',
            'last_name' => 'required',
            'email' => 'required|email|max:255|unique:kycs,email',
            'phone_number' => 'required',
            'dob' => 'required',
            'social_media' => 'required',
            'address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'country' => 'required',
            'document_type' => 'required|in:Int\'l Passport,National ID,Drivers License',
            'frontimg' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'backimg' => 'required_if:document_type,National ID,Drivers License|nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }
}
