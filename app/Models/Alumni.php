<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // ← ADD THIS
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alumni extends Model
{
    use HasFactory, SoftDeletes; // ← ADD SoftDeletes

    protected $table = 'alumnis';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'sex',
        'year_graduated',
        'alumni_photo',
        'alumni_bio',
        'student_id_number',
        'email',
        'phone_number',
        'password_hash',
        'verification_status',
        'program',
        'card_photo',
        'needs_password_change',
        'account_status',
        'is_online',
        'push_token',
        // ↓ ADD THESE NEW FIELDS ↓
        'restriction_reason',
        'restriction_comment',
        'restricted_by',
        'restricted_at',
        'deleted_at', // For soft delete
    ];

    protected $hidden = [
        'password_hash',
        'push_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'year_graduated' => 'date',
        'is_online' => 'boolean',
        'needs_password_change' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'restricted_at' => 'datetime',
        'deleted_at' => 'datetime', // ← ADD THIS
    ];

    /**
     * Get the alumni's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->last_name}, {$this->first_name}" . ($this->middle_name ? " {$this->middle_name}" : '');
    }

    /**
     * Get the alumni's initials.
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * Get the alumni's photo URL.
     */
    public function getAlumniPhotoUrlAttribute(): ?string
    {
        if ($this->alumni_photo) {
            if (filter_var($this->alumni_photo, FILTER_VALIDATE_URL)) {
                return $this->alumni_photo;
            }
            return asset('storage/' . $this->alumni_photo);
        }
        return null;
    }

    // ============================================
    // STATUS CHECK METHODS
    // ============================================

    /**
     * Check if the alumni account is active.
     */
    public function isActive(): bool
    {
        return $this->account_status == 1 && $this->deleted_at === null;
    }

    /**
     * Check if the alumni account is restricted.
     */
    public function isRestricted(): bool
    {
        return $this->account_status == 0 && $this->deleted_at === null;
    }

    /**
     * Check if the alumni account is archived (soft deleted).
     */
    public function isArchived(): bool
    {
        return $this->deleted_at !== null;
    }

    // ============================================
    // SCOPE METHODS
    // ============================================

    /**
     * Scope a query to only include active alumni.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at')->where('account_status', 1);
    }

    /**
     * Scope a query to only include restricted alumni.
     */
    public function scopeRestricted($query)
    {
        return $query->whereNull('deleted_at')->where('account_status', 0);
    }

    /**
     * Scope a query to only include archived alumni.
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    /**
     * Scope a query to only include verified alumni.
     */
    public function scopeVerified($query): void
    {
        $query->where('verification_status', 'verified');
    }

    /**
     * Scope a query to only include online alumni.
     */
    public function scopeOnline($query): void
    {
        $query->where('is_online', true);
    }

    // ============================================
    // RESTRICTION REASONS
    // ============================================

    /**
     * Get the list of predefined restriction reasons.
     */
    public static function getRestrictionReasons(): array
    {
        return [
            'violation' => 'Violation of Community Guidelines',
            'spam' => 'Spam or Unsolicited Messages',
            'fake_account' => 'Fake or Misleading Account Information',
            'harassment' => 'Harassment or Bullying',
            'inappropriate_content' => 'Posting Inappropriate Content',
            'unauthorized_access' => 'Unauthorized Access Attempts',
            'other' => 'Other (Please Specify)',
        ];
    }

    /**
     * Get the human-readable restriction reason label.
     */
    public function getRestrictionReasonLabelAttribute(): ?string
    {
        if (!$this->restriction_reason) {
            return null;
        }
        $reasons = self::getRestrictionReasons();
        return $reasons[$this->restriction_reason] ?? $this->restriction_reason;
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the admin who restricted this account.
     */
    public function restrictedBy()
    {
        return $this->belongsTo(Admin::class, 'restricted_by');
    }

    /**
     * Get the addresses associated with the alumni.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the employment records associated with the alumni.
     */
    public function employments(): HasMany
    {
        return $this->hasMany(AlumniEmployment::class);
    }

    /**
     * Get the skills associated with the alumni.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(AlumniSkill::class);
    }

    /**
     * Get the tracer responses associated with the alumni.
     */
    public function tracerResponses(): HasMany
    {
        return $this->hasMany(TracerResponse::class);
    }

    /**
     * Get the event registrations associated with the alumni.
     */
    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get the followers (alumni who follow this alumni).
     */
    public function followers(): HasMany
    {
        return $this->hasMany(Follower::class, 'followed_alumni_id');
    }

    /**
     * Get the following (alumni this alumni follows).
     */
    public function following(): HasMany
    {
        return $this->hasMany(Follower::class, 'follower_alumni_id');
    }

    /**
     * Get the posts associated with the alumni.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the messages sent by this alumni.
     */
    public function messagesSent(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id')->where('sender_type', 'alumni');
    }

    /**
     * Get the messages received by this alumni.
     */
    public function messagesReceived(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id')->where('receiver_type', 'alumni');
    }

    /**
     * Get the comments made by this alumni.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the reactions made by this alumni.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * Get the reposts made by this alumni.
     */
    public function reposts(): HasMany
    {
        return $this->hasMany(Repost::class);
    }

    /**
     * Get the group chat memberships for this alumni.
     */
    public function groupChatMembers(): HasMany
    {
        return $this->hasMany(GroupChatMember::class);
    }

    /**
     * Get the group chats this alumni is a member of.
     */
    public function groupChats(): BelongsToMany
    {
        return $this->belongsToMany(GroupChat::class, 'group_chat_members');
    }

    /**
     * Get the dismissed notifications for this alumni.
     */
    public function dismissedNotifications(): HasMany
    {
        return $this->hasMany(DismissedNotification::class);
    }

    /**
     * Get the favorite chats for this alumni.
     */
    public function favoriteChats(): HasMany
    {
        return $this->hasMany(FavoriteChat::class);
    }

    /**
     * Get the DM settings for this alumni.
     */
    public function dmSettings(): HasMany
    {
        return $this->hasMany(DmSetting::class);
    }

    /**
     * Get the calls made by this alumni.
     */
    public function callsMade(): HasMany
    {
        return $this->hasMany(Call::class, 'caller_id');
    }

    /**
     * Get the calls received by this alumni.
     */
    public function callsReceived(): HasMany
    {
        return $this->hasMany(Call::class, 'receiver_id');
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if the alumni is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if the alumni is pending verification.
     */
    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    /**
     * Check if the alumni is rejected.
     */
    public function isRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    /**
     * Get the number of followers.
     */
    public function getFollowersCountAttribute(): int
    {
        return $this->followers()->count();
    }

    /**
     * Get the number of following.
     */
    public function getFollowingCountAttribute(): int
    {
        return $this->following()->count();
    }

    /**
     * Get the number of posts.
     */
    public function getPostsCountAttribute(): int
    {
        return $this->posts()->count();
    }

    /**
     * Check if this alumni follows another alumni.
     */
    public function isFollowing(int $alumniId): bool
    {
        return $this->following()->where('followed_alumni_id', $alumniId)->exists();
    }

    /**
     * Check if this alumni is followed by another alumni.
     */
    public function isFollowedBy(int $alumniId): bool
    {
        return $this->followers()->where('follower_alumni_id', $alumniId)->exists();
    }
}