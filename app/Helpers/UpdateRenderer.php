<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class UpdateRenderer
{
    public static function render($contentJson)
    {
        if (empty($contentJson)) {
            return '';
        }

        $data = json_decode($contentJson, true);
        
        if (!$data || !isset($data['blocks'])) {
            return '<p>' . nl2br(e($contentJson)) . '</p>';
        }

        $html = '';
        
        foreach ($data['blocks'] as $block) {
            $html .= self::renderBlock($block);
        }
        
        return $html;
    }

    private static function renderBlock($block)
    {
        $type = $block['type'] ?? 'paragraph';
        $data = $block['data'] ?? [];

        return match($type) {
            'header' => self::renderHeader($data),
            'paragraph' => self::renderParagraph($data),
            'list' => self::renderList($data),
            'code' => self::renderCode($data),
            'image' => self::renderImage($data),
            'alert' => self::renderAlert($data),
            'callout' => self::renderCallout($data),
            'table' => self::renderTable($data),
            'separator' => self::renderSeparator($data),
            'osrs_header' => self::renderOsrsHeader($data),
            'patch_notes_section' => self::renderPatchNotesSection($data),
            'custom_section' => self::renderCustomSection($data),
            default => self::renderParagraph($data),
        };
    }

    private static function renderHeader($data)
    {
        $level = $data['level'] ?? 2;
        $text = $data['text'] ?? '';
        
        $styles = [
            2 => 'font-size: 1.75rem; font-weight: 700; margin-top: 1.5rem; margin-bottom: 1rem; color: var(--primary-color, #d40000);',
            3 => 'font-size: 1.5rem; font-weight: 600; margin-top: 1.25rem; margin-bottom: 0.875rem; color: var(--primary-color, #d40000);',
            4 => 'font-size: 1.25rem; font-weight: 600; margin-top: 1rem; margin-bottom: 0.75rem; color: var(--primary-color, #d40000);',
        ];
        
        $style = $styles[$level] ?? $styles[2];
        
        return "<h{$level} style='{$style}'>" . e($text) . "</h{$level}>";
    }

    private static function renderParagraph($data)
    {
        $text = $data['text'] ?? '';
        return '<p style="margin-bottom: 1rem; line-height: 1.7; color: var(--text-color, #ccc);">' . nl2br(e($text)) . '</p>';
    }

    private static function renderList($data)
    {
        $style = $data['style'] ?? 'unordered';
        $items = $data['items'] ?? [];
        
        $tag = $style === 'ordered' ? 'ol' : 'ul';
        $listStyle = $style === 'ordered' 
            ? 'list-style-type: decimal; margin-left: 2rem; margin-bottom: 1rem; line-height: 1.7;' 
            : 'list-style-type: disc; margin-left: 2rem; margin-bottom: 1rem; line-height: 1.7;';
        
        $html = "<{$tag} style='{$listStyle}'>";
        foreach ($items as $item) {
            $html .= '<li style="margin-bottom: 0.5rem; color: var(--text-color, #ccc);">' . e($item) . '</li>';
        }
        $html .= "</{$tag}>";
        
        return $html;
    }

    private static function renderCode($data)
    {
        $code = $data['code'] ?? '';
        return '<pre style="background: #1a1a1a; padding: 1.25rem; border-radius: 8px; overflow-x: auto; margin-bottom: 1rem; border: 1px solid #333;"><code style="color: #e0e0e0; font-family: \'Courier New\', monospace; font-size: 0.9rem;">' . e($code) . '</code></pre>';
    }

    private static function renderImage($data)
    {
        $url = $data['url'] ?? '';
        $caption = $data['caption'] ?? '';
        
        $html = '<figure style="margin-bottom: 1.5rem;">';
        $html .= '<img src="' . e($url) . '" alt="' . e($caption) . '" style="border-radius: 8px; max-width: 100%; height: auto;">';
        if ($caption) {
            $html .= '<figcaption style="font-size: 0.9rem; color: #999; margin-top: 0.5rem; text-align: center;">' . e($caption) . '</figcaption>';
        }
        $html .= '</figure>';
        
        return $html;
    }

    private static function renderAlert($data)
    {
        $type = $data['type'] ?? 'info';
        $message = $data['message'] ?? '';
        
        $styles = [
            'success' => 'background: rgba(34, 197, 94, 0.15); border-left: 4px solid #22c55e; color: #86efac; padding: 1rem 1.25rem; margin-bottom: 1rem; border-radius: 6px;',
            'info' => 'background: rgba(59, 130, 246, 0.15); border-left: 4px solid #3b82f6; color: #93c5fd; padding: 1rem 1.25rem; margin-bottom: 1rem; border-radius: 6px;',
            'warning' => 'background: rgba(234, 179, 8, 0.15); border-left: 4px solid #eab308; color: #fde047; padding: 1rem 1.25rem; margin-bottom: 1rem; border-radius: 6px;',
            'danger' => 'background: rgba(239, 68, 68, 0.15); border-left: 4px solid #ef4444; color: #fca5a5; padding: 1rem 1.25rem; margin-bottom: 1rem; border-radius: 6px;',
        ];
        
        $style = $styles[$type] ?? $styles['info'];
        
        return '<div style="' . $style . '">' . nl2br(e($message)) . '</div>';
    }

    private static function renderCallout($data)
    {
        $type = $data['type'] ?? 'info';
        $title = $data['title'] ?? '';
        $message = $data['message'] ?? '';
        
        $styles = [
            'info' => [
                'bg' => 'background: rgba(59, 130, 246, 0.1); border: 2px solid #3b82f6;',
                'title' => 'color: #60a5fa; font-weight: 700; font-size: 1.125rem; margin-bottom: 0.5rem;',
                'icon' => '<i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>'
            ],
            'tip' => [
                'bg' => 'background: rgba(34, 197, 94, 0.1); border: 2px solid #22c55e;',
                'title' => 'color: #4ade80; font-weight: 700; font-size: 1.125rem; margin-bottom: 0.5rem;',
                'icon' => '<i class="fas fa-lightbulb" style="margin-right: 0.5rem;"></i>'
            ],
            'warning' => [
                'bg' => 'background: rgba(234, 179, 8, 0.1); border: 2px solid #eab308;',
                'title' => 'color: #facc15; font-weight: 700; font-size: 1.125rem; margin-bottom: 0.5rem;',
                'icon' => '<i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>'
            ],
            'important' => [
                'bg' => 'background: rgba(239, 68, 68, 0.1); border: 2px solid #ef4444;',
                'title' => 'color: #f87171; font-weight: 700; font-size: 1.125rem; margin-bottom: 0.5rem;',
                'icon' => '<i class="fas fa-exclamation-circle" style="margin-right: 0.5rem;"></i>'
            ],
            'new' => [
                'bg' => 'background: rgba(168, 85, 247, 0.1); border: 2px solid #a855f7;',
                'title' => 'color: #c084fc; font-weight: 700; font-size: 1.125rem; margin-bottom: 0.5rem;',
                'icon' => '<i class="fas fa-star" style="margin-right: 0.5rem;"></i>'
            ]
        ];
        
        $styleSet = $styles[$type] ?? $styles['info'];
        
        $html = '<div style="' . $styleSet['bg'] . ' padding: 1.5rem; margin-bottom: 1.5rem; border-radius: 8px;">';
        if ($title) {
            $html .= '<div style="' . $styleSet['title'] . '">' . $styleSet['icon'] . e($title) . '</div>';
        }
        $html .= '<div style="color: var(--text-color, #ccc); line-height: 1.6;">' . nl2br(e($message)) . '</div>';
        $html .= '</div>';
        
        return $html;
    }

    private static function renderTable($data)
    {
        $tableData = $data['data'] ?? [];
        
        if (empty($tableData)) {
            return '';
        }
        
        $html = '<div style="overflow-x: auto; margin-bottom: 1.5rem;">';
        $html .= '<table style="width: 100%; border-collapse: collapse; background: rgba(0, 0, 0, 0.3); border-radius: 8px; overflow: hidden;">';
        
        foreach ($tableData as $rowIdx => $row) {
            if ($rowIdx === 0) {
                $html .= '<thead><tr>';
                foreach ($row as $cell) {
                    $html .= '<th style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: var(--primary-color, #d40000); border-bottom: 2px solid var(--primary-color, #d40000); background: rgba(212, 0, 0, 0.1);">' . e($cell) . '</th>';
                }
                $html .= '</tr></thead><tbody>';
            } else {
                $html .= '<tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.1);">';
                foreach ($row as $cell) {
                    $html .= '<td style="padding: 0.75rem 1rem; color: var(--text-color, #ccc);">' . e($cell) . '</td>';
                }
                $html .= '</tr>';
            }
        }
        
        $html .= '</tbody></table></div>';
        
        return $html;
    }

    private static function renderSeparator($data)
    {
        return '<hr style="border: none; border-top: 2px solid rgba(212, 0, 0, 0.3); margin: 2rem 0;">';
    }

    private static function renderOsrsHeader($data)
    {
        $header = $data['header'] ?? '';
        $subheader = $data['subheader'] ?? '';
        $colorScheme = $data['color'] ?? 'gold';
        
        $colors = [
            'gold' => '#FFB000',
            'red' => '#FF0000',
            'cyan' => '#00FFFF',
            'green' => '#00FF00',
            'white' => '#FFFFFF',
        ];
        
        $color = $colors[$colorScheme] ?? $colors['gold'];
        
        $headerStyle = '
            font-family: \'Press Start 2P\', monospace, system-ui;
            font-size: 1.5rem;
            line-height: 1.8;
            color: ' . $color . ';
            text-shadow: 2px 2px 0px #000000, -1px -1px 0px rgba(0,0,0,0.5);
            letter-spacing: 2px;
            margin-bottom: ' . ($subheader ? '0.5rem' : '1.5rem') . ';
            text-transform: uppercase;
            image-rendering: pixelated;
            -webkit-font-smoothing: none;
            -moz-osx-font-smoothing: grayscale;
        ';
        
        $subheaderStyle = '
            font-family: \'Press Start 2P\', monospace, system-ui;
            font-size: 0.875rem;
            line-height: 1.6;
            color: ' . $color . ';
            text-shadow: 1px 1px 0px #000000;
            letter-spacing: 1px;
            margin-bottom: 1.5rem;
            opacity: 0.8;
            text-align: center;
            image-rendering: pixelated;
            -webkit-font-smoothing: none;
            -moz-osx-font-smoothing: grayscale;
        ';
        
        $html = '<div style="margin: 2rem 0;">';
        $html .= '<div style="' . $headerStyle . '">' . e($header) . '</div>';
        
        if ($subheader) {
            $html .= '<div style="' . $subheaderStyle . '">' . e($subheader) . '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }

    private static function renderPatchNotesSection($data)
    {
        $children = $data['children'] ?? [];
        
        $html = '<div style="background: rgba(196, 30, 58, 0.08); border-left: 4px solid var(--primary-color, #c41e3a); border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
        $html .= '<div style="display: flex; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 2px solid rgba(196, 30, 58, 0.3);">';
        $html .= '<i class="fas fa-wrench" style="color: var(--primary-color, #c41e3a); font-size: 1.25rem; margin-right: 0.75rem;"></i>';
        $html .= '<h3 style="font-size: 1.5rem; font-weight: 700; color: var(--primary-color, #c41e3a); margin: 0;">Patch Notes</h3>';
        $html .= '</div>';
        
        if (!empty($children)) {
            foreach ($children as $child) {
                $html .= self::renderBlock($child);
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }

    private static function renderCustomSection($data)
    {
        $title = $data['title'] ?? 'Section';
        $children = $data['children'] ?? [];
        $colorScheme = $data['color'] ?? 'primary';
        
        $colors = [
            'primary' => ['bg' => 'rgba(196, 30, 58, 0.08)', 'border' => '#c41e3a', 'text' => '#c41e3a'],
            'gold' => ['bg' => 'rgba(212, 165, 116, 0.08)', 'border' => '#d4a574', 'text' => '#d4a574'],
            'blue' => ['bg' => 'rgba(59, 130, 246, 0.08)', 'border' => '#3b82f6', 'text' => '#3b82f6'],
            'green' => ['bg' => 'rgba(34, 197, 94, 0.08)', 'border' => '#22c55e', 'text' => '#22c55e'],
            'purple' => ['bg' => 'rgba(168, 85, 247, 0.08)', 'border' => '#a855f7', 'text' => '#a855f7'],
            'orange' => ['bg' => 'rgba(255, 107, 53, 0.08)', 'border' => '#ff6b35', 'text' => '#ff6b35'],
        ];
        
        $colorSet = $colors[$colorScheme] ?? $colors['primary'];
        
        $html = '<div style="background: ' . $colorSet['bg'] . '; border-left: 4px solid ' . $colorSet['border'] . '; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;">';
        $html .= '<div style="display: flex; align-items: center; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 2px solid ' . $colorSet['border'] . '30;">';
        $html .= '<span style="font-family: \'Courier New\', monospace; font-weight: bold; letter-spacing: 1px; image-rendering: pixelated; font-size: 11px; background: #1a1a1a; border: 2px solid #8b7355; padding: 2px 6px; color: #ff9040; text-shadow: 1px 1px 0px #000; margin-right: 0.75rem;">SECTION</span>';
        $html .= '<h3 style="font-size: 1.5rem; font-weight: 700; color: ' . $colorSet['text'] . '; margin: 0;">' . e($title) . '</h3>';
        $html .= '</div>';
        
        if (!empty($children)) {
            foreach ($children as $child) {
                $html .= self::renderBlock($child);
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }

    public static function extractPlainText($contentJson, $maxLength = 200)
    {
        if (empty($contentJson)) {
            return '';
        }

        $data = json_decode($contentJson, true);
        
        if (!$data || !isset($data['blocks'])) {
            return Str::limit(strip_tags($contentJson), $maxLength);
        }

        $text = [];
        $currentLength = 0;
        
        foreach ($data['blocks'] as $block) {
            // Stop if we already have enough text
            if ($currentLength >= $maxLength) {
                break;
            }
            
            $type = $block['type'] ?? 'paragraph';
            $blockData = $block['data'] ?? [];
            
            // Skip the first header (usually the title which is already shown)
            if ($type === 'header' && empty($text)) {
                continue;
            }
            
            switch($type) {
                case 'paragraph':
                    if (!empty($blockData['text'])) {
                        $text[] = $blockData['text'];
                        $currentLength += strlen($blockData['text']);
                    }
                    break;
                case 'list':
                    if (!empty($blockData['items'])) {
                        // Only show first few list items
                        $items = array_slice($blockData['items'], 0, 3);
                        $listText = implode(', ', $items);
                        if (count($blockData['items']) > 3) {
                            $listText .= '...';
                        }
                        $text[] = $listText;
                        $currentLength += strlen($listText);
                    }
                    break;
                case 'alert':
                    if (!empty($blockData['message'])) {
                        $text[] = $blockData['message'];
                        $currentLength += strlen($blockData['message']);
                    }
                    break;
                case 'header':
                    // Include headers after the first one as section markers
                    if (!empty($blockData['text']) && !empty($text)) {
                        $text[] = $blockData['text'];
                        $currentLength += strlen($blockData['text']);
                    }
                    break;
                // Skip code blocks and images from preview
            }
        }
        
        // If no text was extracted (maybe only header/code/images), show a generic message
        if (empty($text)) {
            return 'Click to read more...';
        }
        
        $plainText = implode(' ', $text);
        return Str::limit($plainText, $maxLength);
    }

    public static function renderPreview($contentJson, $maxWeight = 300)
    {
        if (empty($contentJson)) {
            return '';
        }

        $data = json_decode($contentJson, true);
        
        if (!$data || !isset($data['blocks'])) {
            return '<p style="margin-bottom: 1rem; line-height: 1.7; color: var(--text-color, #ccc);">' . nl2br(e($contentJson)) . '</p>';
        }

        $html = '';
        $currentWeight = 0;
        
        // Block weights for calculating "visual length"
        $blockWeights = [
            'header' => 50,
            'paragraph' => function($data) { return strlen($data['text'] ?? ''); },
            'list' => function($data) { return count($data['items'] ?? []) * 20; },
            'code' => 100,
            'image' => 50,
            'alert' => function($data) { return strlen($data['message'] ?? ''); },
        ];
        
        foreach ($data['blocks'] as $block) {
            $type = $block['type'] ?? 'paragraph';
            $blockData = $block['data'] ?? [];
            
            // Calculate weight for this block
            $weight = 0;
            if (isset($blockWeights[$type])) {
                if (is_callable($blockWeights[$type])) {
                    $weight = $blockWeights[$type]($blockData);
                } else {
                    $weight = $blockWeights[$type];
                }
            }
            
            // Stop if adding this block would exceed limit
            if ($currentWeight + $weight > $maxWeight && $currentWeight > 0) {
                $html .= '<p style="color: var(--text-muted, #999); font-style: italic; margin-top: 0.5rem;">...</p>';
                break;
            }
            
            $html .= self::renderBlock($block);
            $currentWeight += $weight;
        }
        
        return $html;
    }
}
