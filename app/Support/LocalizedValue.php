<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Community;
use DateTimeInterface;
use Illuminate\Support\Carbon;

final class LocalizedValue
{
    private const CEBUANO_MONTHS = [
        1 => 'Enero',
        2 => 'Pebrero',
        3 => 'Marso',
        4 => 'Abril',
        5 => 'Mayo',
        6 => 'Hunyo',
        7 => 'Hulyo',
        8 => 'Agosto',
        9 => 'Setyembre',
        10 => 'Oktubre',
        11 => 'Nobyembre',
        12 => 'Disyembre',
    ];

    private const CEBUANO_MONTHS_SHORT = [
        1 => 'Ene',
        2 => 'Peb',
        3 => 'Mar',
        4 => 'Abr',
        5 => 'May',
        6 => 'Hun',
        7 => 'Hul',
        8 => 'Ago',
        9 => 'Set',
        10 => 'Okt',
        11 => 'Nob',
        12 => 'Dis',
    ];

    private const CEBUANO_WEEKDAYS_SHORT = [
        'Dom',
        'Lun',
        'Mar',
        'Miy',
        'Huw',
        'Biy',
        'Sab',
    ];

    public static function dateTime(?DateTimeInterface $date, string $style = 'datetime'): string
    {
        if ($date === null) {
            return '-';
        }

        $locale = app()->getLocale();
        $date = Carbon::instance($date)
            ->timezone(config('app.timezone'))
            ->locale($locale);

        if ($locale === 'ceb') {
            return self::cebuanoDateTime($date, $style);
        }

        $format = match ($style) {
            'date' => 'LL',
            'date-short' => 'L',
            'time' => 'LT',
            'month' => 'MMMM',
            'month-year' => 'MMMM YYYY',
            'month-year-short' => 'MMM YY',
            'weekday-short' => 'ddd',
            'day' => 'D',
            default => 'LLL',
        };

        return $date->isoFormat($format);
    }

    public static function relativeTime(?DateTimeInterface $date): string
    {
        if ($date === null) {
            return '-';
        }

        if (app()->getLocale() === 'ceb') {
            return self::dateTime($date);
        }

        return Carbon::instance($date)
            ->locale(app()->getLocale())
            ->diffForHumans();
    }

    private static function cebuanoDateTime(Carbon $date, string $style): string
    {
        $month = self::CEBUANO_MONTHS[(int) $date->format('n')];
        $shortMonth = self::CEBUANO_MONTHS_SHORT[(int) $date->format('n')];
        $day = $date->format('j');
        $year = $date->format('Y');
        $weekday = self::CEBUANO_WEEKDAYS_SHORT[(int) $date->dayOfWeek];

        return match ($style) {
            'date' => "{$month} {$day}, {$year}",
            'date-short' => $date->format('m/d/Y'),
            'time' => $date->format('H:i'),
            'month' => $month,
            'month-year' => "{$month} {$year}",
            'month-year-short' => "{$shortMonth} {$date->format('y')}",
            'weekday-short' => $weekday,
            'day' => $day,
            default => "{$month} {$day}, {$year} ".$date->format('H:i'),
        };
    }

    public static function notificationType(?string $value): string
    {
        $key = match ($value) {
            'general' => 'General',
            'maintenance' => 'Maintenance',
            'update' => 'Update',
            'admin_contact' => 'Admin contact',
            'event_comment' => 'Event comment',
            'event_join_request' => 'Event join request',
            'event_join_approved' => 'Event join approved',
            'help_application' => 'New help application',
            'help_application_accepted' => 'Help application accepted',
            'help_application_rejected' => 'Help application rejected',
            'community_join_request' => 'Community join request',
            'community_join_approved' => 'Community join approved',
            'community_join_declined' => 'Community join declined',
            'community_invite' => 'Community invitation',
            'community_moderator_added' => 'Moderator added',
            'community_ownership_transferred' => 'Ownership transferred',
            'community_message' => 'Community message',
            'community_approval' => 'Community approval',
            'event_cancelled' => 'Event cancelled',
            default => null,
        };

        if ($key !== null) {
            return __($key);
        }

        return $value ? __(self::humanize($value)) : '-';
    }

    public static function status(?string $value): string
    {
        $key = match ($value) {
            'draft' => 'Draft',
            'published' => 'Published',
            'cancelled' => 'Cancelled',
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'declined' => 'Declined',
            'active' => 'Active',
            'inactive' => 'Inactive',
            'invited' => 'Invited',
            'upcoming' => 'Upcoming',
            'past' => 'Past',
            'joined' => 'Joined',
            default => null,
        };

        return $key !== null ? __($key) : ($value ? __(self::humanize($value)) : '-');
    }

    public static function role(?string $value): string
    {
        $key = match ($value) {
            'admin' => 'Admin',
            'user' => 'User',
            'organizer' => 'Organizer',
            'owner' => 'Owner',
            'moderator' => 'Moderator',
            'member' => 'Member',
            default => null,
        };

        return $key !== null ? __($key) : ($value ? __(self::humanize($value)) : '-');
    }

    public static function visibility(?string $value): string
    {
        $key = match ($value) {
            'public' => 'Public',
            'private' => 'Private',
            'approval' => 'Approval required',
            'members' => 'Logged-in users',
            'followers' => 'Followers only',
            'invite_only' => 'Only invited people',
            default => null,
        };

        return $key !== null ? __($key) : ($value ? __(self::humanize($value)) : '-');
    }

    public static function approval(bool|int|null $value): string
    {
        return $value ? __('Yes') : __('No');
    }

    public static function language(?string $value): string
    {
        $key = match (strtolower((string) $value)) {
            'en' => 'English',
            'ja' => 'Japanese',
            'ru' => 'Russian',
            'ar' => 'Arabic',
            'ko' => 'Korean',
            'pt' => 'Portuguese',
            'es' => 'Spanish',
            'zh' => 'Chinese',
            'vi' => 'Vietnamese',
            'th' => 'Thai',
            'ceb' => 'Cebuano',
            default => null,
        };

        return $key !== null ? __($key) : ($value ? strtoupper($value) : '-');
    }

    public static function category(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '-';
        }

        $categoryName = Category::localizeName($value);

        return Community::getLocalizedCategoryName($categoryName);
    }

    public static function reviewRating(int $rating): string
    {
        $key = match ($rating) {
            5 => '5 - Very good',
            4 => '4 - Good',
            3 => '3 - Average',
            2 => '2 - Poor',
            1 => '1 - Very poor',
            default => null,
        };

        return $key !== null ? __($key) : (string) $rating;
    }

    private static function humanize(string $value): string
    {
        return str_replace('_', ' ', ucfirst(strtolower($value)));
    }
}
