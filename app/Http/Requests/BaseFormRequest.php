<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalize a numeric string input to float.
     */
    protected function num(mixed $value): float
    {
        return (float) $value;
    }

    /**
     * Prepare camelCase payloads from form data.
     */
    protected function prepareForValidation(): void
    {
        if ($this->isJson()) {
            return;
        }

        $this->prepareCamelCaseKeys();
    }

    protected function prepareCamelCaseKeys(): void
    {
        $data = $this->all();

        foreach ($data as $key => $value) {
            $snake = Str::snake($key);

            if ($snake !== $key) {
                $data[$snake] = $value;
                unset($data[$key]);
            }
        }

        $this->replace($data);
    }
}
