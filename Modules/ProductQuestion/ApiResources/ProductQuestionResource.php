<?php

namespace Modules\ProductQuestion\ApiResources;

use Illuminate\Http\Resources\Json\JsonResource;
use Shetabit\Shopit\Modules\Customer\ApiResources\SafeCustomerResource;

// اطلاعات حساس تو فرانت ندیم
class ProductQuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'answers' => $this->answers,
            'product_id' => $this->product_id,
            'created_at' => $this->created_at,
            'creator' => new SafeCustomerResource($this->whenLoaded('creator'), $this->show_customer_name)
        ];
    }
}
