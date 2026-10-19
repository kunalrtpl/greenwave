<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Audit entry for a sample request line.
 *
 * Every admin edit, approval, decline, ready-mark and dispatch lands here so
 * the detail screen can show what the field user originally asked for next to
 * what the admin turned it into.
 */
class SamplingRequestLog extends Model
{
    const ACTION_CREATED      = 'created';
    const ACTION_UPDATED      = 'updated';
    const ACTION_APPROVED     = 'approved';
    const ACTION_DECLINED     = 'declined';
    const ACTION_MARKED_READY = 'marked_ready';
    const ACTION_DISPATCHED   = 'dispatched';
    const ACTION_RECEIVED     = 'received';
    const ACTION_CLOSED       = 'closed';

    const ACTION_LABELS = [
        self::ACTION_CREATED      => 'Request received',
        self::ACTION_UPDATED      => 'Details changed',
        self::ACTION_APPROVED     => 'Approved',
        self::ACTION_DECLINED     => 'Declined',
        self::ACTION_MARKED_READY => 'Marked ready',
        self::ACTION_DISPATCHED   => 'Dispatched',
        self::ACTION_RECEIVED     => 'Received',
        self::ACTION_CLOSED       => 'Closed',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user(){
        return $this->belongsTo('App\User');
    }

    public function sampling_request(){
        return $this->belongsTo('App\SamplingRequest');
    }

    public function getActionLabelAttribute(){
        return isset(self::ACTION_LABELS[$this->action]) ? self::ACTION_LABELS[$this->action] : ucfirst((string) $this->action);
    }

    /**
     * Write an entry. $changes is a field => ['from' => …, 'to' => …] map;
     * an entry with no changes and no remarks is still worth keeping because
     * it records who did what and when.
     */
    public static function record($requestId, $action, array $changes = [], $remarks = null, $userId = null)
    {
        return self::create([
            'sampling_request_id' => $requestId,
            'user_id'             => $userId !== null ? $userId : (\Auth::check() ? \Auth::user()->id : null),
            'action'              => $action,
            'changes'             => $changes ?: null,
            'remarks'             => $remarks,
        ]);
    }
}
