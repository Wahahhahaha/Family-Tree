<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Repositories\FamilyMemberRepository;
use App\Models\User;
use App\Models\Level;
use App\Models\Role;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    protected $memberRepo;

    public function __construct(FamilyMemberRepository $memberRepo)
    {
        $this->memberRepo = $memberRepo;
    }

    public function index(Request $request)
    {
        $currentRoleId = (int) session('authenticated_user.roleid');
        if (!in_array($currentRoleId, [1, 2], true)) {
            return redirect('/')->with('error', 'Unauthorized.');
        }

        $keyword = trim((string) $request->query('keyword', ''));
        $usersQuery = $this->memberRepo->usersQuery()->whereNull('u.deleted_at');

        if ($keyword !== '') {
            $usersQuery->where(function ($q) use ($keyword) {
                $keyword = '%' . $keyword . '%';
                $q->where('u.username', 'like', $keyword)
                  ->orWhere('e.name', 'like', $keyword)
                  ->orWhere('fm.name', 'like', $keyword);
            });
        }

        $users = $usersQuery->paginate(20)->withQueryString();

        return view("admin.user-management", [
            "pageClass" => "page-family-tree",
            "systemSettings" => $this->getSystemSettings(),
            "users" => $users,
            "levels" => Level::all(),
            "roles" => Role::all()
        ]);
    }

    public function store(Request $request) { /* Migrasi logic storeUser */ }
    public function update(Request $request, $userid) { /* Migrasi logic updateUser */ }
    public function destroy(Request $request, $userid) { /* Migrasi logic deleteUser */ }
    public function resetPassword(Request $request, $userid) { /* Migrasi logic resetPassword */ }
    public function updateLifeStatus(Request $request)
    {
        $validated = $request->validate([
            'memberid' => 'required|integer',
            'life_status' => 'required|string|in:Alive,Deceased,alive,deceased',
        ]);

        $this->memberRepo->updateLifeStatus($validated['memberid'], $validated['life_status']);
        return response()->json(['success' => true]);
    }
}
