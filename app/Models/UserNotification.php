<?php

namespace App\Models;

use App\Support\LocalizedValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'notifications';

    // ヘッダーのコミュニティ通知欄に出す種類（ベルの一般通知からは外す）
    public const COMMUNITY_TYPES = [
        'community_join_request',
        'community_join_approved',
        'community_join_declined',
        'community_invite',
        'community_moderator_added',
        'community_ownership_transferred',
    ];

    protected $fillable = ['user_id', 'recipient_label', 'sender_name', 'type', 'title', 'message', 'related_type', 'related_id', 'read_at'];

    public function scopeCommunityFilter(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('type', 'like', '%community%')
                ->orWhereIn('type', self::COMMUNITY_TYPES);
        })->where('type', '!=', 'community_message');
    }

    public function scopeNotCommunityFilter(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('type', 'not like', '%community%')
                ->whereNotIn('type', self::COMMUNITY_TYPES);
        });
    }

    public function scopeCommunityHeaderFilter(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where(function (Builder $query): void {
                $query->where('type', 'like', '%community%')
                    ->orWhereIn('type', self::COMMUNITY_TYPES);
            })->orWhere(function (Builder $query): void {
                $query->where('related_type', Community::class)
                    ->whereNotNull('related_id');
            })->orWhere(function (Builder $query): void {
                $query->where('related_type', Event::class)
                    ->whereIn(
                        'related_id',
                        Event::withTrashed()->select('id')->whereNotNull('community_id'),
                    );
            });
        });
    }

    public function scopeNotCommunityHeaderFilter(Builder $query): Builder
    {
        return $query->notCommunityFilter()
            ->where(function (Builder $query): void {
                $query->whereNull('related_type')
                    ->orWhere(function (Builder $query): void {
                        $query->where('related_type', '!=', Community::class)
                            ->where(function (Builder $query): void {
                                $query->where('related_type', '!=', Event::class)
                                    ->orWhereNotIn(
                                        'related_id',
                                        Event::withTrashed()->select('id')->whereNotNull('community_id'),
                                    );
                            });
                    });
            });
    }

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related()
    {
        return $this->morphTo();
    }

    public function getRecipientNameAttribute(): ?string
    {
        return $this->recipient_label ?: $this->user?->username;
    }

    public function getTypeLabelAttribute(): string
    {
        return LocalizedValue::notificationType($this->type);
    }

    public function getLocalizedTitleAttribute(): ?string
    {
        $generatedTitles = [
            'help_application' => ['New help application', 'New help application'],
            'help_application_accepted' => ['Help application accepted', 'Help application accepted'],
            'help_application_rejected' => ['Help application rejected', 'Help application rejected'],
            'event_comment' => ['New event comment', 'Event comment'],
            'event_join_request' => ['Event registration request', 'Event join request'],
            'event_joined' => ['New participant', 'Event join approved'],
            'event_join_approved' => ['Event registration approved', 'Event join approved'],
            'event_join_rejected' => ['Event registration declined', 'Declined'],
            'participant_cancelled' => ['Participant cancelled', 'Participant cancelled'],
            'event_cancelled' => ['Event cancelled', 'Event cancelled'],
            'event_updated' => ['Event updated', 'Update'],
            'community_join_request' => ['Community join request', 'Community join request'],
            'community_join_approved' => ['Community join request approved', 'Community join approved'],
            'community_join_declined' => ['Community join request declined', 'Community join declined'],
            'community_invite' => ['Community invitation', 'Community invitation'],
            'community_moderator_added' => ['You are now a moderator', 'Moderator added'],
            'community_ownership_transferred' => ['You are now the community owner', 'Ownership transferred'],
            'community_message' => ['New community message', 'Community message'],
        ];

        [$sourceTitle, $localizedKey] = $generatedTitles[$this->type] ?? [$this->title, null];

        return $this->title === $sourceTitle && $localizedKey !== null
            ? (app()->getLocale() === 'en' ? $sourceTitle : __($localizedKey))
            : $this->title;
    }

    public function getLocalizedMessageAttribute(): ?string
    {
        $helpTitle = $this->related?->title;

        return match ($this->type) {
            'help_application' => __(':name applied to your help ":help".', [
                'name' => $this->sender_name,
                'help' => $helpTitle,
            ]),
            'help_application_accepted' => __('Your application for ":help" was accepted.', [
                'help' => $helpTitle,
            ]),
            'help_application_rejected' => __('Your application for ":help" was rejected.', [
                'help' => $helpTitle,
            ]),
            'event_cancelled' => $this->message,
            default => $this->message,
        };
    }
}