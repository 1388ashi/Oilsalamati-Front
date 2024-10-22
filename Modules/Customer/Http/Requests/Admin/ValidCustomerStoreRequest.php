<?php

namespace Modules\Customer\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ValidCustomerStoreRequest extends FormRequest
{

	public function rules()
	{
		return [
			'name' => 'nullable|min:3|max:192',
			'description' => 'nullable|min:3|max:192',
			'image' => 'required|image',
			'link' => 'nullable',
			'status' => 'nullable|boolean',
		];
	}

	public function passedValidation()
	{
		$this->merge([
			'status' => $this->has('status')
		]);
	}


	public function authorize()
	{
		return true;
	}
}
