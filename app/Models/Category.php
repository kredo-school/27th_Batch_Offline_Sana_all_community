<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    private const LOCALIZED_NAMES = [
        'ja' => [
            'Events' => 'イベント',
            'Meet up' => 'ミートアップ',
            'Sports' => 'スポーツ',
            'Exchange' => '交流',
            'Jobs' => '求人',
            'Art' => '絵',
            'Sell' => '売る',
            'Buy' => '買う',
            'Learn' => '学習',
            'Help' => '助けて！',
            'Advertisement' => '広告',
        ],
        'es' => [
            'Events' => 'Eventos',
            'Meet up' => 'Encuentro',
            'Sports' => 'Deportes',
            'Exchange' => 'Intercambio',
            'Jobs' => 'Empleo',
            'Art' => 'Arte',
            'Sell' => 'Vender',
            'Buy' => 'Comprar',
            'Learn' => 'Aprender',
            'Help' => 'Ayuda',
            'Advertisement' => 'Publicidad',
        ],
        'pt' => [
            'Events' => 'Eventos',
            'Meet up' => 'Encontro',
            'Sports' => 'Desporto',
            'Exchange' => 'Intercâmbio',
            'Jobs' => 'Empregos',
            'Art' => 'Arte',
            'Sell' => 'Vender',
            'Buy' => 'Comprar',
            'Learn' => 'Aprender',
            'Help' => 'Ajuda',
            'Advertisement' => 'Publicidade',
        ],
        'ru' => [
            'Events' => 'Мероприятия',
            'Meet up' => 'Встречи',
            'Sports' => 'Спорт',
            'Exchange' => 'Обмен',
            'Jobs' => 'Работа',
            'Art' => 'Искусство',
            'Sell' => 'Продать',
            'Buy' => 'Купить',
            'Learn' => 'Обучение',
            'Help' => 'Помощь',
            'Advertisement' => 'Реклама',
        ],
        'ar' => [
            'Events' => 'الفعاليات',
            'Meet up' => 'لقاءات',
            'Sports' => 'الرياضة',
            'Exchange' => 'تبادل',
            'Jobs' => 'وظائف',
            'Art' => 'الفن',
            'Sell' => 'بيع',
            'Buy' => 'شراء',
            'Learn' => 'تعلم',
            'Help' => 'مساعدة',
            'Advertisement' => 'إعلان',
        ],
        'ko' => [
            'Events' => '이벤트',
            'Meet up' => '모임',
            'Sports' => '스포츠',
            'Exchange' => '교류',
            'Jobs' => '채용',
            'Art' => '예술',
            'Sell' => '판매',
            'Buy' => '구매',
            'Learn' => '학습',
            'Help' => '도움',
            'Advertisement' => '광고',
        ],
        'zh' => [
            'Events' => '活动',
            'Meet up' => '聚会',
            'Sports' => '运动',
            'Exchange' => '交流',
            'Jobs' => '工作',
            'Art' => '艺术',
            'Sell' => '出售',
            'Buy' => '购买',
            'Learn' => '学习',
            'Help' => '帮助',
            'Advertisement' => '广告',
        ],
        'vi' => [
            'Events' => 'Sự kiện',
            'Meet up' => 'Gặp mặt',
            'Sports' => 'Thể thao',
            'Exchange' => 'Trao đổi',
            'Jobs' => 'Việc làm',
            'Art' => 'Nghệ thuật',
            'Sell' => 'Bán',
            'Buy' => 'Mua',
            'Learn' => 'Học tập',
            'Help' => 'Trợ giúp',
            'Advertisement' => 'Quảng cáo',
        ],
        'th' => [
            'Events' => 'กิจกรรม',
            'Meet up' => 'พบปะ',
            'Sports' => 'กีฬา',
            'Exchange' => 'แลกเปลี่ยน',
            'Jobs' => 'งาน',
            'Art' => 'ศิลปะ',
            'Sell' => 'ขาย',
            'Buy' => 'ซื้อ',
            'Learn' => 'การเรียนรู้',
            'Help' => 'ช่วยเหลือ',
            'Advertisement' => 'โฆษณา',
        ],
    ];

    protected $fillable = ['name', 'description'];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_categories');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function getJapaneseNameAttribute(): string
    {
        $translation = $this->relationLoaded('translations')
            ? $this->translations
                ->where('field', 'name')
                ->firstWhere('target_language', 'ja')?->translated_text
            : $this->translations()
                ->where('target_language', 'ja')
                ->where('field', 'name')
                ->value('translated_text');

        return $translation ?: self::LOCALIZED_NAMES['ja'][$this->name] ?? $this->name;
    }

    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        if ($locale === 'en') {
            return $this->name;
        }

        if ($locale === 'ja') {
            return $this->japanese_name;
        }

        $translation = $this->relationLoaded('translations')
            ? $this->translations
                ->where('field', 'name')
                ->firstWhere('target_language', $locale)?->translated_text
            : $this->translations()
                ->where('target_language', $locale)
                ->where('field', 'name')
                ->value('translated_text');

        return $translation ?: self::LOCALIZED_NAMES[$locale][$this->name] ?? $this->name;
    }
}