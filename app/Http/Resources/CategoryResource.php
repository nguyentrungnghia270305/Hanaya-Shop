<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request)
    {
        $imageUrl = null;
        if (!empty($this->image)) {
            $imageUrl = url('storage/' . ltrim($this->image, '/'));
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'status' => $this->status,
            'image' => $this->image,
            'image_url' => $imageUrl,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
