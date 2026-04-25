<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class CreateLinkRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            "url" => "required|url",
            "slug" => "nullable|alpha_dash|min:4|max:16",
            "expires_at" => "nullable|date_format:Y-m-d H:i:s|after:now",
        ];
    }
}
