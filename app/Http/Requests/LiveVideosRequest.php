<?php

namespace App\Http\Requests;

class LiveVideosRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        //dd($this->all()); // 입력값 디버깅

        return [
            'title' => 'required',
            //'description' => 'required|min:10',
            'scheduledStartTime' => 'required',
            'cdnResolution' => 'required',
            'cdnFrameRate' => 'required',
        ];
    }
}
