<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Community;
use App\Models\Contact;
use App\Models\Event;
use App\Models\EventReview;
use App\Models\UserNotification;
use App\Support\LanguageFileWriter;
use App\Support\LocalizedValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_change_preferred_language(): void
    {
        $user = User::factory()->create(['preferred_language' => 'en']);

        $this->actingAs($user)
            ->post(route('language.update'), ['locale' => 'ja'])
            ->assertRedirect();

        $this->assertSame('ja', $user->fresh()->preferred_language);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk();

        $this->assertSame('ja', App::getLocale());
    }

    public function test_language_switch_rejects_unsupported_locale(): void
    {
        $user = User::factory()->create(['preferred_language' => 'en']);

        $this->actingAs($user)
            ->from(route('home'))
            ->post(route('language.update'), ['locale' => 'fr'])
            ->assertSessionHasErrors('locale');

        $this->assertSame('en', $user->fresh()->preferred_language);
    }

    public function test_user_can_select_each_supported_locale(): void
    {
        $user = User::factory()->create(['preferred_language' => 'en']);

        foreach (array_keys(config('app.supported_locales')) as $locale) {
            $this->actingAs($user)
                ->post(route('language.update'), ['locale' => $locale])
                ->assertRedirect();

            $this->assertSame($locale, $user->fresh()->preferred_language);

            $this->actingAs($user)
                ->get(route('home'))
                ->assertOk()
                ->assertSee('Sana ALL');

            $this->assertSame($locale, App::getLocale());
        }
    }

    public function test_guest_pages_render_translated_auth_labels(): void
    {
        foreach (array_keys(config('app.supported_locales')) as $locale) {
            $this->withSession(['locale' => $locale])
                ->get(route('login'))
                ->assertOk()
                ->assertSee(__('Login', [], $locale));
        }
    }

    public function test_admin_console_labels_are_available_in_japanese(): void
    {
        $this->assertSame('管理画面', __('Admin console', [], 'ja'));
        $this->assertSame('ユーザー管理', __('User Management', [], 'ja'));
        $this->assertSame('ユーザー管理', __('Admin Users', [], 'ja'));
        $this->assertSame('通知を作成', __('Create Notification', [], 'ja'));
        $this->assertSame('ユーザーを検索', __('Search users', [], 'ja'));
    }

    public function test_japanese_role_change_confirmation_is_grammatically_correct(): void
    {
        $this->assertSame(
            'testさんの権限を管理者に変更しますか？',
            __('Change :name role to :role?', [
                'name' => 'test',
                'role' => __('Admin', [], 'ja'),
            ], 'ja')
        );
    }

    public function test_category_labels_follow_the_selected_language(): void
    {
        $user = User::factory()->create(['preferred_language' => 'ja']);
        $category = Category::create(['name' => 'Events']);
        Category::create(['name' => 'Jobs']);

        $this->actingAs($user)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('イベント')
            ->assertSee('求人')
            ->assertSee(route('events.index', ['category' => $category->id]), false);

        $user->update(['preferred_language' => 'en']);

        $this->actingAs($user)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('Events');
    }

    public function test_admin_detail_and_edit_pages_follow_preferred_language(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'preferred_language' => 'ja',
        ]);
        $owner = User::factory()->create();
        $community = Community::create([
            'owner_id' => $owner->id,
            'name' => 'Hikers',
            'description' => 'We hike',
            'visibility' => 'public',
            'language' => 'ja',
        ]);
        $event = Event::create([
            'owner_id' => $owner->id,
            'community_id' => $community->id,
            'title' => 'Community hike',
            'description' => 'Original description',
            'language' => 'ja',
            'location' => 'Osaka',
            'start_at' => now()->addDays(2),
            'join_till' => now()->addDay(),
            'end_at' => now()->addDays(3),
            'price' => 0,
            'approval_required' => true,
            'visibility' => 'public',
            'status' => 'published',
        ]);
        $category = Category::create(['name' => 'Events']);
        $contact = Contact::create([
            'name' => 'Test Inquirer',
            'username' => 'test_inquirer',
            'email' => 'test@example.com',
            'detail' => 'Please help',
        ]);
        $review = EventReview::create([
            'event_id' => $event->id,
            'user_id' => $owner->id,
            'rating' => 5,
            'comment' => 'Excellent',
        ]);
        $comment = Comment::create([
            'event_id' => $event->id,
            'user_id' => $owner->id,
            'content' => 'Great event',
            'language' => 'ja',
        ]);

        $this->actingAs($admin);

        $this->get(route('admin.contacts.show', $contact))
            ->assertOk()
            ->assertSee('お問い合わせ')
            ->assertSee('送信された情報')
            ->assertDontSee('fa-solid fa-trash', false);

        $this->get(route('admin.reviews.edit', $review))
            ->assertOk()
            ->assertSee('レビューを編集')
            ->assertSee('評価')
            ->assertDontSee('fa-solid fa-trash', false);

        $this->get(route('admin.comments.edit', $comment))
            ->assertOk()
            ->assertSee('コメントを編集')
            ->assertSee('コメント')
            ->assertDontSee('fa-solid fa-trash', false);

        $this->get(route('admin.communities.edit', $community))
            ->assertOk()
            ->assertSee('コミュニティを編集')
            ->assertSee('変更を保存する')
            ->assertDontSee('fa-solid fa-trash', false);

        $this->get(route('admin.communities'))
            ->assertOk()
            ->assertSee('fa-solid fa-pen', false)
            ->assertSee('fa-solid fa-trash', false)
            ->assertDontSee('>編集<', false)
            ->assertDontSee('>削除<', false);

        $this->get(route('admin.communities.show', $community))
            ->assertOk()
            ->assertSee('fa-solid fa-pen', false)
            ->assertDontSee('fa-solid fa-trash', false)
            ->assertDontSee('>削除<', false);

        $this->get(route('admin.events.show', $event))
            ->assertOk()
            ->assertSee('イベント詳細')
            ->assertSee('承認制')
            ->assertSee('主催者')
            ->assertSee('fa-solid fa-pen', false)
            ->assertDontSee('fa-solid fa-trash', false)
            ->assertDontSee('>削除<', false);

        $this->get(route('admin.events.edit', $event))
            ->assertOk()
            ->assertSee('イベント編集')
            ->assertSee('参加には承認が必要')
            ->assertDontSee('fa-solid fa-trash', false);

        $this->get(route('admin.categories.edit', $category))
            ->assertOk()
            ->assertSee('カテゴリーを編集')
            ->assertSee('カテゴリー名')
            ->assertDontSee('fa-solid fa-trash', false);
    }

    public function test_enum_and_data_labels_follow_the_preferred_language(): void
    {
        app()->setLocale('ja');

        $this->assertSame('コミュニティ参加承認', LocalizedValue::notificationType('community_join_approved'));
        $this->assertSame('スポーツ', LocalizedValue::category('Sports'));
        $this->assertSame('承認制', LocalizedValue::visibility('approval'));
        $this->assertSame('公開', LocalizedValue::status('published'));
        $this->assertSame('承認済み', LocalizedValue::status('approved'));
        $this->assertSame('管理者', LocalizedValue::role('admin'));
        $this->assertSame('ロシア語', LocalizedValue::language('ru'));
        $this->assertSame('はい', LocalizedValue::approval(true));
        $this->assertSame('いいえ', LocalizedValue::approval(false));
        $this->assertSame('5 - とても良い', LocalizedValue::reviewRating(5));
    }

    public function test_new_category_name_is_added_to_language_files_without_overwriting(): void
    {
        config(['services.google_translate.enabled' => false]);
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lang-'.uniqid();
        mkdir($dir);
        foreach (glob(lang_path('*.json')) as $file) {
            copy($file, $dir.DIRECTORY_SEPARATOR.basename($file));
        }
        $this->app->instance(LanguageFileWriter::class, new LanguageFileWriter($dir));

        $admin = User::factory()->create(['role' => 'admin', 'preferred_language' => 'en']);

        $this->actingAs($admin)
            ->get(route('admin.categories.create'))
            ->assertOk()
            ->assertDontSee('name="translations[ja]"', false);

        Http::fake(fn ($request) => $request['q'] === 'Cooking'
            ? Http::response('error', 500)
            : Http::response(['translatedText' => 'T:'.$request['target']]));
        config(['services.libretranslate.url' => 'https://translate.test']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => 'Gardening', 'translations' => ['ja' => 'ガーデニング']])
            ->assertRedirect(route('admin.categories.index'));

        $english = json_decode(file_get_contents($dir.'/en.json'), true);
        $japanese = json_decode(file_get_contents($dir.'/ja.json'), true);
        $spanish = json_decode(file_get_contents($dir.'/es.json'), true);
        $this->assertSame('Gardening', $english['Gardening']);
        $this->assertSame('ガーデニング', $japanese['Gardening']);
        $this->assertSame('T:es', $spanish['Gardening']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => 'Sports', 'translations' => []])
            ->assertRedirect(route('admin.categories.index'));
        $japanese = json_decode(file_get_contents($dir.'/ja.json'), true);
        $this->assertSame('スポーツ', $japanese['Sports']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), ['name' => 'Cooking', 'translations' => []])
            ->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Cooking']);


        array_map('unlink', glob($dir.DIRECTORY_SEPARATOR.'*'));
        rmdir($dir);
    }

    public function test_user_notification_type_follows_the_preferred_language(): void
    {
        $user = User::factory()->create(['preferred_language' => 'ja']);
        $notification = UserNotification::create([
            'user_id' => $user->id,
            'type' => 'community_join_approved',
            'title' => null,
            'message' => 'Approved',
        ]);

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('コミュニティ参加承認');
    }
}