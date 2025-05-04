<?php

namespace App\Http\Requests;

use App\Rules\FutureDate;
use App\Rules\SlugFormat;
use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare date before send it to validate
     * @return void
     */
    protected function prepareForValidation()
    {
        if (!$this->filled('slug') && $this->filled('title')) {
            $this->merge([
                'slug' => Str::slug($this->input('title'))
            ]);
        }
        $tags = collect(explode(',', $this->input('tags', '')))
            ->filter()->map('trim')->values();
        $keywords = collect(explode(' ', $this->input('meta_description', '')))
            ->filter()->map('trim')->take(10)->values();
        $this->merge(compact('tags', 'keywords'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'             => ['required', 'string', 'max:255'],
            'slug'              => ['required', 'string', 'max:255', 'unique:posts,slug', new SlugFormat],
            'body'              => ['required', 'string'],
            'is_published'      => ['boolean'],
            'published_date'    => ['nullable', 'date', new FutureDate],
            'meta_description'  => ['nullable', 'string', 'max:160'],
            'tags'              => ['nullable', 'array'],
            'tags.*'            => ['string', 'max:50'],
            'keywords'          => ['nullable', 'array', 'max:10'],
            'keywords.*'        => ['string']
        ];
    }



    /**
     * Summary of attributes
     * @return array{body: string, is_published: string, keywords: string, meta_description: string, publish_date: string, slug: string, tags: string, title: string}
     */
    public function attributes(){
        return [
            'title'             => 'Title',
            'slug'              => 'Slug',
            'body'              => 'Content',
            'is_published'      => 'Publishing Status',
            'publish_date'      => 'Publish Date',
            'meta_description'  => 'Meta Description',
            'tags'              => 'Tags',
            'keywords'          => 'Keywords',
        ];
    }


    /**
     * Summary of messages
     * @return array{body.required: string, is_published.boolean: string, keywords.string: string, meta_description.max: string, publish_date.date: string, publish_date.required_if: string, slug.max: string, slug.unique: string, title.max: string, title.required: string}
     */
    public function messages(){
        return [
            'title.required'            => 'The title field is required.',
            'title.max'                 => 'The title field may not be greater than :max characters.',
            'slug.unique'               => 'This slug (:input) is already in use. Please choose another one or leave it blank for automatic generation.',
            'slug.max'                  => 'The slug field may not be greater than :max characters.',
            'body.required'             => 'The article content is required.',
            'is_published.boolean'      => 'The is_published field must be true or false.',
            'publish_date.date'         => 'The publish date must be a valid date.',
            'meta_description.max'      => 'The meta description may not be greater than :max characters.',
            'keywords.string'           => 'The keywords field must be a string.',
            'publish_date.required_if'  => 'Since you marked the article as "published", the publish date must be specified.',
        ];
    }


    /**
     * Summary of failedValidation
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Validation\ValidationException
     * @return never
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $response = response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);

        throw new \Illuminate\Validation\ValidationException($validator, $response);
    }

    /**
     * Summary of passedValidation
     * @return void
     */
    protected function passedValidation()
    {
        // Ensure all keywords are strings and convert to lowercase
        $keywords = collect($this->keywords)
            ->filter()
            ->map(function ($word) {
                return is_string($word) ? strtolower($word) : '';
            })
            ->filter()   // Remove any empty strings
            ->values();

        // Clean tags by removing special characters and trimming
        $tags = collect($this->tags)
            ->map(function ($tag) {
                $tag = is_string($tag) ? $tag : '';
                return preg_replace('/[^\p{L}\p{N}\s]/u', '', $tag);
            })
            ->filter()
            ->map('trim')
            ->values();

        $this->merge([
            'keywords'  => $keywords,
            'tags'      => $tags,
        ]);
    }
}
