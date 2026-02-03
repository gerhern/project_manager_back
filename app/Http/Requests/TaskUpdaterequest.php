<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskUpdaterequest extends FormRequest
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
        $task = $this->route('task');

        return [
            'title'         => [
                Rule::unique('objectives', 'title')->ignore($task->id),
                'required',
                'string',
                'min:3',
                'max:255'
            ],
            'description'   => 'nullable|string|min:3|max:1000',
            'due_date'      => 'required|date|after_or_equal:today',
            'status'        => 'prohibited',
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
