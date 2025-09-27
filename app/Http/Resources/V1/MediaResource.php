<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{

    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'model_type'        => $this->model_type,
            'model_id'          => $this->model_id,
            'collection_name'   => $this->collection_name,
            'name'              => $this->name,
            'file_name'         => $this->file_name,
            'mime_type'         => $this->mime_type,
            'disk'              => $this->disk,
            'size'              => $this->size,
            'manipulations'     => $this->manipulations,
            'custom_properties' => $this->custom_properties,
            'responsive_images' => $this->responsive_images,
            'order_column'      => $this->order_column,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            'urls'              => [
                'original'  => $this->getUrl(),
                'thumbnail' => $this->getUrl('thumbnail'),
                'banner'    => $this->getUrl('banner'),
                'icon'      => $this->getUrl('icon'),
            ]
        ];
    }
}
