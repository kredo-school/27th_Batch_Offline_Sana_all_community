<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UsersController extends Controller
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    //this function will return the view of the users index page
    public function index(Request $request)
    {
        $users = $this->user->withTrashed()->with('profile');

        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $users->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $sorts = [
            'latest' => ['created_at', 'desc'],
            'oldest' => ['created_at', 'asc'],
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
        ];
        $sort = $request->query('sort', 'latest');
        [$column, $direction] = $sorts[$sort] ?? $sorts['latest'];

        $all_users = $users->orderBy($column, $direction)->paginate(10)->withQueryString();

        return view('admin.admin', compact('all_users'));
    }

    //this function will deactivate the user
    public function deactivate($id){
        $this->user->destroy($id);
        return redirect()->back();
    }

    //this function will activate user 
    public function activate($id){
        $this->user->onlyTrashed()->findOrFail($id)->restore();
        return redirect()->back();
    }

    public function changeRole(Request $request, $id)
    {
        abort_if((int) $id === (int) $request->user()->getKey(), 403);

        $validated = $request->validate([
            'role' => ['required', 'in:admin,user'],
        ]);

        $this->user->withTrashed()->findOrFail($id)->update($validated);

        return redirect()->back();
    }
}
