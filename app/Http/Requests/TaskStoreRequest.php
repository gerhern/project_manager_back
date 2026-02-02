<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $project = $this->route('project');
        $objective = $this->route('objective');

        if(!$project || !$objective){
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
        $project = $this->route('project');

        return [
            'title'         => 'required|string|min:3|max:255',
            'description'   => 'nullable|string|min:3|max:1000',
            'due_date'      => 'required|date|after_or_equal:today',
            'user_id'       => [
                'nullable',
                Rule::exists('memberships', 'user_id')
                    ->where(function ($q) use ($project) {
                        $q->where('model_id', $project->id)
                            ->where('model_type', Project::class);
                    }),
                ]
        ];
    }
}
