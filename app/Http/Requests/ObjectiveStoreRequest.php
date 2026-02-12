<?php

namespace App\Http\Requests;

use App\Enums\ObjectivePriority;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ObjectiveStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');

        if(!$project){
            return false;
        }

        return true;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => 'required|string|min:3|max:255',
            'description' => 'nullable|string|min:3|max:1000',
            'status'      => 'prohibited',
            'priority'    => ['string', 'nullable', new Enum(ObjectivePriority::class)]
        ];
    }
}
