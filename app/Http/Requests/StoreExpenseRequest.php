<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreExpenseRequest extends FormRequest
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
        return [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'category' => 'required|string|max:50',
            'expense_date' => 'required|date|before_or_equal:today',
            'project_id' => 'nullable|exists:projects,id',
            'is_billable' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'description.required' => 'De beschrijving is verplicht.',
            'description.max' => 'De beschrijving mag maximaal 255 karakters bevatten.',
            'amount.required' => 'Het bedrag is verplicht.',
            'amount.numeric' => 'Het bedrag moet een geldig nummer zijn.',
            'amount.min' => 'Het bedrag moet groter zijn dan 0.',
            'category.required' => 'De categorie is verplicht.',
            'category.max' => 'De categorie mag maximaal 50 karakters bevatten.',
            'expense_date.required' => 'De uitgave datum is verplicht.',
            'expense_date.date' => 'De uitgave datum moet een geldige datum zijn.',
            'expense_date.before_or_equal' => 'De uitgave datum mag niet in de toekomst liggen.',
            'project_id.exists' => 'Het geselecteerde project bestaat niet.',
            'notes.max' => 'De notities mogen maximaal 1000 karakters bevatten.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'description' => 'beschrijving',
            'amount' => 'bedrag',
            'category' => 'categorie',
            'expense_date' => 'uitgave datum',
            'project_id' => 'project',
            'is_billable' => 'factureerbaar',
            'notes' => 'notities',
        ];
    }
}
