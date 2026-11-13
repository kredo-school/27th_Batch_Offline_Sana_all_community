<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;

// コミュニティへの参加・退会、オーナーによる参加申請の承認と招待
class CommunityMembershipController extends Controller
{
    /**
     * オーナーがユーザー名でメンバーを招待する（招待された人が承諾すると参加になる）
     */
    public function invite(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->owner_id === $request->user()->id, 403);

        $validated = $request->validateWithBag('invite', [
            'username' => 'required|string|max:255',
        ]);
        // エラーのときだけ入力を残して戻す（成功したら入力欄は空にする）
        $fail = fn (string $message) => redirect()->to(route('community.show', $community->id) . '#members')
            ->withInput()
            ->withErrors(['username' => $message], 'invite');

        $invitee = User::where('username', ltrim(trim($validated['username']), '@'))->first();
        if (! $invitee) {
            return $fail('No user found with that username.');
        }
        if ($invitee->id === $community->owner_id) {
            return $fail('You are already the organizer of this community.');
        }

        $membership = $community->memberships()->firstOrNew(['user_id' => $invitee->id]);
        if ($membership->exists && $membership->status === 'approved') {
            return $fail("{$invitee->username} is already a member.");
        }
        if ($membership->exists && $membership->status === 'invited') {
            return $fail("{$invitee->username} has already been invited.");
        }

        if ($membership->exists) {
            // 参加申請中の人を招待したら、そのまま承認する
            $membership->update(['status' => 'approved', 'joined_at' => now()]);
            $this->notifyRequestResult($community, $invitee, $request->user(), true);
        } else {
            $membership->fill(['role' => 'member', 'status' => 'invited', 'joined_at' => null])->save();
            $owner = $request->user();
            UserNotification::create([
                'user_id' => $invitee->id,
                'sender_name' => $owner->username ?: $owner->name,
                'type' => 'community_invite',
                'title' => 'Community invitation',
                'message' => ($owner->username ?: $owner->name) . " invited you to join '{$community->name}'.",
                'related_type' => Community::class,
                'related_id' => $community->id,
            ]);
        }

        return redirect()->to(route('community.show', $community->id) . '#members');
    }

    /**
     * 招待された人が承諾して参加する
     */
    public function accept(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        $userId = $request->user()->id;

        $community->memberships()
            ->where('user_id', $userId)
            ->where('status', 'invited')
            ->firstOrFail()
            ->update(['status' => 'approved', 'joined_at' => now()]);
        $this->markInvitationRead($community, $userId);

        return redirect()->route('community.show', $community->id);
    }

    // 参加申請の結果（承認・却下）を申請者に知らせ、オーナー側に残っている申請の通知を片付ける
    private function notifyRequestResult(Community $community, User $requester, User $owner, bool $approved): void
    {
        UserNotification::create([
            'user_id' => $requester->id,
            'sender_name' => $owner->username ?: $owner->name,
            'type' => $approved ? 'community_join_approved' : 'community_join_declined',
            'title' => $approved ? 'Community join request approved' : 'Community join request declined',
            'message' => $approved
                ? "Your request to join '{$community->name}' was approved."
                : "Your request to join '{$community->name}' was declined.",
            'related_type' => Community::class,
            'related_id' => $community->id,
        ]);

        UserNotification::query()
            ->where('user_id', $community->owner_id)
            ->where('type', 'community_join_request')
            ->where('related_id', $community->id)
            ->where('sender_name', $requester->username ?: $requester->name)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    // 招待の通知を既読にして、ナビバーのコミュニティ通知欄から消す
    private function markInvitationRead(Community $community, int $userId): void
    {
        UserNotification::query()
            ->where('user_id', $userId)
            ->where('type', 'community_invite')
            ->where('related_id', $community->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    /**
     * 参加（public はすぐ参加、approval は申請中になる、private は参加できない）
     */
    public function join(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        $user = $request->user();
        abort_if($community->owner_id === $user->id, 403);
        abort_if($community->visibility === 'private', 403, 'This community is invite only.');

        $membership = $community->memberships()->firstOrNew(['user_id' => $user->id]);
        if ($membership->exists) {
            return redirect()->route('community.show', $community->id);
        }

        $approved = $community->visibility === 'public';
        $membership->fill([
            'role' => 'member',
            'status' => $approved ? 'approved' : 'pending',
            'joined_at' => $approved ? now() : null,
        ])->save();

        if (! $approved) {
            UserNotification::create([
                'user_id' => $community->owner_id,
                'sender_name' => $user->username ?: $user->name,
                'type' => 'community_join_request',
                'title' => 'Community join request',
                'message' => "{$user->name} requested to join '{$community->name}'.",
                'related_type' => Community::class,
                'related_id' => $community->id,
            ]);
        }

        return redirect()->route('community.show', $community->id);
    }

    /**
     * 退会（申請中の取り消しと、招待を断るのも兼ねる）
     */
    public function leave(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        $community->memberships()->where('user_id', $request->user()->id)->delete();
        $this->markInvitationRead($community, $request->user()->id);

        // 招待のみのコミュニティを抜けたり招待を断ったりしたら、もう見られないので一覧に戻す
        if (! $community->isVisibleTo($request->user())) {
            return redirect()->route('community.index');
        }

        return redirect()->route('community.show', $community->id);
    }

    /**
     * オーナーが参加申請を承認する
     */
    public function approve(Request $request, $id, $userId)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->owner_id === $request->user()->id, 403);

        $membership = $community->memberships()
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->firstOrFail();
        $membership->update(['status' => 'approved', 'joined_at' => now()]);
        // 申請者に承認を知らせ、オーナー側の申請の通知を片付ける（main 側で入った同じ通知もここにまとめた）
        $this->notifyRequestResult($community, $membership->user, $request->user(), true);

        return redirect()->to(route('community.show', $community->id) . '#members');
    }

    /**
     * オーナーが参加申請を断る、招待を取り消す、またはメンバーを外す
     */
    public function remove(Request $request, $id, $userId)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->owner_id === $request->user()->id, 403);

        $membership = $community->memberships()->where('user_id', $userId)->firstOrFail();
        $membership->delete();
        // 参加申請を断ったら申請者に知らせる
        if ($membership->status === 'pending') {
            $this->notifyRequestResult($community, $membership->user, $request->user(), false);
        }
        // 招待を取り消したら、相手に残っている招待の通知も消す
        $this->markInvitationRead($community, (int) $userId);

        return redirect()->to(route('community.show', $community->id) . '#members');
    }
}
