<?php

class SWN_SMS
{

    public static $username = "mecafe";
    public static $password = "28fhSD_er@9a";


    public static function send_sms($user_id, $sms_text)
    {
        // try to get user's phone number

        $user_phone_number = self::get_user_phone_number($user_id);

        if ($user_phone_number === false) {
            // log : user does not have a phone number
            return;
        }

        // get sms text
        $text = $sms_text;

        $parameters = [
            "phone-number" => $user_phone_number,
            "sms-text" => $text,
        ];

        $response = self::send_sms_with_text($parameters);

        var_dump($response);

        // log sms result
    }


    public static function get_user_phone_number($user_id)
    {
        $phone_number = get_user_meta($user_id, 'digits_phone', true);
        if (! $phone_number) {
            return false;
        }

        $phone_number =  preg_replace('/^\+98/', '0', $phone_number);

        return $phone_number;
    }

    public static function send_pattern_sms($user_id, $pattern_code, $variables)
    {

        // try to get user's phone number

        $user_phone_number = self::get_user_phone_number($user_id);

        if ($user_phone_number === false) {
            // log : user does not have a phone number
            return;
        }

        // Send SMS using the dynamic message
        $parameters = [
            "pattern_code" => $pattern_code,
            "phone_number" => $user_phone_number,
            "variable_values" => $variables,
        ];



        $response = self::send_sms_by_pattern($parameters);

        if (is_numeric($response)) {
            $data = [
                "status" => "success",
                "messages" => [
                    "با موفقیت ارسال شد",
                    "کد پیگیری پیام : $response"
                ],
            ];
        } else {
            $data = [
                "status" => "fail",
                "messages" => [
                    "ارسال پیام با خطا مواجه شد.",
                    $response,
                ],
            ];
        }

        // var_dump($data);

        return true;
    }

    public static function send_text_sms($phone_number, $text)
    {

        $is_phone_number_valid = self::validate_phone_numbers($phone_number);

        if (! $is_phone_number_valid) {
            // phone number is invalid
            var_dump("phone number invalid");
            return false;
        }

        // Send SMS using the dynamic message
        $parameters = [
            "phone-number" => $phone_number,
            "sms-text" => $text,
        ];

        $response = self::send_sms_with_text($parameters);

        if (is_numeric($response)) {
            $data = [
                "status" => "success",
                "messages" => [
                    "با موفقیت ارسال شد",
                    "کد پیگیری پیام : $response"
                ],
            ];
        } else {
            $data = [
                "status" => "fail",
                "messages" => [
                    "ارسال پیام با خطا مواجه شد.",
                    $response,
                ],
            ];
        }

        var_dump($data);

        return true;
    }

    public static function send_sms_with_text($data)
    {

        $phone_number = array($data['phone-number']);
        $params = array(
            'uname' => self::$username,
            'pass' => self::$password,
            'from' => "+9810004223",
            'message' => $data['sms-text'],
            'to' => json_encode($phone_number),
            'op' => 'send'
        );

        $handler = curl_init();
        $curl_options = array(
            CURLOPT_URL => "https://ippanel.com/services.jspd",
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($handler, $curl_options);

        // dump($data['phone-number']);
        // dd($params['to']);
        // die("temporary disabled");
        $response = curl_exec($handler);
        curl_close($handler);

        $response = json_decode($response);

        return $response;
    }

    public static function send_sms_by_pattern($params)
    {

        // these values are constant
        $from = "+985000125475";

        // these values are passed to the function in $params 
        $pattern_code = $params['pattern_code'];
        $to = array($params['phone_number']);
        $input_data = $params['variable_values'];

        $url = "https://ippanel.com/patterns/pattern?username=" . self::$username . "&password=" . urlencode(self::$password,) . "&from=$from&to=" . json_encode($to) . "&input_data=" . urlencode(json_encode($input_data)) . "&pattern_code=$pattern_code";
        $handler = curl_init();
        $curl_options = array(
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $input_data,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($handler, $curl_options);
        $response = curl_exec($handler);
        curl_close($handler);

        return $response;
    }

    public static function validate_phone_numbers($phone_numbers)
    {
        $mobile_number_regex = "/^9[01239]{1}[0-9]{8}$/";
        $fail = 0;
        $messages = [];

        $phone_list = preg_split('/\r\n|[\r\n]/', $phone_numbers);

        foreach ($phone_list as $key => $number) {
            $p = strtr($phone_list[$key], array('۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9'));

            $phone_list[$key] =  ltrim($p, '0');

            if (!preg_match($mobile_number_regex, $phone_list[$key])) {
                $messages[] = "شماره موبایل " . "0" . $phone_list[$key] . " نادرست است.";
                $fail = 1;
            }
        }

        if ($fail) {
            return false;
        }
        return true;

        // return array(
        //     "phone_list" => $phone_list,
        //     "messages" => $messages,
        //     "is_failed" => $fail,
        // );
    }
}
