<?php

namespace App\Support;

final class SmsSegments
{
    public static function count(string $text): int
    {
        $basic = '@$ !"#%&\'()*+,-./0123456789:;<=>?ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz'."\u{a3}\u{a5}\u{e8}\u{e9}\u{f9}\u{ec}\u{f2}\u{c7}\u{d8}\u{f8}\u{c5}\u{e5}\u{394}\u{3a6}\u{393}\u{39b}\u{3a9}\u{3a0}\u{3a8}\u{3a3}\u{398}\u{39e}\u{c6}\u{e6}\u{df}\u{c9}\u{a4}\u{a1}\u{c4}\u{d6}\u{d1}\u{dc}\u{a7}\u{bf}\u{e4}\u{f6}\u{f1}\u{fc}\u{e0}\n\r\x1b";
        $extended = "^{}\\[~]|\u{20ac}\f";
        $length = 0;
        foreach (preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) as $char) {
            if (str_contains($basic, $char)) { $length++; }
            elseif (str_contains($extended, $char)) { $length += 2; }
            else {
                $units = strlen(mb_convert_encoding($text, 'UTF-16BE', 'UTF-8')) / 2;
                return max(1, (int) ($units <= 70 ? 1 : ceil($units / 67)));
            }
        }
        return max(1, $length <= 160 ? 1 : (int) ceil($length / 153));
    }

    public static function recipients(string $text): array
    {
        $numbers = [];
        foreach (preg_split('/[\s,;]+/', trim($text), -1, PREG_SPLIT_NO_EMPTY) as $number) {
            $number = ltrim($number, '+');
            if (preg_match('/^234[789][01][0-9]{8}$/', $number)) { $number = '0'.substr($number, 3); }
            if (!preg_match('/^0[789][01][0-9]{8}$/', $number)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['recipients' => 'Enter valid Nigerian mobile numbers separated by commas or new lines.']);
            }
            $numbers[$number] = $number;
        }
        if (!$numbers) { throw \Illuminate\Validation\ValidationException::withMessages(['recipients' => 'Enter at least one valid recipient.']); }
        return array_values($numbers);
    }
}
