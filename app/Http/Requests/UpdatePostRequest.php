<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Rules\FutureDate;
use App\Rules\SlugFormat;

class UpdatePostRequest extends StorePostRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $postId = $this->route('post')->id;

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('posts', 'slug')
                    ->ignore($postId),
                new SlugFormat
            ],
            'body'              => ['sometimes', 'string'],
            'is_published'      => ['sometimes', 'boolean'],
            'publish_date'      => ['nullable', 'date', new FutureDate],
            'meta_description'  => ['nullable', 'string', 'max:160'],
            'tags'              => ['nullable', 'array'],
            'tags.*'            => ['string', 'max:50'],
            'keywords'          => ['nullable', 'array', 'max:10'],
            'keywords.*'        => ['string'],
        ];
    }
}
