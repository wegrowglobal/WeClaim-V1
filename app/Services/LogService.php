<?php

namespace App\Services;

use App\Models\System\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class LogService
{
    /**
     * Log an activity in the system
     *
     * @param string $type The type of log (admin, user, system)
     * @param string $activityType The specific activity type
     * @param string $description Description of the activity
     * @param array|null $data Additional data to store with the log
     * @return ActivityLog
     */
    public function log(string $type, string $activityType, string $description, array $data = null): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'type' => $type,
            'activity_type' => $activityType,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'data' => $data,
        ]);
    }

    /**
     * Log an admin activity
     *
     * @param string $activityType
     * @param string $description
     * @param array|null $data
     * @return ActivityLog
     */
    public function logAdmin(string $activityType, string $description, array $data = null): ActivityLog
    {
        return $this->log('admin', $activityType, $description, $data);
    }

    /**
     * Log a user activity
     *
     * @param string $activityType
     * @param string $description
     * @param array|null $data
     * @return ActivityLog
     */
    public function logUser(string $activityType, string $description, array $data = null): ActivityLog
    {
        return $this->log('user', $activityType, $description, $data);
    }

    /**
     * Log a system activity
     *
     * @param string $activityType
     * @param string $description
     * @param array|null $data
     * @return ActivityLog
     */
    public function logSystem(string $activityType, string $description, array $data = null): ActivityLog
    {
        return $this->log('system', $activityType, $description, $data);
    }

    /**
     * Log a user login activity
     *
     * @param int $userId
     * @param string $username
     * @return ActivityLog
     */
    public function logLogin(int $userId, string $username): ActivityLog
    {
        return $this->log('user', 'login', "User {$username} logged in", [
            'user_id' => $userId,
            'username' => $username
        ]);
    }

    /**
     * Log a user logout activity
     *
     * @return ActivityLog|null
     */
    public function logLogout(): ?ActivityLog
    {
        if (Auth::check()) {
            $user = Auth::user();
            return $this->log('user', 'logout', "User {$user->name} logged out", [
                'user_id' => $user->id,
                'username' => $user->name
            ]);
        }
        
        return null;
    }

    /**
     * Log a claim creation activity
     *
     * @param int $claimId
     * @param string $claimReference
     * @param array $claimData
     * @return ActivityLog
     */
    public function logClaimCreated(int $claimId, string $claimReference, array $claimData = []): ActivityLog
    {
        return $this->log('user', 'claim_created', "Claim {$claimReference} was created", [
            'claim_id' => $claimId,
            'reference' => $claimReference,
            'claim_data' => $claimData
        ]);
    }

    /**
     * Log a claim update activity
     *
     * @param int $claimId
     * @param string $claimReference
     * @param array $changes
     * @return ActivityLog
     */
    public function logClaimUpdated(int $claimId, string $claimReference, array $changes = []): ActivityLog
    {
        return $this->log('user', 'claim_updated', "Claim {$claimReference} was updated", [
            'claim_id' => $claimId,
            'reference' => $claimReference,
            'changes' => $changes
        ]);
    }

    /**
     * Log a claim status change activity
     *
     * @param int $claimId
     * @param string $claimReference
     * @param string $oldStatus
     * @param string $newStatus
     * @return ActivityLog
     */
    public function logClaimStatusChanged(int $claimId, string $claimReference, string $oldStatus, string $newStatus): ActivityLog
    {
        return $this->log('user', 'claim_status_changed', "Claim {$claimReference} status changed from {$oldStatus} to {$newStatus}", [
            'claim_id' => $claimId,
            'reference' => $claimReference,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
    }

    /**
     * Log a claim status change with a note.
     *
     * @param int $claim_id
     * @param string $old_status
     * @param string $new_status
     * @param string $note
     * @return ActivityLog
     */
    public function logClaimStatusChange($claim_id, $old_status, $new_status, $note = null)
    {
        $description = "Claim status changed from {$old_status} to {$new_status}";
        
        $data = [
            'claim_id' => $claim_id,
            'old_status' => $old_status,
            'new_status' => $new_status
        ];
        
        if ($note) {
            $data['note'] = $note;
        }
        
        return $this->logAdmin('claim_status_change', $description, $data);
    }
    
    /**
     * Log a claim note addition.
     *
     * @param int $claim_id
     * @param string $note
     * @return ActivityLog
     */
    public function logClaimNote($claim_id, $note)
    {
        $description = "Note added to claim #{$claim_id}";
        
        $data = [
            'claim_id' => $claim_id,
            'note' => $note
        ];
        
        return $this->logAdmin('claim_note_added', $description, $data);
    }
    
    /**
     * Log a document download.
     *
     * @param int $claim_id
     * @param int $document_id
     * @param string $document_name
     * @return ActivityLog
     */
    public function logDocumentDownload($claim_id, $document_id, $document_name)
    {
        $description = "Downloaded document '{$document_name}' from claim #{$claim_id}";
        
        $data = [
            'claim_id' => $claim_id,
            'document_id' => $document_id,
            'document_name' => $document_name
        ];
        
        return $this->logAdmin('document_downloaded', $description, $data);
    }
    
    /**
     * Log an export action.
     *
     * @param string $export_type
     * @param string $format
     * @param array $filters
     * @return ActivityLog
     */
    public function logExport($export_type, $format, $filters = [])
    {
        $description = "Exported {$export_type} in {$format} format";
        
        $data = [
            'export_type' => $export_type,
            'format' => $format,
            'filters' => $filters
        ];
        
        return $this->logAdmin('export', $description, $data);
    }
    
    /**
     * Get recent activities by user.
     *
     * @param int $user_id
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentUserActivities($user_id, $limit = 10)
    {
        return ActivityLog::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get recent admin activities.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRecentAdminActivities($limit = 20)
    {
        return ActivityLog::where('type', 'admin')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * Get activities related to a specific claim.
     *
     * @param int $claim_id
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getClaimActivities($claim_id)
    {
        return ActivityLog::whereJsonContains('data->claim_id', $claim_id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
} 