<?php


namespace App\Services\impl;


use App\Services\TemplateService;

class EmailTemplateService implements TemplateService
{

    public static function set($template)
    {
        // TODO: Implement set() method.
    }

    public static function get(int|string $templateId): string
    {
        $template = "<div>";
        $template .= "<p>Dear：{{username}}</p>";
        $template .= "<p>This is your verification code:</p>";
        $template .= "<p><b>{{otp}}</b></p>";
        $template .= "<div>";

        return $template;
    }
}
