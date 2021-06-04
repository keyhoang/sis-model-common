<?php


namespace App\Services\impl;


use App\Services\TemplateService;

class SmsTemplateService implements TemplateService
{

    public static function set($template)
    {
        // TODO: Implement set() method.
    }

    public static function get(int|string $templateId): string
    {
        return "Sms template body";
    }
}
