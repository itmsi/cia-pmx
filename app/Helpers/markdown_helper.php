<?php

if (!function_exists('markdown_to_html')) {
    /**
     * Convert markdown to HTML (simple implementation)
     * For production, consider using a library like Parsedown
     */
    function markdown_to_html(string $markdown): string
    {
        $html = $markdown;
        
        // Headers
        $html = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $html);
        $html = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $html);
        $html = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $html);
        
        // Bold
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/__(.*?)__/', '<strong>$1</strong>', $html);
        
        // Italic
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
        $html = preg_replace('/_(.*?)_/', '<em>$1</em>', $html);
        
        // Links
        $html = preg_replace('/\[([^\]]+)\]\(([^\)]+)\)/', '<a href="$2">$1</a>', $html);
        
        // Code blocks
        $html = preg_replace('/```([^`]+)```/s', '<pre><code>$1</code></pre>', $html);
        $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
        
        // Line breaks (do before lists to preserve line structure)
        $html = nl2br($html);
        
        // Lists (simple implementation)
        $lines = explode('<br />', $html);
        $inList = false;
        $processedLines = [];
        
        foreach ($lines as $line) {
            $trimmedLine = trim(strip_tags($line));
            if (preg_match('/^[\*\-] (.*)$/', $trimmedLine, $matches) || preg_match('/^\d+\. (.*)$/', $trimmedLine, $matches)) {
                if (!$inList) {
                    $processedLines[] = '<ul>';
                    $inList = true;
                }
                $processedLines[] = '<li>' . $matches[1] . '</li>';
            } else {
                if ($inList) {
                    $processedLines[] = '</ul>';
                    $inList = false;
                }
                if (!empty($trimmedLine)) {
                    $processedLines[] = $line;
                }
            }
        }
        
        if ($inList) {
            $processedLines[] = '</ul>';
        }
        
        $html = implode('<br />', $processedLines);
        
        // Paragraphs
        $html = preg_replace('/<br \/><br \/>/', '</p><p>', $html);
        $html = '<p>' . $html . '</p>';
        $html = preg_replace('/<p>\s*<\/p>/', '', $html);
        
        return $html;
    }
}
