<?php

namespace App\Services;

use App\Models\CommentModel;
use App\Services\ActivityLogService;

class CommentService
{
    protected CommentModel $commentModel;
    protected ActivityLogService $logService;
    protected $db;

    public function __construct()
    {
        $this->commentModel = new CommentModel();
        $this->logService = new ActivityLogService();
        $this->db = \Config\Database::connect();
    }

    /**
     * Create a new comment
     */
    public function createComment(int $issueId, int $userId, string $content): int
    {
        // Parse mentions from content
        $mentions = $this->parseMentions($content);

        $commentId = $this->commentModel->insert([
            'issue_id' => $issueId,
            'user_id' => $userId,
            'content' => $content,
            'mentions' => !empty($mentions) ? json_encode($mentions) : null,
            'edited' => false
        ]);

        $this->logService->log(
            'create',
            'comment',
            $commentId,
            "Comment created on issue {$issueId}"
        );

        return $commentId;
    }

    /**
     * Get comments for an issue
     */
    public function getCommentsByIssue(int $issueId): array
    {
        return $this->commentModel
            ->select('comments.*, users.email as user_email, users.full_name as user_name, users.photo as user_photo')
            ->join('users', 'users.id = comments.user_id')
            ->where('comments.issue_id', $issueId)
            ->orderBy('comments.created_at', 'ASC')
            ->findAll();
    }

    /**
     * Get comment by ID
     */
    public function getCommentById(int $commentId): ?array
    {
        return $this->commentModel
            ->select('comments.*, users.email as user_email, users.full_name as user_name')
            ->join('users', 'users.id = comments.user_id')
            ->where('comments.id', $commentId)
            ->first();
    }

    /**
     * Update comment
     */
    public function updateComment(int $commentId, int $userId, string $content): bool
    {
        // Check ownership
        $comment = $this->commentModel->find($commentId);
        if (!$comment || $comment['user_id'] != $userId) {
            throw new \RuntimeException('Only comment owner can update comment');
        }

        // Parse mentions
        $mentions = $this->parseMentions($content);

        $result = $this->commentModel->update($commentId, [
            'content' => $content,
            'mentions' => !empty($mentions) ? json_encode($mentions) : null,
            'edited' => true
        ]);

        if ($result) {
            $this->logService->log(
                'update',
                'comment',
                $commentId,
                'Comment updated'
            );
        }

        return $result;
    }

    /**
     * Delete comment
     */
    public function deleteComment(int $commentId, int $userId): bool
    {
        // Check ownership
        $comment = $this->commentModel->find($commentId);
        if (!$comment || $comment['user_id'] != $userId) {
            throw new \RuntimeException('Only comment owner can delete comment');
        }

        $result = $this->commentModel->delete($commentId);

        if ($result) {
            $this->logService->log(
                'delete',
                'comment',
                $commentId,
                'Comment deleted'
            );
        }

        return $result;
    }

    /**
     * Get mentions from comment content
     * Parses @username patterns and returns array of user IDs
     */
    protected function parseMentions(string $content): array
    {
        // Pattern to match @username
        preg_match_all('/@(\w+)/', $content, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $usernames = array_unique($matches[1]);
        $userIds = [];

        // Get user IDs by email (assuming username is email)
        foreach ($usernames as $username) {
            $user = $this->db->table('users')
                ->where('email', $username)
                ->orWhere('email', 'LIKE', "%{$username}%")
                ->get()
                ->getRowArray();

            if ($user) {
                $userIds[] = $user['id'];
            }
        }

        return array_unique($userIds);
    }

    /**
     * Get comments mentioning a user
     */
    public function getCommentsMentioningUser(int $userId): array
    {
        $comments = $this->commentModel
            ->where('mentions IS NOT NULL')
            ->findAll();

        $filtered = [];
        foreach ($comments as $comment) {
            $mentions = json_decode($comment['mentions'], true);
            if (is_array($mentions) && in_array($userId, $mentions)) {
                $filtered[] = $comment;
            }
        }

        return $filtered;
    }

    /**
     * Get comment count for an issue
     */
    public function getCommentCount(int $issueId): int
    {
        return $this->commentModel
            ->where('issue_id', $issueId)
            ->countAllResults();
    }
}

