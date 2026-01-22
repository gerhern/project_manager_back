<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class ProjectUpdateRequest extends FormRequest
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
        $project = $this->route('project');
        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('projects', 'name')->ignore($project->id),
            ],
            'description' => 'nullable|string|max:1000',
            'status' => 'prohibited'
        ];
    }
}
