<?php

namespace App\Services;

use App\Models\AttachmentModel;
use App\Services\ActivityLogService;
use CodeIgniter\Files\File;

class AttachmentService
{
    protected AttachmentModel $attachmentModel;
    protected ActivityLogService $logService;
    protected $uploadPath;

    public function __construct()
    {
        $this->attachmentModel = new AttachmentModel();
        $this->logService = new ActivityLogService();
        
        // Set upload path
        $this->uploadPath = WRITEPATH . 'uploads/attachments/';
        
        // Create directory if not exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Upload file attachment for issue
     */
    public function uploadAttachment(int $issueId, int $userId, $file, ?string $description = null): int
    {
        if (!$file || !$file->isValid()) {
            throw new \RuntimeException('Invalid file uploaded');
        }

        // Validate file size (max 10MB)
        $maxSize = 10 * 1024 * 1024; // 10MB
        if ($file->getSize() > $maxSize) {
            throw new \RuntimeException('File size exceeds maximum limit of 10MB');
        }

        // Generate unique file name
        $originalName = $file->getName();
        $extension = $file->getExtension();
        $fileName = uniqid('att_', true) . '_' . time() . '.' . $extension;
        $relativePath = 'uploads/attachments/' . $fileName;
        $filePath = $this->uploadPath . $fileName;

        // Move uploaded file
        if (!$file->move($this->uploadPath, $fileName)) {
            throw new \RuntimeException('Failed to save uploaded file');
        }

        // Determine file type
        $mimeType = $file->getMimeType();
        $fileType = $this->determineFileType($mimeType, $extension);

        // Save to database (store relative path for easier access)
        $attachmentId = $this->attachmentModel->insert([
            'issue_id' => $issueId,
            'user_id' => $userId,
            'original_name' => $originalName,
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'file_size' => $file->getSize(),
            'mime_type' => $mimeType,
            'file_type' => $fileType,
            'description' => $description,
        ]);

        $this->logService->log(
            'create',
            'attachment',
            $attachmentId,
            "File uploaded: {$originalName}"
        );

        return $attachmentId;
    }

    /**
     * Get all attachments for an issue
     */
    public function getAttachmentsByIssue(int $issueId): array
    {
        return $this->attachmentModel
            ->where('issue_id', $issueId)
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get attachment by ID
     */
    public function getAttachmentById(int $attachmentId): ?array
    {
        return $this->attachmentModel->find($attachmentId);
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment(int $attachmentId, int $userId): bool
    {
        $attachment = $this->getAttachmentById($attachmentId);
        
        if (!$attachment) {
            throw new \RuntimeException('Attachment not found');
        }

        // Check if user is the uploader or has permission
        if ($attachment['user_id'] != $userId) {
            // TODO: Add permission check for issue access
            throw new \RuntimeException('You do not have permission to delete this attachment');
        }

        // Delete physical file
        $fullPath = WRITEPATH . $attachment['file_path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }

        // Delete from database
        $result = $this->attachmentModel->delete($attachmentId);

        if ($result) {
            $this->logService->log(
                'delete',
                'attachment',
                $attachmentId,
                "File deleted: {$attachment['original_name']}"
            );
        }

        return $result;
    }

    /**
     * Get file content for download
     */
    public function getFileContent(int $attachmentId): ?array
    {
        $attachment = $this->getAttachmentById($attachmentId);
        
        if (!$attachment) {
            return null;
        }

        // Build full path
        $fullPath = WRITEPATH . $attachment['file_path'];
        
        if (!file_exists($fullPath)) {
            return null;
        }

        return [
            'path' => $fullPath,
            'name' => $attachment['original_name'],
            'mime_type' => $attachment['mime_type'],
            'size' => $attachment['file_size']
        ];
    }

    /**
     * Determine file type based on mime type and extension
     */
    protected function determineFileType(string $mimeType, string $extension): string
    {
        // Image types
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        }

        // PDF
        if ($mimeType === 'application/pdf' || $extension === 'pdf') {
            return 'pdf';
        }

        // Document types
        $documentTypes = ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 
                         'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                         'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                         'text/plain', 'text/csv'];
        
        if (in_array($mimeType, $documentTypes) || in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'])) {
            return 'document';
        }

        return 'other';
    }

    /**
     * Get attachment count for issue
     */
    public function getAttachmentCount(int $issueId): int
    {
        return $this->attachmentModel
            ->where('issue_id', $issueId)
            ->countAllResults();
    }
}

