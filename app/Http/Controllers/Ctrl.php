<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class Ctrl extends Controller
{


    public function home(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $systemSettings = $this->getSystemSettings();
        $familyMembers = DB::table('user as u')
            ->join('family_member as fm', 'fm.userid', '=', 'u.userid')
            ->where('u.levelid', 2)
            ->select(
                'u.userid',
                'u.username',
                'fm.memberid',
                'fm.name',
                'fm.gender',
                'fm.birthdate',
                'fm.life_status',
                'fm.picture',
                'fm.job',
                'fm.address',
                'fm.education_status'
            )
            ->orderBy('fm.memberid')
            ->get()
            ->map(function ($member) {
                $age = null;
                if (!empty($member->birthdate)) {
                    try {
                        $age = Carbon::parse($member->birthdate)->age;
                    } catch (\Throwable $e) {
                        $age = null;
                    }
                }

                $member->age = $age;
                if (empty($member->picture)) {
                    $member->picture = 'https://api.dicebear.com/9.x/personas/svg?seed='
                        . urlencode((string) $member->name)
                        . '&backgroundColor=93c5fd';
                }

                return $member;
            });

        $membersById = $familyMembers->keyBy('memberid');
        $relationships = DB::table('relationship')
            ->select('memberid', 'relatedmemberid', 'relationtype')
            ->get();

        $childrenMap = [];
        $partnerMap = [];
        $parentMap = [];
        $parentCount = [];

        foreach ($relationships as $relation) {
            $from = (int) $relation->memberid;
            $to = (int) $relation->relatedmemberid;
            $type = strtolower((string) $relation->relationtype);

            if (!isset($membersById[$from]) || !isset($membersById[$to])) {
                continue;
            }

            if ($type === 'child') {
                $childrenMap[$from] = $childrenMap[$from] ?? [];
                if (!in_array($to, $childrenMap[$from], true)) {
                    $childrenMap[$from][] = $to;
                    $parentMap[$to] = $parentMap[$to] ?? [];
                    if (!in_array($from, $parentMap[$to], true)) {
                        $parentMap[$to][] = $from;
                    }
                    $parentCount[$to] = ($parentCount[$to] ?? 0) + 1;
                }
            }

            if ($type === 'partner') {
                $partnerMap[$from] = $partnerMap[$from] ?? [];
                if (!in_array($to, $partnerMap[$from], true)) {
                    $partnerMap[$from][] = $to;
                }

                $partnerMap[$to] = $partnerMap[$to] ?? [];
                if (!in_array($from, $partnerMap[$to], true)) {
                    $partnerMap[$to][] = $from;
                }
            }
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentMember = $familyMembers->firstWhere('userid', $currentUserId);
        $currentMemberId = (int) ($currentMember->memberid ?? 0);

        $relationLabels = [];
        $genderLabel = function (int $memberId, string $maleLabel, string $femaleLabel, string $fallback) use ($membersById): string {
            $gender = strtolower((string) ($membersById[$memberId]->gender ?? ''));
            if ($gender === 'male') {
                return $maleLabel;
            }
            if ($gender === 'female') {
                return $femaleLabel;
            }
            return $fallback;
        };

        $asSet = function (array $ids): array {
            $set = [];
            foreach ($ids as $id) {
                $set[(int) $id] = true;
            }
            return $set;
        };

        $parentsOf = function (int $memberId) use ($parentMap): array {
            return $parentMap[$memberId] ?? [];
        };

        $childrenOf = function (int $memberId) use ($childrenMap): array {
            return $childrenMap[$memberId] ?? [];
        };

        foreach ($familyMembers as $member) {
            $targetId = (int) $member->memberid;

            if ($targetId === 0) {
                continue;
            }

            if ($currentMemberId === 0) {
                $relationLabels[$targetId] = 'Family Member';
                continue;
            }

            if ($targetId === $currentMemberId) {
                $relationLabels[$targetId] = 'Me';
                continue;
            }

            $myParents = $parentsOf($currentMemberId);
            $myChildren = $childrenOf($currentMemberId);
            $targetParents = $parentsOf($targetId);
            $targetChildren = $childrenOf($targetId);
            $myParentSet = $asSet($myParents);
            $targetParentSet = $asSet($targetParents);

            if (in_array($targetId, $partnerMap[$currentMemberId] ?? [], true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Husband', 'Wife', 'Partner');
                continue;
            }

            if (in_array($targetId, $myParents, true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Father', 'Mother', 'Parent');
                continue;
            }

            if (in_array($targetId, $myChildren, true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Son', 'Daughter', 'Child');
                continue;
            }

            $sameParent = false;
            foreach ($myParents as $parentId) {
                if (isset($targetParentSet[(int) $parentId])) {
                    $sameParent = true;
                    break;
                }
            }
            if ($sameParent) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Brother', 'Sister', 'Sibling');
                continue;
            }

            $myGrandParents = [];
            foreach ($myParents as $parentId) {
                foreach ($parentsOf((int) $parentId) as $grandParentId) {
                    $myGrandParents[] = (int) $grandParentId;
                }
            }
            if (in_array($targetId, $myGrandParents, true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Grandfather', 'Grandmother', 'Grandparent');
                continue;
            }

            $myGrandChildren = [];
            foreach ($myChildren as $childId) {
                foreach ($childrenOf((int) $childId) as $grandChildId) {
                    $myGrandChildren[] = (int) $grandChildId;
                }
            }
            if (in_array($targetId, $myGrandChildren, true)) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Grandson', 'Granddaughter', 'Grandchild');
                continue;
            }

            $myParentSiblings = [];
            foreach ($myParents as $parentId) {
                $grandParents = $parentsOf((int) $parentId);
                $parentSiblingCandidates = [];
                foreach ($grandParents as $grandParentId) {
                    foreach ($childrenOf((int) $grandParentId) as $childOfGrand) {
                        $parentSiblingCandidates[] = (int) $childOfGrand;
                    }
                }

                foreach ($parentSiblingCandidates as $candidateId) {
                    if ($candidateId !== (int) $parentId) {
                        $myParentSiblings[$candidateId] = true;
                    }
                }
            }

            if (isset($myParentSiblings[$targetId])) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Uncle', 'Aunt', 'Relative');
                continue;
            }

            $isCousin = false;
            foreach (array_keys($myParentSiblings) as $parentSiblingId) {
                if (in_array($targetId, $childrenOf((int) $parentSiblingId), true)) {
                    $isCousin = true;
                    break;
                }
            }

            if ($isCousin) {
                $relationLabels[$targetId] = 'Cousin';
                continue;
            }

            $mySiblings = [];
            foreach ($myParents as $parentId) {
                foreach ($childrenOf((int) $parentId) as $siblingId) {
                    $siblingId = (int) $siblingId;
                    if ($siblingId !== $currentMemberId) {
                        $mySiblings[$siblingId] = true;
                    }
                }
            }
            $isNephewNiece = false;
            foreach (array_keys($mySiblings) as $siblingId) {
                if (in_array($targetId, $childrenOf((int) $siblingId), true)) {
                    $isNephewNiece = true;
                    break;
                }
            }
            if ($isNephewNiece) {
                $relationLabels[$targetId] = $genderLabel($targetId, 'Nephew', 'Niece', 'Relative');
                continue;
            }

            $relationLabels[$targetId] = 'Relative';
        }

        $candidateRoots = $familyMembers
            ->pluck('memberid')
            ->filter(function ($memberId) use ($parentCount) {
                return !isset($parentCount[(int) $memberId]);
            })
            ->values()
            ->all();

        if (empty($candidateRoots)) {
            $candidateRoots = $familyMembers->pluck('memberid')->values()->all();
        }

        $usedMemberIds = [];
        $buildNode = function (int $memberId, array $ancestorIds = []) use (&$buildNode, &$usedMemberIds, $membersById, $childrenMap, $partnerMap) {
            if (isset($ancestorIds[$memberId]) || !isset($membersById[$memberId])) {
                return null;
            }

            if (isset($usedMemberIds[$memberId])) {
                return null;
            }

            $usedMemberIds[$memberId] = true;
            $ancestorIds[$memberId] = true;

            $partnerMembers = collect();
            foreach ($partnerMap[$memberId] ?? [] as $partnerId) {
                if (!isset($membersById[$partnerId])) {
                    continue;
                }

                if (!isset($usedMemberIds[$partnerId])) {
                    $usedMemberIds[$partnerId] = true;
                    $partnerMembers->push($membersById[$partnerId]);
                }
            }

            $children = [];
            foreach ($childrenMap[$memberId] ?? [] as $childId) {
                $childNode = $buildNode((int) $childId, $ancestorIds);
                if ($childNode !== null) {
                    $children[] = $childNode;
                }
            }

            return [
                'member' => $membersById[$memberId],
                'partners' => $partnerMembers->values()->all(),
                'children' => $children,
            ];
        };

        $treeRoots = [];
        foreach ($candidateRoots as $rootId) {
            $rootNode = $buildNode((int) $rootId);
            if ($rootNode !== null) {
                $treeRoots[] = $rootNode;
            }
        }

        foreach ($familyMembers as $member) {
            $memberId = (int) $member->memberid;
            if (isset($usedMemberIds[$memberId])) {
                continue;
            }

            $node = $buildNode($memberId);
            if ($node !== null) {
                $treeRoots[] = $node;
            }
        }

        echo view('all.header', [
            'pageTitle' => $systemSettings['website_name'],
            'pageClass' => 'page-family-tree',
        ]);
        $currentUserId = (int) session('authenticated_user.userid');
        $currentFamilyProfile = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->select('userid', 'job', 'address', 'education_status')
            ->first();

        echo view('all.home', compact('systemSettings', 'familyMembers', 'currentFamilyProfile', 'treeRoots', 'relationLabels'));
        echo view('all.footer');
    }

    public function login(){
        if (session()->has('authenticated_user')) {
            return redirect('/');

        }

        $systemSettings = $this->getSystemSettings();

        echo view('all.header', [
            'pageTitle' => 'Login | ' . $systemSettings['website_name'],
            'pageClass' => 'page-login',
        ]);
        echo view('all.login', compact('systemSettings'));
        echo view('all.footer');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username is required.',
            'password.required' => 'Password is required.',
        ]);

        $username = trim($credentials['username']);

        $user = DB::table('user as u')
            ->where('u.username', $username)
            ->select('u.userid', 'u.username', 'u.password', 'u.levelid')
            ->first();

        if (!$user) {
            return back()->withErrors([
                'username' => 'Username not found.',
            ])->withInput();
        }

        $storedPassword = stripslashes((string) $user->password);
        $validPassword = Hash::check($credentials['password'], $storedPassword)
            || hash_equals((string) $user->password, $credentials['password']);

        if (!$validPassword) {
            return back()->withErrors([
                'password' => 'Invalid password.',
            ])->withInput();
        }

        $level = DB::table('level')
            ->where('levelid', $user->levelid)
            ->first();

        $employer = DB::table('employer as e')
            ->leftJoin('role as r', 'r.roleid', '=', 'e.roleid')
            ->where('e.userid', $user->userid)
            ->select(
                'e.employerid',
                'e.name',
                'e.email',
                'e.phonenumber',
                'e.roleid',
                'r.rolename'
            )
            ->first();

        $familyMember = DB::table('family_member')
            ->where('userid', $user->userid)
            ->first();

        $request->session()->regenerate();
        $request->session()->put('authenticated_user', [
            'userid' => $user->userid,
            'username' => $user->username,
            'levelid' => $user->levelid,
            'levelname' => $level->levelname ?? null,
            'roleid' => $employer->roleid ?? null,
            'rolename' => $employer->rolename ?? null,
            'employer' => $employer,
            'familyMember' => $familyMember,
        ]);


        return redirect('/');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('authenticated_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }


    public function account(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $systemSettings = $this->getSystemSettings();


        echo view('all.header', [
            'pageTitle' => 'Account | ' . $systemSettings['website_name'],
            'pageClass' => 'page-family-tree',
        ]);
        echo view('all.account', compact('systemSettings'));
        echo view('all.footer');
    }

    public function userManagement(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];
        $isFamilyHead = $currentRoleId === 3;

        if (!in_array($currentRoleId, $allowedRoles, true)) {
            return redirect('/')->with('error', 'You do not have permission to access user management.');
        }

        $usersQuery = $this->usersQuery();
        if ($isFamilyHead) {
            $usersQuery->where('u.levelid', 2);
        }

        $users = $usersQuery->paginate(20)->withQueryString();

        $levels = DB::table('level')
            ->selectRaw('MIN(levelid) as levelid, levelname')
            ->groupBy('levelname')
            ->orderBy('levelname')
            ->get();

        $roles = DB::table('role')
            ->orderBy('roleid')
            ->get();

        if ($isFamilyHead) {
            $levels = $levels->filter(function ($level) {
                return stripos((string) $level->levelname, 'family') !== false;
            })->values();

            $roles = $roles->filter(function ($role) {
                return in_array((int) $role->roleid, [3, 4], true);
            })->values();
        }

        $systemSettings = $this->getSystemSettings();

        if ($request->ajax() || $request->expectsJson() || $request->query('ajax') === '1') {
            return response()->json([
                'rows_html' => view('admin.partials.user-table-rows', ['users' => $users])->render(),
                'pagination_html' => view('admin.partials.user-pagination', ['users' => $users])->render(),
                'total' => $users->total(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ]);
        }

        echo view('all.header', [
            'pageTitle' => 'User Management',
            'pageClass' => 'page-family-tree',
        ]);
        echo view('admin.user-management', compact('users', 'levels', 'roles', 'systemSettings'));
        echo view('all.footer');
    }

    public function storeUser(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $allowedRoles = [1, 2, 3];
        $isFamilyHead = $currentRoleId === 3;

        if (!in_array($currentRoleId, $allowedRoles, true)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'You do not have permission to add users.',
                ], 403);
            }

            return redirect('/')->with('error', 'You do not have permission to add users.');
        }

        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:255', 'unique:user,username'],
            'levelid' => ['required', 'integer', 'exists:level,levelid'],
            'roleid' => ['required', 'integer', 'exists:role,roleid'],
            'email' => ['nullable', 'email', 'max:255'],
            'phonenumber' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'max:255'],
            'birthdate' => ['nullable', 'date', 'before_or_equal:today'],
            'birthplace' => ['nullable', 'string', 'max:255'],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'Username already exists.',
            'levelid.required' => 'Level is required.',
            'levelid.exists' => 'Selected level is invalid.',
            'roleid.required' => 'Role is required.',
            'roleid.exists' => 'Selected role is invalid.',
            'email.email' => 'Email format is invalid.',
            'gender.in' => 'Gender must be male or female.',
            'birthdate.before_or_equal' => 'Birthdate must be today or earlier.',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return redirect('/management/users')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();

        $selectedLevel = DB::table('level')
            ->where('levelid', (int) $validated['levelid'])
            ->first();

        $isFamilyLevel = $selectedLevel && stripos((string) $selectedLevel->levelname, 'family') !== false;
        $allowedRoleIds = $isFamilyLevel ? [3, 4] : [1, 2];
        $isEmployerLevel = !$isFamilyLevel;

        if ($isFamilyHead) {
            if (!$isFamilyLevel) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Family head can only add family users.',
                    ], 422);
                }

                return redirect('/management/users')->with('error', 'Family head can only add family users.');
            }

            if (!in_array((int) $validated['roleid'], [3, 4], true)) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Family head can only assign family roles.',
                    ], 422);
                }

                return redirect('/management/users')->with('error', 'Family head can only assign family roles.');
            }
        }

        if (!in_array((int) $validated['roleid'], $allowedRoleIds, true)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => ['roleid' => ['Role does not match the selected level.']],
                ], 422);
            }

            return redirect('/management/users')->with('error', 'Role does not match the selected level.');
        }

        if ($isEmployerLevel && (empty($validated['email']) || empty($validated['phonenumber']))) {
            $errors = [];
            if (empty($validated['name'])) {
                $errors['name'] = 'Name is required for Employer level.';
            }
            if (empty($validated['email'])) {
                $errors['email'] = 'Email is required for Employer level.';
            }
            if (empty($validated['phonenumber'])) {
                $errors['phonenumber'] = 'Phone number is required for Employer level.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'message' => 'Validation failed.',
                    'errors' => $errors,
                ], 422);
            }

            return redirect('/management/users')
                ->withErrors($errors)
                ->withInput();
        }

        if ($isFamilyLevel) {
            $errors = [];
            $requiredFields = [
                'name' => 'Name is required for Family level.',
                'email' => 'Email is required for Family level.',
                'phonenumber' => 'Phone number is required for Family level.',
                'gender' => 'Gender is required for Family level.',
                'address' => 'Address is required for Family level.',
                'marital_status' => 'Marital status is required for Family level.',
                'birthdate' => 'Birthdate is required for Family level.',
                'birthplace' => 'Birthplace is required for Family level.',
            ];

            foreach ($requiredFields as $field => $message) {
                if (empty($validated[$field])) {
                    $errors[$field] = $message;
                }
            }

            if (!empty($errors)) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'message' => 'Validation failed.',
                        'errors' => $errors,
                    ], 422);
                }

                return redirect('/management/users')
                    ->withErrors($errors)
                    ->withInput();
            }
        }

        DB::transaction(function () use ($validated, $isFamilyLevel) {
            $userId = DB::table('user')->insertGetId([
                'username' => $validated['username'],
                'password' => Hash::make($validated['username']),
                'levelid' => (int) $validated['levelid'],
            ]);

            if ($isFamilyLevel) {
                $birthdate = Carbon::parse($validated['birthdate']);
                $picture = $validated['gender'] === 'male'
                    ? '/images/avatar-male.svg'
                    : '/images/avatar-female.svg';

                DB::table('family_member')->insert([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phonenumber' => $validated['phonenumber'],
                    'gender' => $validated['gender'],
                    'birthdate' => $birthdate->toDateString(),
                    'birthplace' => $validated['birthplace'],
                    'address' => $validated['address'],
                    'job' => null,
                    'education_status' => null,
                    'life_status' => 'alive',
                    'marital_status' => $validated['marital_status'],
                    'deaddate' => null,
                    'picture' => $picture,
                    'userid' => $userId,
                ]);
            } else {
                DB::table('employer')->insert([
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? '',
                    'phonenumber' => $validated['phonenumber'] ?? '',
                    'roleid' => (int) $validated['roleid'],
                    'userid' => $userId,
                ]);
            }
        });

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'New user has been added.',
            ]);
        }

        return redirect('/management/users')->with('success', 'New user has been added.');
    }

    public function resetUserPassword(Request $request, $userid)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $user = DB::table('user')
            ->where('userid', (int) $userid)
            ->first();

        if (!$user) {
            return redirect('/management/users')->with('error', 'User not found.');
        }

        DB::table('user')
            ->where('userid', (int) $userid)
            ->update([
                'password' => Hash::make($user->username),
            ]);

        return redirect('/management/users')->with(
            'success',
            'Password has been reset to the default value (username).'
        );
    }

    public function deleteUser(Request $request, $userid)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $targetUserId = (int) $userid;
        $currentUserId = (int) session('authenticated_user.userid');

        if ($targetUserId === $currentUserId) {
            return redirect('/management/users')->with('error', 'You cannot delete your own account.');
        }

        $user = DB::table('user')
            ->where('userid', $targetUserId)
            ->first();

        if (!$user) {
            return redirect('/management/users')->with('error', 'User not found.');
        }

        DB::transaction(function () use ($targetUserId) {
            DB::table('employer')->where('userid', $targetUserId)->delete();
            DB::table('family_member')->where('userid', $targetUserId)->delete();
            DB::table('user')->where('userid', $targetUserId)->delete();
        });

        return redirect('/management/users')->with('success', 'User has been deleted.');
    }

    public function updateFamilyProfile(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentUserId = (int) session('authenticated_user.userid');
        $currentLevelId = (int) session('authenticated_user.levelid');

        if ($currentLevelId !== 2) {
            return redirect('/')->with('error', 'Only family members can update this profile.');
        }

        $familyMember = DB::table('family_member')
            ->where('userid', $currentUserId)
            ->first();

        if (!$familyMember) {
            return redirect('/')->with('error', 'Family profile not found.');
        }

        $validated = $request->validate([
            'job' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'education_status' => ['nullable', 'string', 'max:255'],
        ], [
            'job.max' => 'Job max length is 255 characters.',
            'address.max' => 'Address max length is 255 characters.',
            'education_status.max' => 'Education max length is 255 characters.',
        ]);

        DB::table('family_member')
            ->where('userid', $currentUserId)
            ->update([
                'job' => $validated['job'] ?? null,
                'address' => $validated['address'] ?? null,
                'education_status' => $validated['education_status'] ?? null,
            ]);

        return redirect('/')->with('success', 'Profile details updated successfully.');
    }

    public function storeFamilyMemberFromHome(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        $currentRoleId = (int) session('authenticated_user.roleid');
        $currentLevelId = (int) session('authenticated_user.levelid');
        if ($currentRoleId !== 3 && $currentLevelId !== 2) {
            return redirect('/')->with('error', 'Only family head can add new members from this page.');
        }

        $validated = $request->validate([
            'username' => ['required', 'string', 'max:255', 'unique:user,username'],
            'relation_type' => ['required', 'string', 'in:child,partner'],
            'target_memberid' => ['required', 'integer', 'exists:family_member,memberid'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phonenumber' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'string', 'in:male,female'],
            'address' => ['required', 'string', 'max:255'],
            'marital_status' => ['required', 'string', 'max:255'],
            'birthdate' => ['required', 'date', 'before_or_equal:today'],
            'birthplace' => ['required', 'string', 'max:255'],
        ], [
            'username.required' => 'Username is required.',
            'username.unique' => 'Username already exists.',
            'relation_type.required' => 'Please choose Add Child or Add Partner.',
            'relation_type.in' => 'Invalid relation type selected.',
            'target_memberid.required' => 'Please select a target member from the tree.',
            'target_memberid.exists' => 'Selected target member is invalid.',
            'birthdate.before_or_equal' => 'Birthdate must be today or earlier.',
        ]);

        DB::transaction(function () use ($validated) {
            $userId = DB::table('user')->insertGetId([
                'username' => $validated['username'],
                'password' => Hash::make($validated['username']),
                'levelid' => 2,
            ]);

            $picture = $validated['gender'] === 'male'
                ? '/images/avatar-male.svg'
                : '/images/avatar-female.svg';

            $newMemberId = DB::table('family_member')->insertGetId([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phonenumber' => $validated['phonenumber'],
                'gender' => $validated['gender'],
                'birthdate' => Carbon::parse($validated['birthdate'])->toDateString(),
                'birthplace' => $validated['birthplace'],
                'address' => $validated['address'],
                'job' => null,
                'education_status' => null,
                'life_status' => 'alive',
                'marital_status' => $validated['marital_status'],
                'deaddate' => null,
                'picture' => $picture,
                'userid' => $userId,
            ]);

            $targetMemberId = (int) $validated['target_memberid'];
            $relationType = (string) $validated['relation_type'];

            if ($relationType === 'child') {
                DB::table('relationship')->insert([
                    'memberid' => $targetMemberId,
                    'relatedmemberid' => $newMemberId,
                    'relationtype' => 'child',
                ]);
            }

            if ($relationType === 'partner') {
                DB::table('relationship')->insert([
                    'memberid' => $targetMemberId,
                    'relatedmemberid' => $newMemberId,
                    'relationtype' => 'partner',
                ]);

                DB::table('relationship')->insert([
                    'memberid' => $newMemberId,
                    'relatedmemberid' => $targetMemberId,
                    'relationtype' => 'partner',
                ]);
            }
        });

        return redirect('/')->with('success', 'New family member has been added.');
    }

    public function systemSetting(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            return redirect('/management/users')->with('error', 'Only superadmin can access settings.');
        }

        $systemSettings = $this->getSystemSettings();

        echo view('all.header', [
            'pageTitle' => 'System Settings',
            'pageClass' => 'page-family-tree',
        ]);
        echo view('superadmin.settings', compact('systemSettings'));
        echo view('all.footer');
    }

    public function updateSystemSetting(Request $request)
    {
        if (!$request->session()->has('authenticated_user')) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect('/login');
        }

        if ((int) session('authenticated_user.roleid') !== 1) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['message' => 'Only superadmin can update settings.'], 403);
            }

            return redirect('/management/users')->with('error', 'Only superadmin can update settings.');
        }

        $validated = $request->validate([
            'website_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,svg', 'max:2048'],
        ], [
            'website_name.required' => 'Website name is required.',
            'logo.image' => 'Logo must be an image file.',
            'logo.mimes' => 'Logo must be jpg, jpeg, png, webp, or svg.',
            'logo.max' => 'Logo max size is 2MB.',
        ]);

        $settings = $this->getSystemSettings();
        $settings['website_name'] = $validated['website_name'];

        if ($request->hasFile('logo')) {
            $uploadDir = public_path('uploads/system');
            File::ensureDirectoryExists($uploadDir);

            if (!empty($settings['logo_path']) && str_starts_with($settings['logo_path'], '/uploads/system/')) {
                $oldFile = public_path(ltrim($settings['logo_path'], '/'));
                if (File::exists($oldFile)) {
                    File::delete($oldFile);
                }
            }

            $ext = $request->file('logo')->getClientOriginalExtension();
            $fileName = 'system_logo_' . time() . '.' . $ext;
            $request->file('logo')->move($uploadDir, $fileName);
            $settings['logo_path'] = '/uploads/system/' . $fileName;
        }

        $this->saveSystemSettings($settings);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'message' => 'System settings updated successfully.',
                'settings' => $settings,
            ]);
        }

        return redirect('/setting')->with('success', 'System settings updated successfully.');
    }

    private function usersQuery()
    {
        return DB::table('user as u')
            ->leftJoin('level as l', 'l.levelid', '=', 'u.levelid')
            ->leftJoin('employer as e', 'e.userid', '=', 'u.userid')
            ->leftJoin('role as r', 'r.roleid', '=', 'e.roleid')
            ->leftJoin('family_member as fm', 'fm.userid', '=', 'u.userid')
            ->select(
                'u.userid',
                'u.username',
                'l.levelname',
                'r.rolename',
                DB::raw("COALESCE(e.name, fm.name, '-') as fullname"),
                DB::raw("COALESCE(e.email, fm.email, '-') as email"),
                DB::raw("COALESCE(e.phonenumber, fm.phonenumber, '-') as phone"),
                DB::raw("CASE
                    WHEN e.employerid IS NOT NULL THEN 'Employer'
                    WHEN fm.memberid IS NOT NULL THEN 'Family Member'
                    ELSE 'User'
                END as source")
            )
            ->orderBy('u.userid');
    }

    private function getSystemSettings(): array
    {
        $defaults = [
            'website_name' => 'Family Tree System',
            'logo_path' => '',
        ];

        $path = storage_path('app/system_settings.json');
        if (!File::exists($path)) {
            return $defaults;
        }

        $data = json_decode((string) File::get($path), true);
        if (!is_array($data)) {
            return $defaults;
        }

        return array_merge($defaults, $data);
    }

    private function saveSystemSettings(array $settings): void
    {
        $path = storage_path('app/system_settings.json');
        File::put($path, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
