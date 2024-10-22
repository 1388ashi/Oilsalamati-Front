<?php

namespace Modules\Core\Helpers;

use Illuminate\Support\Facades\Log;
use Shetabit\Shopit\Modules\Core\Classes\CoreSettings;
use Shetabit\Shopit\Modules\Core\Helpers\Sms as BaseSms;

class Sms extends BaseSms
{
    public static function execute_manually_to_kavenegar($data = null)
    {
        $coreSetting = app(CoreSettings::class);
        $url = "https://api.kavenegar.com/v1/" . $coreSetting->get('sms.api_key') . "/verify/lookup.json/";

        $headers = [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'charset: utf-8'
        ];
        $fields_string = "";
        if (!is_null($data)) {
            $fields_string = http_build_query($data);
        }
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $fields_string);

        $response = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        $curl_errno = curl_errno($handle);
        $curl_error = curl_error($handle);
        if ($curl_errno) {
            throw new \Shetabit\Shopit\Modules\Sms\Exceptions\HttpException($curl_error, $curl_errno);
        }
        $json_response = json_decode($response);
        if ($code != 200 && is_null($json_response)) {
            throw new \HttpException("Request have errors", $code);
        } else {
            $json_return = $json_response->return;
            if ($json_return->status != 200) {
                Log::debug('', [
                    debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4)
                ]);
                throw new \Shetabit\Shopit\Modules\Sms\Exceptions\ApiException($json_return->message . var_export($data, true),
                    $json_return->status);
            }
            return [
                'status' => $code
            ];
        }

    }

    public static function shopit_neworder($pattern, $data, $mobile)
    {
        $smsPatternManually = [
            "receptor" => $mobile,
            "token" => $data['order_id'],
            "token2" => null,
            "token3" => null,
            "template" => $pattern,
            "type" => null,
            "token10" => ($data['full_name'] ?? null),
            "token20" => ($data['status'] ?? null),
        ];

        return self::execute_manually_to_kavenegar($smsPatternManually);
    }


    public static function send_as_group($messages)
    {
        $receptor = [];
        $sender = [];
        $message = [];
        foreach ($messages as $message) {

        }
    }

    public static function execute_manually_to_kavenegar_as_group($data = null)
    {
        $coreSetting = app(CoreSettings::class);
        $url = "https://api.kavenegar.com/v1/" . $coreSetting->get('sms.api_key') . "/sms/sendarray.json";

        $headers = [
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded',
            'charset: utf-8'
        ];
        $fields_string = "";
        if (!is_null($data)) {
            $fields_string = http_build_query($data);
        }
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $fields_string);

        $response = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        $curl_errno = curl_errno($handle);
        $curl_error = curl_error($handle);
        if ($curl_errno) {
            throw new \Shetabit\Shopit\Modules\Sms\Exceptions\HttpException($curl_error, $curl_errno);
        }
        $json_response = json_decode($response);
        if ($code != 200 && is_null($json_response)) {
            throw new \HttpException("Request have errors", $code);
        } else {
            $json_return = $json_response->return;
            if ($json_return->status != 200) {
                Log::debug('', [
                    debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 4)
                ]);
                throw new \Shetabit\Shopit\Modules\Sms\Exceptions\ApiException($json_return->message . var_export($data, true),
                    $json_return->status);
            }
            return [
                'status' => $code
            ];
        }

    }

}
