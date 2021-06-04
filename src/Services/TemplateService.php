<?php


namespace App\Services;


interface TemplateService
{
    public static function set($template);

    public static function get(int|string $templateId);
}
