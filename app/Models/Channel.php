<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An admin-created persistent public channel (MUC room). The room itself lives in
 * ejabberd at {localpart}@{muc_domain}; this row is the directory entry KewlChats
 * shows in the rooms showcase.
 */
#[Fillable(['localpart', 'name', 'description', 'created_by'])]
class Channel extends Model
{
    /**
     * The channel's full MUC JID, e.g. "lounge@conference.kewlchats.net".
     */
    public function jid(): string
    {
        return $this->localpart.'@'.config('xmpp.muc_domain');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
