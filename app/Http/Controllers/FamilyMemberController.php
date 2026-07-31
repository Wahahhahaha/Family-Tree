<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FamilyMemberController extends Controller
{
    public function update(Request $request, $id)
    {
        Log::info('FamilyMember Update Request:', ['id' => $id, 'data' => $request->all()]);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'birthdate' => 'nullable|date|before_or_equal:today',
            'birthplace' => 'nullable|string|max:255',
            'bloodtype' => 'nullable|string|max:11',
            'job' => 'nullable|string|max:255',
            'education_status' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phonenumber' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'address_detail' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'life_status' => 'required|string|in:alive,deceased',
            'marital_status' => 'nullable|string|max:255',
            'deaddate' => 'nullable|required_if:life_status,deceased|date|before_or_equal:today',
            'grave_location_url' => 'nullable|string',
            'gender' => 'required|string|in:male,female',
            'picture' => 'nullable|image|max:5120',

            // Parent sync
            'primary_parent_id' => 'nullable|exists:family_member,memberid',
            'secondary_parent_id' => 'nullable|exists:family_member,memberid',

            // Social Media
            'social_media' => 'nullable|array|max:3',
            'social_media.*.socialid' => 'required|integer',
            'social_media.*.link' => 'required|string|max:255',
        ]);

        // Concatenate address if empty
        if (empty($data['address'])) {
            $addressParts = array_filter([
                $data['address_detail'] ?? null,
                $data['city'] ?? null,
                $data['province'] ?? null,
                $data['country'] ?? null,
            ]);
            $data['address'] = implode(', ', $addressParts);
        }

        // Validation: Child must be younger than parents
        if (! empty($data['birthdate'])) {
            $parents = DB::table('family_member')
                ->whereIn('memberid', array_filter([$data['primary_parent_id'] ?? null, $data['secondary_parent_id'] ?? null]))
                ->get();
            foreach ($parents as $p) {
                if ($p->birthdate && strtotime($data['birthdate']) <= strtotime($p->birthdate)) {
                    return back()->withErrors(['birthdate' => "Time-stream error: Member cannot be older than their parent ({$p->name})."]);
                }
            }
        }

        // Validation: If deceased, deaddate must be after birthdate
        if ($data['life_status'] === 'deceased' && ! empty($data['deaddate']) && ! empty($data['birthdate'])) {
            if (strtotime($data['deaddate']) < strtotime($data['birthdate'])) {
                return back()->withErrors(['deaddate' => 'Chronology error: Death date cannot precede birth date.']);
            }
        }

        DB::transaction(function () use ($data, $id, $request) {
            $memberData = $data;
            unset(
                $memberData['primary_parent_id'],
                $memberData['secondary_parent_id'],
                $memberData['social_media'],
                $memberData['address_detail'],
                $memberData['country'],
                $memberData['province'],
                $memberData['city']
            );

            // Logic to preserve existing picture if no new one is uploaded
            if ($request->hasFile('picture')) {
                $file = $request->file('picture');
                $storageName = time().'_'.$file->getClientOriginalName();
                $file->move(base_path('../uploads/profile-pictures'), $storageName);
                $memberData['picture'] = asset('uploads/profile-pictures/'.$storageName);
            } else {
                // Remove picture from update data so it doesn't overwrite with null
                unset($memberData['picture']);
            }

            Log::info('FamilyMember Update DB Data:', ['id' => $id, 'data' => $memberData]);
            DB::table('family_member')->where('memberid', $id)->update($memberData);

            // Sync Parents
            if ($request->has('primary_parent_id')) {
                DB::table('relationship')->where('relatedmemberid', $id)->where('relationtype', 'child')->delete();
                if (! empty($data['primary_parent_id'])) {
                    DB::table('relationship')->insert(['memberid' => $data['primary_parent_id'], 'relatedmemberid' => $id, 'relationtype' => 'child']);
                }
                if (! empty($data['secondary_parent_id'])) {
                    DB::table('relationship')->insert(['memberid' => $data['secondary_parent_id'], 'relatedmemberid' => $id, 'relationtype' => 'child']);
                }
            }

            // Sync Social Media
            DB::table('ownsocial')->where('memberid', $id)->delete();
            if (! empty($data['social_media'])) {
                foreach ($data['social_media'] as $social) {
                    DB::table('ownsocial')->insert([
                        'memberid' => $id,
                        'socialid' => $social['socialid'],
                        'link' => $social['link'],
                    ]);
                }
            }
        });

        return back()->with('success', 'Member data updated successfully.');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'birthdate' => 'nullable|date|before_or_equal:today',
            'birthplace' => 'nullable|string|max:255',
            'bloodtype' => 'nullable|string|max:11',
            'job' => 'nullable|string|max:255',
            'education_status' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phonenumber' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'address_detail' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'province' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'life_status' => 'nullable|string|in:alive,deceased',
            'marital_status' => 'nullable|string|max:255',
            'deaddate' => 'nullable|required_if:life_status,deceased|date|before_or_equal:today',
            'grave_location_url' => 'nullable|string',
            'gender' => 'required|string|in:male,female',

            // Relationship specific
            'related_to' => 'required|exists:family_member,memberid',
            'secondary_parent_id' => 'nullable|exists:family_member,memberid',
            'relation_type' => 'required|string|in:spouse,child,parent',

            // Social Media
            'social_media' => 'nullable|array|max:3',
            'social_media.*.socialid' => 'required|integer',
            'social_media.*.link' => 'required|string|max:255',
        ]);

        // Concatenate address if empty
        if (empty($data['address'])) {
            $addressParts = array_filter([
                $data['address_detail'] ?? null,
                $data['city'] ?? null,
                $data['province'] ?? null,
                $data['country'] ?? null,
            ]);
            $data['address'] = implode(', ', $addressParts);
        }

        // Set Defaults
        $data['life_status'] = $data['life_status'] ?? 'alive';
        $data['marital_status'] = $data['marital_status'] ?? 'single';
        if ($data['relation_type'] === 'spouse') {
            $data['marital_status'] = 'married';
        }

        // Validation: Child must be younger than parents
        if ($data['relation_type'] === 'child' && ! empty($data['birthdate'])) {
            $parents = DB::table('family_member')
                ->whereIn('memberid', array_filter([$data['related_to'] ?? null, $data['secondary_parent_id'] ?? null]))
                ->get();
            foreach ($parents as $p) {
                if ($p->birthdate && strtotime($data['birthdate']) <= strtotime($p->birthdate)) {
                    return back()->withErrors(['birthdate' => "Natural Law Violation: Child cannot be older than their parent ({$p->name})."]);
                }
            }
        }

        // Validation: If deceased, deaddate must be after birthdate
        if ($data['life_status'] === 'deceased' && ! empty($data['deaddate']) && ! empty($data['birthdate'])) {
            if (strtotime($data['deaddate']) < strtotime($data['birthdate'])) {
                return back()->withErrors(['deaddate' => 'Chronology error: Death date cannot precede birth date.']);
            }
        }

        DB::transaction(function () use ($data, $request) {
            // 1. Generate Username automatically
            $firstName = strtolower(explode(' ', trim($data['name']))[0]);
            $username = $firstName.rand(100, 999);
            while (DB::table('user')->where('username', $username)->exists()) {
                $username = $firstName.rand(100, 999);
            }

            // 2. Create User record (Table 'user' has no created_at)
            $userId = DB::table('user')->insertGetId([
                'username' => $username,
                'password' => bcrypt('password'),
                'levelid' => 1,
            ]);

            // 3. Create the new member linked to user
            $memberData = $data;
            unset(
                $memberData['related_to'],
                $memberData['relation_type'],
                $memberData['secondary_parent_id'],
                $memberData['social_media'],
                $memberData['address_detail'],
                $memberData['country'],
                $memberData['province'],
                $memberData['city']
            );

            if ($request->hasFile('picture')) {
                $file = $request->file('picture');
                $storageName = time().'_'.$file->getClientOriginalName();
                $file->move(base_path('../uploads/profile-pictures'), $storageName);
                $memberData['picture'] = asset('uploads/profile-pictures/'.$storageName);
            } else {
                // Set default avatar based on gender matched to public/images
                $gender = strtolower($memberData['gender'] ?? 'male');
                $defaultAvatar = ($gender === 'male') ? 'avatar-male.svg' : 'avatar-female.svg';
                $memberData['picture'] = asset('images/'.$defaultAvatar);
            }

            $newMemberId = DB::table('family_member')->insertGetId(array_merge($memberData, [
                'userid' => $userId,
                'created_at' => now(),
            ]));

            // 4. Create Relationships (Table 'relationship' has no created_at)
            if ($data['relation_type'] === 'spouse') {
                DB::table('relationship')->insert(['memberid' => $data['related_to'], 'relatedmemberid' => $newMemberId, 'relationtype' => 'partner']);
                
                // Also update the existing spouse's marital status to married
                DB::table('family_member')->where('memberid', $data['related_to'])->update(['marital_status' => 'married']);
            } elseif ($data['relation_type'] === 'child') {
                DB::table('relationship')->insert(['memberid' => $data['related_to'], 'relatedmemberid' => $newMemberId, 'relationtype' => 'child']);
                if (! empty($data['secondary_parent_id'])) {
                    DB::table('relationship')->insert(['memberid' => $data['secondary_parent_id'], 'relatedmemberid' => $newMemberId, 'relationtype' => 'child']);
                }
            } elseif ($data['relation_type'] === 'parent') {
                DB::table('relationship')->insert(['memberid' => $newMemberId, 'relatedmemberid' => $data['related_to'], 'relationtype' => 'child']);

                // Link new parent as partner to existing parents of the child
                $existingParents = DB::table('relationship')
                    ->where('relatedmemberid', $data['related_to'])
                    ->where('relationtype', 'child')
                    ->where('memberid', '!=', $newMemberId)
                    ->pluck('memberid');

                foreach ($existingParents as $parentId) {
                    DB::table('relationship')->updateOrInsert(
                        [
                            'memberid' => min($newMemberId, $parentId),
                            'relatedmemberid' => max($newMemberId, $parentId),
                            'relationtype' => 'partner'
                        ],
                        []
                    );
                    
                    // Also ensure they are marked as married
                    DB::table('family_member')->whereIn('memberid', [$newMemberId, $parentId])->update(['marital_status' => 'married']);
                }
            }

            // 5. Create Social Media records (Table 'ownsocial' has no created_at)
            if (! empty($data['social_media'])) {
                foreach ($data['social_media'] as $social) {
                    DB::table('ownsocial')->insert([
                        'memberid' => $newMemberId,
                        'socialid' => $social['socialid'],
                        'link' => $social['link'],
                    ]);
                }
            }
        });

        return back()->with('success', 'New family member added successfully.');
    }

    public function markAsDeceased(Request $request, $id)
    {
        $data = $request->validate([
            'dead_date' => 'required|date',
            'grave_location_url' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $id, $request) {
            $member = DB::table('family_member')->where('memberid', $id)->first();

            DB::table('family_member')->where('memberid', $id)->update([
                'life_status' => 'deceased',
                'deaddate' => $data['dead_date'],
                'grave_location_url' => $data['grave_location_url'],
                'marital_status' => 'widowed', // Deceased is widowed
            ]);

            // Find surviving spouses and mark them as widowed
            $spouses = DB::table('relationship')
                ->where('relationtype', 'partner')
                ->where(function ($q) use ($id) {
                    $q->where('memberid', $id)->orWhere('relatedmemberid', $id);
                })
                ->get();

            foreach ($spouses as $s) {
                $survivorId = ($s->memberid == $id) ? $s->relatedmemberid : $s->memberid;
                DB::table('family_member')->where('memberid', $survivorId)->update(['marital_status' => 'widowed']);
            }

            // Succession Logic: If the deceased is the Admin (Role 2)
            if ($member && $member->roleid == 2 && $member->userid) {
                $setting = DB::table('leader_succession_settings')->where('owner_userid', $member->userid)->first();

                // Fallback to global setting if specific owner setting doesn't exist
                if (! $setting) {
                    $setting = DB::table('leader_succession_settings')->first();
                }

                if ($setting && $setting->heir_memberid) {
                    $heirMember = DB::table('family_member')->where('memberid', $setting->heir_memberid)->first();

                    if ($heirMember && $heirMember->life_status !== 'deceased') {
                        // Revoke admin from deceased
                        DB::table('family_member')->where('memberid', $id)->update(['roleid' => null]);

                        // Grant admin to heir
                        DB::table('family_member')->where('memberid', $setting->heir_memberid)->update(['roleid' => 2]);

                        // Log history with clear intent
                        DB::table('leader_succession_histories')->insert([
                            'owner_userid' => $heirMember->userid ?? $member->userid, // Use heir's userid if available, else fallback
                            'leader_memberid' => $setting->heir_memberid,
                            'leader_name' => $heirMember->name,
                            'source' => 'Automatic Succession: New Head of Family (Role ID 2)',
                            'changed_by_userid' => $request->user()->userid ?? $member->userid, // Action performed by
                            'changed_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);

                        // Clear the setting since it has been executed
                        DB::table('leader_succession_settings')->where('id', $setting->id)->delete();
                    }
                }
            }
        });

        return back()->with('success', 'Member marked as deceased. May they rest in peace. Associated successions (if any) have been executed.');
    }

    public function requestDeletion(Request $request, $id)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:1000',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $currentUser = auth()->user();
        $member = DB::table('family_member')->where('userid', $currentUser->userid)->first();

        if (! $member) {
            return back()->with('error', 'You must be linked to a family member to request deletions.');
        }

        $documentUrl = null;
        if ($request->hasFile('document')) {
            $file = $request->file('document');

            // Capture metadata before moving (WAMP safety)
            $storageName = time().'_deletion_proof_'.$file->getClientOriginalName();
            $file->move(base_path('../uploads/system'), $storageName);
            $documentUrl = asset('uploads/system/'.$storageName);
        }

        DB::table('relationship_validations')->insert([
            'requested_by_member_id' => $member->memberid,
            'target_member_id' => $id,
            'action_type' => 'delete_member', // Changed to delete_member since the migration updated the enum to support it
            'document_path' => $documentUrl,
            'reason' => $data['reason'],
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('validation.index')->with('success', 'Deletion request submitted for validation.');
    }

    public function destroy($id)
    {
        $member = DB::table('family_member')->where('memberid', $id)->whereNull('deleted_at')->first();

        if (! $member) {
            return back()->with('error', 'Member not found or already deleted.');
        }

        DB::transaction(function () use ($member, $id) {
            $now = now();
            $dependentIds = $this->findDependentsRecursively($id);

            // Delete dependents first
            foreach ($dependentIds as $depId) {
                // Soft delete relationships
                DB::table('relationship')
                    ->where(function ($q) use ($depId) {
                        $q->where('memberid', $depId)->orWhere('relatedmemberid', $depId);
                    })
                    ->update(['deleted_at' => $now]);

                // Soft delete member with cascade flag
                DB::table('family_member')->where('memberid', $depId)->update([
                    'deleted_at' => $now,
                    'deleted_by_cascade_from' => $id,
                ]);

                // Soft delete associated user account
                $depMember = DB::table('family_member')->where('memberid', $depId)->first();
                if ($depMember && $depMember->userid) {
                    DB::table('user')->where('userid', $depMember->userid)->update(['deleted_at' => $now]);
                }
            }

            // Finally soft delete the core member
            DB::table('relationship')
                ->where(function ($q) use ($id) {
                    $q->where('memberid', $id)->orWhere('relatedmemberid', $id);
                })
                ->update(['deleted_at' => $now]);

            DB::table('family_member')->where('memberid', $id)->update(['deleted_at' => $now]);

            if ($member->userid) {
                DB::table('user')->where('userid', $member->userid)->update(['deleted_at' => $now]);
            }
        });

        return back()->with('success', 'Core member and all related descendants/partners moved to Recycle Bin.');
    }

    private function findDependentsRecursively($memberId, &$visited = [])
    {
        if (in_array($memberId, $visited)) {
            return [];
        }
        $visited[] = $memberId;

        $dependents = [];

        // 1. Find Partners
        $partners = DB::table('relationship')
            ->where('relationtype', 'partner')
            ->where(function ($q) use ($memberId) {
                $q->where('memberid', $memberId)->orWhere('relatedmemberid', $memberId);
            })
            ->get();

        foreach ($partners as $p) {
            $pId = ($p->memberid == $memberId) ? $p->relatedmemberid : $p->memberid;
            if (! in_array($pId, $visited)) {
                $dependents[] = $pId;
                // We don't necessarily cascade from a spouse's family,
                // but we might want to check if they have children from this union
            }
        }

        // 2. Find Children
        $children = DB::table('relationship')
            ->where('relationtype', 'child')
            ->where('memberid', $memberId)
            ->pluck('relatedmemberid')
            ->toArray();

        foreach ($children as $cId) {
            if (! in_array($cId, $visited)) {
                $dependents[] = $cId;
                // Recursive call for descendants
                $dependents = array_merge($dependents, $this->findDependentsRecursively($cId, $visited));
            }
        }

        return array_unique($dependents);
    }
}
