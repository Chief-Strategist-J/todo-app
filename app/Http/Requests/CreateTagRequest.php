<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use function App\Helper\errorMsg;

class CreateTagRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
            'version' => 'nullable|integer',
            'follower_count' => 'nullable|integer',
            'usage_count' => 'nullable|integer',
            'related_posts_count' => 'nullable|integer',
            'user_interaction_count' => 'nullable|integer',
            'popularity_score' => 'nullable|numeric',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'image_url' => 'nullable|url',
            'tag_type' => 'nullable|string',
            'content_type' => 'nullable|string',
            'description_vector' => 'nullable|string',
            'meta_description' => 'nullable|string',
            'description' => 'nullable|string',
            'geolocation_data' => 'nullable|array',
            'meta_data' => 'nullable|array',
            'created_by' => 'nullable|integer',
            'parent_id' => 'nullable|integer',
            'last_trend_update' => 'nullable|date',
            'last_used_at' => 'nullable|date',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $errors = $validator->errors()->all();
        throw new HttpResponseException(errorMsg(implode(', ', $errors), 422));
    }
}
