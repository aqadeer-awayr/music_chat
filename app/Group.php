<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $appends = ['number_of_members'];
    protected $fillable = ['name', 'description', 'group_image', 'is_active'];
    public function groupUsers()
    {
        return $this->hasMany(GroupUser::class, 'group_id', 'id');
    }
    public function getNumberOfMembersAttribute()
    {
        // return $this->groupUsers->count();
        return GroupUser::where('group_id', $this->id)->count();
    }
}
