<?php

namespace App\Http\Requests;

use App\Enums\TeamStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Spatie\Permission\Models\Role;

class   TeamUpdateRequest extends FormRequest
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
        $teamId = $this->route('team')->id;
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('teams', 'name')->ignore($teamId),
            ],
            'description' => 'nullable|string|max:1000',
            'status' => 'prohibited'
        ];
    }
}
