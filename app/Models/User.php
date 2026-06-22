<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Spatie\Permission\Traits\HasRoles;

use Illuminate\Support\Facades\Session;

use App\Models\OrgStructureTree;
use App\Models\Post;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'position_id',
        'name',
        'email',
        'password',
        'profile_pic',
        'dark_mode',
        'skin',
        'google_id',
        'last_activity',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Dynamically set the database connection based on the session.
     */
    public function getConnectionName()
    {
        return Session::get('db_connection', 'mysql'); // Default to 'mysql' if not set
    }

    public function isOnline() {
        return $this->last_activity >= now()->subMinutes(2);
    }

    public function adminlte_image()
    {
        // random image
        return !empty($this->profile_pic) ? asset($this->profile_pic) : asset('images/Default_pfp.svg.png');
    }

    public function adminlte_desc()
    {
        return implode(', ', $this->getRoleNames()->toArray()) ?? '-';
    }

    public function adminlte_profile_url()
    {
        return 'profile/'.encrypt($this->id);
    }

    public function company() {
        return $this->belongsTo('App\Models\Company');
    }

    public function position() {
        return $this->belongsTo('App\Models\Position');
    }
    /**
     * Get the org structure trees associated with the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function structure_trees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrgStructureTree::class, 'user_id', 'id');
    }

    public function posts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Recursively collect subordinate user IDs in PHP memory up to a max depth.
     *
     * @param  \App\Models\OrgStructureTree  $structure
     * @param  array<int, array<int, int>>  &$subordinateIds
     * @param  \Illuminate\Support\Collection $allStructuresGrouped
     * @param  int  $level
     * @return void
     */
    private function collectSubordinateIdsInMemory(OrgStructureTree $structure, array &$subordinateIds, $allStructuresGrouped, int $level = 1): void
    {
        if ($level > 5) {
            return;
        }

        if ($structure->user_id) {
            $subordinateIds['level_' . $level][] = $structure->user_id;
        }

        // Fetch children directly from the loaded PHP Collection, NOT the database.
        // If there are no children, it returns an empty array.
        $children = $allStructuresGrouped->get($structure->id, []);

        foreach ($children as $child) {
            $this->collectSubordinateIdsInMemory($child, $subordinateIds, $allStructuresGrouped, $level + 1);
        }
    }

    /**
     * Get all supervisor user IDs in the reporting chain.
     *
     * @return array<int>
     */
    public function getSupervisorIds(): array
    {
        $supervisorIds = [];
        $structure = $this->structure_trees()->first();

        if (!$structure) {
            return [];
        }

        // Fetch ALL structures into memory keyed by their ID to prevent N+1 going UP the chain
        $allStructuresKeyed = OrgStructureTree::all()->keyBy('id');

        while ($structure && $structure->reports_to_id) {
            // Find the superior from the in-memory collection instead of OrgStructureTree::find()
            $superior = $allStructuresKeyed->get($structure->reports_to_id);

            if ($superior && $superior->user_id) {
                $supervisorIds[] = $superior->user_id;
            }

            $structure = $superior;
        }

        return array_unique($supervisorIds);
    }

    /**
     * Get the immediate superior's user ID.
     *
     * @return int|null
     */
    public function getImmediateSuperiorId(): ?int
    {
        $structure = $this->structure_trees()->first();

        if ($structure && $structure->reports_to_id) {
            $superior = OrgStructureTree::find($structure->reports_to_id);

            return $superior?->user_id;
        }

        return null;
    }
}
