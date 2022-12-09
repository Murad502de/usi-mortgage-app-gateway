<?php

namespace App\Http\Resources\Dictionaries;

use Illuminate\Http\Resources\Json\JsonResource;

class PipelinesDictionaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return array_merge(parent::toArray($request), [
            'stages' => $this->stages
        ]);
    }
}
