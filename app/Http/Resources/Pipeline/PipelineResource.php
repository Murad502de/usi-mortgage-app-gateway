<?php

namespace App\Http\Resources\Pipeline;

use Illuminate\Http\Resources\Json\JsonResource;

class PipelineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $mortgage = $this->mortgage()->first();

        return array_diff_key(array_merge(parent::toArray($request), [
            'mortgage' => array_merge($mortgage->toArray(), [
                'amo_mortgage_before_applying_stage_ids' => json_decode($mortgage->amo_mortgage_before_applying_stage_ids),
                'amo_mortgage_after_applying_stage_ids'  => json_decode($mortgage->amo_mortgage_after_applying_stage_ids),
            ]),
        ]), ['mortgage_id' => '']);
    }
}
