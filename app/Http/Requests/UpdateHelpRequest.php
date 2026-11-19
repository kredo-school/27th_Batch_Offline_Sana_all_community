<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateHelpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'requester_type' => [
                'required',
                Rule::in([
                    'individual',
                    'shop',
                    'company',
                ]),
            ],

            'category_id' => [
                'required',
                'integer',
                Rule::exists('help_categories', 'id')
                    ->where('is_active', true),
            ],

            'company_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
            ],

            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'event_start_at' => [
                'required',
                'date',
            ],

            'event_end_at' => [
                'required',
                'date',
                'after:event_start_at',
            ],

            'reward_type' => [
                'required',
                Rule::in([
                    'hourly',
                    'fixed',
                    'negotiable',
                ]),
            ],

            'reward_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'required_unless:reward_type,negotiable',
            ],

            'capacity' => [
                'required',
                'integer',
                'min:1',
            ],

            'requirements' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'application_deadline' => [
                'nullable',
                'date',
            ],

            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ];
    }
}
