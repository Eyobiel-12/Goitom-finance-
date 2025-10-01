<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreInvoiceRequest extends FormRequest
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
            'client_id' => 'required|exists:clients,id',
            'project_id' => 'nullable|exists:projects,id',
            'issue_date' => 'required|date|before_or_equal:today',
            'due_date' => 'nullable|date|after:issue_date',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000',
            'terms' => 'nullable|string|max:1000',
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
            'client_id.required' => 'Een klant is verplicht.',
            'client_id.exists' => 'De geselecteerde klant bestaat niet.',
            'project_id.exists' => 'Het geselecteerde project bestaat niet.',
            'issue_date.required' => 'De factuurdatum is verplicht.',
            'issue_date.date' => 'De factuurdatum moet een geldige datum zijn.',
            'issue_date.before_or_equal' => 'De factuurdatum mag niet in de toekomst liggen.',
            'due_date.required' => 'De vervaldatum is verplicht.',
            'due_date.date' => 'De vervaldatum moet een geldige datum zijn.',
            'due_date.after' => 'De vervaldatum moet na de factuurdatum liggen.',
            'items.required' => 'Er moet minimaal één item toegevoegd worden.',
            'items.min' => 'Er moet minimaal één item toegevoegd worden.',
            'items.*.description.required' => 'De beschrijving is verplicht.',
            'items.*.description.max' => 'De beschrijving mag maximaal 255 karakters bevatten.',
            'items.*.quantity.required' => 'De hoeveelheid is verplicht.',
            'items.*.quantity.min' => 'De hoeveelheid moet groter zijn dan 0.',
            'items.*.unit_price.required' => 'De eenheidsprijs is verplicht.',
            'items.*.unit_price.min' => 'De eenheidsprijs moet groter zijn dan of gelijk aan 0.',
            'tax_rate.required' => 'Het BTW percentage is verplicht.',
            'tax_rate.min' => 'Het BTW percentage moet groter zijn dan of gelijk aan 0.',
            'tax_rate.max' => 'Het BTW percentage mag niet hoger zijn dan 100.',
            'notes.max' => 'De notities mogen maximaal 1000 karakters bevatten.',
            'terms.max' => 'De betalingsvoorwaarden mogen maximaal 1000 karakters bevatten.',
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
            'client_id' => 'klant',
            'project_id' => 'project',
            'issue_date' => 'factuurdatum',
            'due_date' => 'vervaldatum',
            'items' => 'items',
            'items.*.description' => 'beschrijving',
            'items.*.quantity' => 'hoeveelheid',
            'items.*.unit_price' => 'eenheidsprijs',
            'tax_rate' => 'BTW percentage',
            'notes' => 'notities',
            'terms' => 'betalingsvoorwaarden',
        ];
    }
}
