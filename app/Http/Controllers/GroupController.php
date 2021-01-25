<?php

namespace App\Http\Controllers;

use Auth;
use Image;
use App\User;
use App\Group;
use App\GroupUser;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{
    public function store(Request $request)
    {
        $group = Group::create($request->all());
        if ($request->hasFile('group_image')) {
            $group_image = $request->file('group_image');
            $filename = $group->id . '-' . str_replace(' ', '', $group->name) . '.' . $group_image->getClientOriginalExtension();
            Image::make($group_image)->save(public_path('/uploads/groups/' . $filename));
            $group->group_image = $filename;
            $group->update();
        }
        // group created by
        $group_user = new GroupUser();
        $group_user->user_id = Auth::user()->id;
        $group_user->group_id = $group->id;
        $group_user->is_admin = 1;
        $group_user->save();
        if (isset($request->ids)) {
            $member_ids = explode(',', $request->ids);
            foreach ($member_ids as $member_id) {
                $group_user = new GroupUser();
                $group_user->user_id = $member_id;
                $group_user->group_id = $group->id;
                $group_user->save();
            }
        }
        return response()->json([
            'status' => 200,
            'message' => 'Group created succcessfully'
        ], 200);
    }
    public function userGroups(Request $request)
    {
        $group_ids = GroupUser::where('user_id', Auth::user()->id)->pluck('group_id');
        // GroupUser::where('group_id', $this->id)->where('user_id', \Auth::user()->id)->first();
        $data = Group::whereIn('id', $group_ids)->with(['groupUsers.user'])->get();
        // dd(Group::whereIn('id', $group_ids)->with(['groupUsers'])->get());
        return response()->json([
            'data' => $data,
            'status' => 200,
            'message' => 'user groups loaded successfully'
        ], 200);
    }
    public function leave(Request $request)
    {
        $group = Group::find($request->group_id);
        $group_user = GroupUser::where('group_id', $group->id)->where('user_id', Auth::user()->id)->first();
        if ($group_user->is_admin == 1) {
            $oldest_member = GroupUser::where('group_id', $group->id)->orderBy('created_at', 'ASC')->first();
            $oldest_member->is_admin = 1;
            $oldest_member->update();
        }
        $group_user->delete();
        return response()->json([
            'status' => 200,
            'message' => 'you left the group successfully'
        ], 200);
    }
    public function kickMember(Request $request)
    {
        $logged_in_user_admin = GroupUser::where('group_id', $request->group_id)->where('user_id', Auth::user()->id)->first();
        if ($logged_in_user_admin->is_admin != 1) {
            return response()->json([
                'status' => 400,
                'message' => 'only group admin can kick'
            ], 400);
        }

        $group_member = GroupUser::where('group_id', $request->group_id)->where('user_id', $request->kicked_user_id)->first();
        if ($group_member->is_admin == 1) {
            return response()->json([
                'status' => 400,
                'message' => 'You cannot kick an admin'
            ], 400);
        }
        $group_member->delete();
        return response()->json([
            'status' => 200,
            'message' => 'user has been kicked'
        ], 200);
    }
    public function addMembers(Request $request)
    {
        $members = explode(',', $request->member_ids);
        $group = Group::find($request->group_id);
        if (count($members) > 1) {
            foreach ($members as $member) {
                $existing_member = $group->groupUsers->where('user_id', $member)->first();
                if ($existing_member == null) {
                    $group_user = new GroupUser();
                    $group_user->user_id = $member;
                    $group_user->group_id = $group->id;
                    $group_user->is_admin = 0;
                    $group_user->save();
                }
            }
        } else {
            $group_user = new GroupUser();
            $group_user->user_id = $members;
            $group_user->group_id = $group->id;
            $group_user->is_admin = 0;
            $group_user->save();
        }
        return response()->json([
            'status' => 200,
            'message' => 'members added to the group successfully',
        ], 200);
    }
    public function listMembers(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_id' => ['required', Rule::exists('groups', 'id')->whereNull('deleted_at')],
            // 'message' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->returnError($validator->errors()->first());
        }
        $group = Group::findOrFail($request->group_id);
        $group_users = GroupUser::where('group_id', $group->id)->pluck('user_id');
        $data = [
            'members' => User::whereIn('id', $group_users)->get(),
            'group_id' => $group->id
        ];
        return response()->json([
            'data' => $data,
            'status' => 200,
            'message' => 'group members list'
        ], 200);
    }

    public function destroy($id)
    {
        $group = Group::find($id);
        if (!$group) {
            return response()->json([
                'message' => 'Group Not Found',
                'status' => 404,
            ], 404);
        }
        try {
            DB::beginTransaction();
            $group->groupUsers()->delete();
            $group->delete();
            DB::commit();
            return response()->json(['message' => 'Group Deleted'], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Unable to delete entry, ' . $e->getMessage()], 500);
        }
    }
}
