<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\UserNotification;
use Illuminate\Http\Request;

// コミュニティへの参加・退会と、オーナーによる参加申請の承認
class CommunityMembershipController extends Controller
{
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
     * 退会（申請中の取り消しも兼ねる）
     */
    public function leave(Request $request, $id)
    {
        $community = Community::findOrFail($id);
        $community->memberships()->where('user_id', $request->user()->id)->delete();

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

        UserNotification::create([
            'user_id' => $membership->user_id,
            'sender_name' => $request->user()->username ?: $request->user()->name,
            'type' => 'community_join_approved',
            'title' => 'Community join request approved',
            'message' => "Your request to join '{$community->name}' was approved.",
            'related_type' => Community::class,
            'related_id' => $community->id,
        ]);

        return redirect()->to(route('community.show', $community->id) . '#members');
    }

    /**
     * オーナーが参加申請を断る、またはメンバーを外す
     */
    public function remove(Request $request, $id, $userId)
    {
        $community = Community::findOrFail($id);
        abort_unless($community->owner_id === $request->user()->id, 403);

        $community->memberships()->where('user_id', $userId)->firstOrFail()->delete();

        return redirect()->to(route('community.show', $community->id) . '#members');
    }
}
