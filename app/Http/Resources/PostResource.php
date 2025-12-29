<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        $imageUrl = null;
        if (!empty($this->featured_image)) {
            $imageUrl = url('storage/' . ltrim($this->featured_image, '/'));
        }

        $tags = $this->tags;
        if (is_string($tags) && strlen($tags) > 0) {
            $tags = array_map('trim', explode(',', $tags));
        } elseif (is_null($tags)) {
            $tags = [];
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'body' => $this->body,
            'slug' => $this->slug,
            'author_id' => $this->author_id,
            'status' => $this->status,
            'tags' => $tags,
            'featured_image' => $this->featured_image,
            'featured_image_url' => $imageUrl,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
