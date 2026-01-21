<?php

namespace App\Http\Requests;

use App\Models\Team;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ProjectStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $team = Team::find($this->team_id);

        if(!$team){
            return false;
        }

        return Gate::allows('createProject', $team);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'string|max:255|min:3|required|unique:projects,name',
            'description' => 'nullable|string|max:1000',
            'team_id' => 'required|exists:teams,id'
        ];
    }
}
