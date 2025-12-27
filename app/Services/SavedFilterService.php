<?php

namespace App\Services;

use App\Models\SavedFilterModel;

class SavedFilterService
{
    protected SavedFilterModel $savedFilterModel;
    protected $db;

    public function __construct()
    {
        $this->savedFilterModel = new SavedFilterModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Save a filter for user
     */
    public function saveFilter(int $userId, string $name, array $filterData, ?int $projectId = null, bool $isDefault = false): int
    {
        // If setting as default, unset other defaults for this user/project
        if ($isDefault) {
            $this->unsetOtherDefaults($userId, $projectId);
        }

        $filterId = $this->savedFilterModel->insert([
            'user_id' => $userId,
            'project_id' => $projectId,
            'name' => $name,
            'filter_data' => json_encode($filterData),
            'is_default' => $isDefault ? 1 : 0,
        ]);

        return $filterId;
    }

    /**
     * Get all saved filters for user
     */
    public function getSavedFilters(int $userId, ?int $projectId = null): array
    {
        $builder = $this->savedFilterModel->where('user_id', $userId);

        if ($projectId !== null) {
            $builder->groupStart()
                ->where('project_id', $projectId)
                ->orWhere('project_id', null)
                ->groupEnd();
        } else {
            $builder->where('project_id', null);
        }

        return $builder->orderBy('is_default', 'DESC')
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Get saved filter by ID
     */
    public function getSavedFilterById(int $filterId, int $userId): ?array
    {
        $filter = $this->savedFilterModel
            ->where('id', $filterId)
            ->where('user_id', $userId)
            ->first();

        if ($filter && isset($filter['filter_data'])) {
            $filter['filter_data'] = is_string($filter['filter_data']) 
                ? json_decode($filter['filter_data'], true) 
                : $filter['filter_data'];
        }

        return $filter;
    }

    /**
     * Get default filter for user
     */
    public function getDefaultFilter(int $userId, ?int $projectId = null): ?array
    {
        $builder = $this->savedFilterModel
            ->where('user_id', $userId)
            ->where('is_default', 1);

        if ($projectId !== null) {
            $builder->groupStart()
                ->where('project_id', $projectId)
                ->orWhere('project_id', null)
                ->groupEnd();
        } else {
            $builder->where('project_id', null);
        }

        $filter = $builder->first();

        if ($filter && isset($filter['filter_data'])) {
            $filter['filter_data'] = is_string($filter['filter_data']) 
                ? json_decode($filter['filter_data'], true) 
                : $filter['filter_data'];
        }

        return $filter;
    }

    /**
     * Update saved filter
     */
    public function updateFilter(int $filterId, int $userId, array $data): bool
    {
        // Check ownership
        $filter = $this->savedFilterModel
            ->where('id', $filterId)
            ->where('user_id', $userId)
            ->first();

        if (!$filter) {
            throw new \RuntimeException('Filter not found or access denied');
        }

        // If setting as default, unset other defaults
        if (isset($data['is_default']) && $data['is_default']) {
            $this->unsetOtherDefaults($userId, $filter['project_id']);
        }

        // Encode filter_data if provided
        if (isset($data['filter_data']) && is_array($data['filter_data'])) {
            $data['filter_data'] = json_encode($data['filter_data']);
        }

        return $this->savedFilterModel->update($filterId, $data);
    }

    /**
     * Delete saved filter
     */
    public function deleteFilter(int $filterId, int $userId): bool
    {
        // Check ownership
        $filter = $this->savedFilterModel
            ->where('id', $filterId)
            ->where('user_id', $userId)
            ->first();

        if (!$filter) {
            throw new \RuntimeException('Filter not found or access denied');
        }

        return $this->savedFilterModel->delete($filterId);
    }

    /**
     * Unset other default filters for user/project
     */
    protected function unsetOtherDefaults(int $userId, ?int $projectId = null): void
    {
        $builder = $this->savedFilterModel
            ->where('user_id', $userId)
            ->where('is_default', 1);

        if ($projectId !== null) {
            $builder->groupStart()
                ->where('project_id', $projectId)
                ->orWhere('project_id', null)
                ->groupEnd();
        } else {
            $builder->where('project_id', null);
        }

        $builder->set('is_default', 0)->update();
    }
}

