<?php

namespace App\Http\Requests;

use App\Rules\FutureDate;
use App\Rules\SlugFormat;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     *
     * This method:
     * - Auto-generates a slug if not provided.
     * - Cleans up tags only if sent.
     * - Generates or cleans up keywords only if provided (or meta_description is used as fallback).
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug from title if not explicitly sent
        if (!$this->filled('slug') && $this->filled('title')) {
            $this->merge([
                'slug' => Str::slug($this->input('title')),
            ]);
        }

        // Clean tags only if present
        if ($this->filled('tags')) {
            $rawTags = $this->input('tags');

            $tags = is_string($rawTags)
                ? collect(explode(',', $rawTags))
                : collect($rawTags);

            $tagsArray = $tags
                ->filter()
                ->map('trim')
                ->values()
                ->toArray();

            $this->merge(['tags' => $tagsArray]);
        }

        // Process keywords only if provided (or fallback to meta_description)
        if ($this->filled('keywords') || $this->filled('meta_description')) {
            $rawKeywords = $this->input('keywords');

            if (is_string($rawKeywords)) {
                $source = explode(' ', $rawKeywords);
            } elseif (is_array($rawKeywords)) {
                $source = $rawKeywords;
            } else {
                $source = explode(' ', $this->input('meta_description', ''));
            }

            $keywordsArray = collect($source)
                ->filter()
                ->map('trim')
                ->slice(0, 10)
                ->values()
                ->toArray();

            $this->merge(['keywords' => $keywordsArray]);
        }
    }


    /**
     * Define validation rules.
     * Use 'sometimes' to allow partial updates.
     * @return array{body: string[], is_published: string[], keywords: string[], keywords.*: string[], meta_description: string[], published_date: array<FutureDate|string>, slug: array<SlugFormat|string|\Illuminate\Validation\Rules\Unique>, tags: string[], tags.*: string[], title: string[]}
     */
    public function rules(): array
    {
        $postId = $this->route('post')->id;

        return [
            'title'            => ['sometimes', 'string', 'max:255'],
            'slug'             => [
                'sometimes', 'string', 'max:255',
                Rule::unique('posts', 'slug')->ignore($postId),
                new SlugFormat,
            ],
            'body'             => ['sometimes', 'string'],
            'is_published'     => ['sometimes', 'boolean'],
            'published_date'   => ['nullable', 'date', new FutureDate],
            'meta_description' => ['nullable', 'string', 'max:160'],
            'tags'             => ['nullable', 'array'],
            'tags.*'           => ['string', 'max:50'],
            'keywords'         => ['nullable', 'array', 'max:10'],
            'keywords.*'       => ['string'],
        ];
    }

    /**
     * Human-friendly attribute names for error messages.
     * @return array{body: string, is_published: string, keywords: string, meta_description: string, published_date: string, slug: string, tags: string, title: string}
     */
    public function attributes(): array
    {
        return [
            'title'            => 'Title',
            'slug'             => 'Slug',
            'body'             => 'Content',
            'is_published'     => 'Publishing Status',
            'published_date'   => 'Publish Date',
            'meta_description' => 'Meta Description',
            'tags'             => 'Tags',
            'keywords'         => 'Keywords',
        ];
    }

    /**
     * Custom validation messages.
     * @return array{body.string: string, is_published.boolean: string, keywords.*.string: string, keywords.array: string, keywords.max: string, meta_description.max: string, published_date.date: string, slug.unique: string, tags.*.max: string, tags.array: string, title.max: string, title.required: string}
     */
    public function messages(): array
    {
        return [
            'title.required'           => 'The title field is required.',
            'title.max'                => 'The title may not be greater than :max characters.',
            'slug.unique'              => 'This slug is already taken.',
            'body.string'              => 'The content must be a string.',
            'is_published.boolean'     => 'The publishing status must be true or false.',
            'published_date.date'      => 'The publish date must be a valid date.',
            'meta_description.max'     => 'The meta description may not exceed :max characters.',
            'tags.array'               => 'The tags must be an array.',
            'tags.*.max'               => 'Each tag may not exceed :max characters.',
            'keywords.array'           => 'The keywords must be an array.',
            'keywords.max'             => 'The keywords may not have more than :max items.',
            'keywords.*.string'        => 'Each keyword must be a string.',
        ];
    }

    /**
     * Process clean-up after validation passes.
     * @return void
     */
    protected function passedValidation(): void
    {
        if ($this->filled('keywords')) {
            $cleaned = collect($this->keywords)
                ->map(fn($word) => is_string($word) ? strtolower($word) : null)
                ->filter()
                ->values()
                ->toArray();

            $this->merge(['keywords' => $cleaned]);
        }
    }


    /**
     * Handle validation failure with custom JSON response.
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
}
