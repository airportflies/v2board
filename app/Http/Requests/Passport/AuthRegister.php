<?php

namespace App\Http\Requests\Passport;

use Illuminate\Foundation\Http\FormRequest;

class AuthRegister extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email:strict',
            'password' => 'required|min:8'
        ];
    }

    public function messages()
    {
        return [
            'email.required' => __('Email can not be empty'),
            'email.email' => __('Email format is incorrect'),
            'password.required' => __('Password can not be empty'),
            'password.min' => __('Password must be greater than 8 digits')
        ];
    }

    public function getClientRealIp(): string
    {
        // Check for Cloudflare's CF-Connecting-IP header
        if (request()->hasHeader('CF-Connecting-IP')) {
            return request()->header('CF-Connecting-IP');
        }

        // Check for X-Forwarded-For header
        if (request()->hasHeader('X-Forwarded-For')) {
            // X-Forwarded-For may contain multiple IPs, take the first one
            $ipList = explode(',', request()->header('X-Forwarded-For'));
            return trim($ipList[0]);
        }

        // Fallback to REMOTE_ADDR if no proxy headers are found
        return request()->getClientIp();
    }
}
