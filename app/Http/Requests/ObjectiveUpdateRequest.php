<?php

namespace App\Http\Requests;

use App\Enums\ObjectivePriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class ObjectiveUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $objective = $this->route('objective');

        return [
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('objectives', 'title')->ignore($objective->id),
            ],
            'description' => 'nullable|string|max:1000',
            'status' => 'prohibited',
            'priority'    => ['string', new Enum(ObjectivePriority::class)]
        ];
    }
}