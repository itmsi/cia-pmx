<?php 
$pager->setSurroundCount(1); // Show only 1 page on each side of current
$currentPage = $pager->getCurrentPageNumber();
$lastPage = $pager->getPageCount();
$links = $pager->links();
?>

<nav aria-label="Page navigation" style="margin: 20px 0;">
    <ul class="pagination" style="display: flex; justify-content: center; gap: 4px; margin: 0; padding: 0; list-style: none;">
        <!-- First Page -->
        <?php if ($currentPage > 1) : ?>
            <li class="page-item">
                <a href="<?= $pager->getFirst() ?>" class="page-link" title="First" aria-label="First" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #e2e8f0; border-radius: 6px; color: #4a5568; text-decoration: none; transition: all 0.2s ease;">
                    <i class="fas fa-angle-double-left" style="font-size: 12px;"></i>
                </a>
            </li>
            <li class="page-item">
                <a href="<?= $pager->getPreviousPage() ?>" class="page-link" title="Previous" aria-label="Previous" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #e2e8f0; border-radius: 6px; color: #4a5568; text-decoration: none; transition: all 0.2s ease;">
                    <i class="fas fa-angle-left" style="font-size: 12px;"></i>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #e2e8f0; border-radius: 6px; color: #a0aec0; background-color: #f7fafc; cursor: not-allowed; opacity: 0.7;">
                    <i class="fas fa-angle-double-left" style="font-size: 12px;"></i>
                </span>
            </li>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #e2e8f0; border-radius: 6px; color: #a0aec0; background-color: #f7fafc; cursor: not-allowed; opacity: 0.7;">
                    <i class="fas fa-angle-left" style="font-size: 12px;"></i>
                </span>
            </li>
        <?php endif; ?>

        <!-- Page Numbers -->
        <?php 
        // Only show a limited number of page numbers
        $maxPagesToShow = 3; // Current page + 1 before + 1 after
        $start = max(1, $currentPage - 1);
        $end = min($lastPage, $currentPage + 1);
        
        // Always show first page
        if ($start > 1) {
            echo '<li class="page-item' . (1 == $currentPage ? ' active' : '') . '">';
            echo '<a href="' . $pager->getFirst() . '" class="page-link">1</a>';
            echo '</li>';
            
            if ($start > 2) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }
        
        // Show pages around current
        $links = $pager->links();
        $pageLinks = [];
        
        // Extract all page links first
        foreach ($links as $link) {
            if (is_numeric($link['title'])) {
                $pageLinks[intval($link['title'])] = $link['uri'];
            }
        }
        
        // Output the page numbers
        for ($i = $start; $i <= $end; $i++) {
            if ($i == $currentPage) {
                echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
            } else {
                $uri = $pageLinks[$i] ?? '#'; // Fallback to '#' if page not found
                echo '<li class="page-item"><a href="' . $uri . '" class="page-link">' . $i . '</a></li>';
            }
        }
        
        // Always show last page
        if ($end < $lastPage) {
            if ($end < $lastPage - 1) {
                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
            
            echo '<li class="page-item' . ($lastPage == $currentPage ? ' active' : '') . '">';
            echo '<a href="' . $pager->getLast() . '" class="page-link">' . $lastPage . '</a>';
            echo '</li>';
        }
        ?>

        <!-- Next/Last -->
        <?php if ($currentPage < $lastPage) : ?>
            <li class="page-item">
                <a href="<?= $pager->getNextPage() ?>" class="page-link" title="Next" aria-label="Next" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #e2e8f0; border-radius: 6px; color: #4a5568; text-decoration: none; transition: all 0.2s ease;">
                    <i class="fas fa-angle-right" style="font-size: 12px;"></i>
                </a>
            </li>
            <li class="page-item">
                <a href="<?= $pager->getLast() ?>" class="page-link" title="Last" aria-label="Last" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #e2e8f0; border-radius: 6px; color: #4a5568; text-decoration: none; transition: all 0.2s ease;">
                    <i class="fas fa-angle-double-right" style="font-size: 12px;"></i>
                </a>
            </li>
        <?php else: ?>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #e2e8f0; border-radius: 6px; color: #a0aec0; background-color: #f7fafc; cursor: not-allowed; opacity: 0.7;">
                    <i class="fas fa-angle-right" style="font-size: 12px;"></i>
                </span>
            </li>
            <li class="page-item disabled">
                <span class="page-link" aria-hidden="true" style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border: 1px solid #e2e8f0; border-radius: 6px; color: #a0aec0; background-color: #f7fafc; cursor: not-allowed; opacity: 0.7;">
                    <i class="fas fa-angle-double-right" style="font-size: 12px;"></i>
                </span>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<style>
.pagination {
    display: flex;
    gap: 6px;
    margin: 0;
    padding: 0;
    list-style: none;
}

.page-item {
    margin: 0;
}

.page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 12px;
    color: #4a5568;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    transition: all 0.2s ease;
}

.page-link:hover {
    background-color: #f7fafc;
    border-color: #cbd5e0;
    color: #2d3748;
}

.page-item.active .page-link {
    background-color: #4a90e2;
    border-color: #4a90e2;
    color: white;
    font-weight: 600;
}

.page-item.disabled .page-link {
    color: #a0aec0;
    background-color: #f7fafc;
    border-color: #e2e8f0;
    cursor: not-allowed;
    opacity: 0.7;
}

.page-link i {
    font-size: 12px;
    line-height: 1;
}

/* Responsive adjustments */
@media (max-width: 640px) {
    .pagination {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .page-link {
        min-width: 34px;
        height: 34px;
        padding: 0 8px;
        font-size: 13px;
    }
}
</style>
