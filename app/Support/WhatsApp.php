<?php

namespace App\Support;

class WhatsApp
{
    /**
     * Normalise an Indonesian phone number for wa.me: digits only, country
     * code 62 without the trunk 0. Accepts the ways people actually type it:
     * 0812..., 62812..., +62 812..., +62 0812..., 0062 812... and 812....
     */
    public static function number(?string $number): string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);

        return preg_replace(['/^00(?=62)/', '/^620/', '/^0/', '/^8/'], ['', '62', '62', '628'], $digits);
    }

    /**
     * Whether the number looks like a real Indonesian mobile or landline number.
     */
    public static function isValid(?string $number): bool
    {
        return (bool) preg_match('/^62[1-9]\d{7,12}$/', static::number($number));
    }

    /**
     * The site's main number, normalised; an empty string hides the buttons.
     */
    public static function siteNumber(): string
    {
        return static::number(config('site.whatsapp'));
    }

    public static function url(string $number, string $message): string
    {
        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }
}
