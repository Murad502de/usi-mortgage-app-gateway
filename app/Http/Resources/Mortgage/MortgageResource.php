<?php

namespace App\Http\Resources\Mortgage;

use Illuminate\Http\Resources\Json\JsonResource;

class MortgageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $pipelines = [];

        foreach ($this->pipelines as $pipeline) {
            $pipelines[] = array_diff_key($pipeline->toArray(), ['mortgage_id' => '']);
        }

        return array_merge(parent::toArray($request), [
            'amo_mortgage_before_applying_stage_ids' => json_decode($this->amo_mortgage_before_applying_stage_ids),
            'amo_mortgage_after_applying_stage_ids'  => json_decode($this->amo_mortgage_after_applying_stage_ids),
            'pipelines'                              => $pipelines,
        ]);
    }
}
