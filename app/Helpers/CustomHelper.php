<?php
use Illuminate\Support\Facades\Mail;

if (!function_exists('generateUniqueAlphaNumericValue')) {
    function generateUniqueAlphaNumericValue($length = 10) {
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $number = random_int(0, 36);
            $character = base_convert($number, 10, 36);
            $random_string .= $character;
        }
        return strtoupper($random_string);
    }
}

if (!function_exists('getInitials')) {
     function getInitials($fullName) {
            return collect(explode(' ', $fullName))
                ->map(fn($name) => Str::upper(Str::substr($name, 0, 1)))
                ->join('');
        }
}



function SendMail($data, $ccEmails = [], $bccEmails = [])
{
    $mail_from = isset($data['from']) && !empty($data['from']) ? $data['from'] : 'admin@foxandmandal.co.in';

    // Send mail
    Mail::send($data['blade_file'], $data, function ($message) use ($data, $mail_from, $ccEmails, $bccEmails) {
        $message->to($data['email'], $data['name'])
                ->subject($data['subject'])
                ->from($mail_from, env('APP_NAME'));

        if (!empty($ccEmails)) {
            $message->cc($ccEmails);
        }

        if (!empty($bccEmails)) {
            $message->bcc($bccEmails);
        }
    });
}
