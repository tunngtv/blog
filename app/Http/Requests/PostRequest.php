<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PostRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required',
            'content' => 'required',
            'author_id' => ['required', 'exists:users,id'],
            'category_id' => ['required', 'exists:categories,id'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title is required',
            'content.required' => 'The content is required',
            'author_id.required' => 'The author is required',
            'author_id.exists' => 'The author doest not exist',
            'category_id.required' => 'The category is required',
            'category_id.exists' => 'The category doest not exist',
        ];
    }

    /**
     * Create an error message summary from the validation errors.
     *
     * @param Validator $validator
     * @return string
     */
    protected static function summarize(Validator $validator): string
    {
        $messages = $validator->errors()->all();

        if (!count($messages) || !is_string($messages[0])) {
            return $validator->getTranslator()->get('The given data was invalid.');
        }

        $message = array_shift($messages);

        if ($count = count($messages)) {
            $pluralized = $count === 1 ? 'error' : 'errors';

            $message .= ' ' . $validator->getTranslator()->choice("(and :count more $pluralized)", $count, compact('count'));
        }

        return $message;
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            $response = response()->json([
                'status' => false,
                'message' => $this->summarize($validator),
                'errors' => $validator->errors()
            ], 422);

            throw new HttpResponseException($response);
        }

        parent::failedValidation($validator);
    }
}
