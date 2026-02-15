<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePluginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('plugin')->isOwnedBy(auth()->user());
    }

    public function rules(): array
    {
        $pluginId = $this->route('plugin')->id;
        
        return [
            'name' => 'required|string|max:255|unique:plugins,name,' . $pluginId,
            'description' => 'required|string|max:5000',
            'category_id' => 'required|exists:categories,id',
            'version' => 'required|string|max:50',
            'compatibility' => 'required|string|max:100',
            'license_type' => 'required|string|in:MIT,GPL,Apache,Proprietary',
            'requirements' => 'required|array',
            'requirements.php' => 'required|string',
            'requirements.laravel' => 'required|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048|dimensions:min_width=200,min_height=200',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'A plugin with this name already exists.',
            'category_id.exists' => 'The selected category is invalid.',
            'license_type.in' => 'Please select a valid license type.',
            'logo.dimensions' => 'Logo must be at least 200x200 pixels.',
        ];
    }
}
