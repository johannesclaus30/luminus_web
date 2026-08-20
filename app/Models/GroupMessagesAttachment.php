<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMessagesAttachment extends Model
{
    protected $table = 'group_messages_attachments';

    protected $fillable = [
        'group_message_id',
        'attachment_type',
        'attachment_path',
    ];

    public function message()
    {
        return $this->belongsTo(GroupMessage::class, 'group_message_id');
    }
}