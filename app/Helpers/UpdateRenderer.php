<?php

namespace App\Helpers;

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
            default => self::renderParagraph($data),
        };
    }

    private static function renderHeader($data)
    {
        $level = $data['level'] ?? 2;
        $text = $data['text'] ?? '';
        
        return "<h{$level} class='text-xl font-bold mt-4 mb-2'>" . e($text) . "</h{$level}>";
    }

    private static function renderParagraph($data)
    {
        $text = $data['text'] ?? '';
        return '<p class="mb-3">' . e($text) . '</p>';
    }

    private static function renderList($data)
    {
        $style = $data['style'] ?? 'unordered';
        $items = $data['items'] ?? [];
        
        $tag = $style === 'ordered' ? 'ol' : 'ul';
        $class = $style === 'ordered' ? 'list-decimal' : 'list-disc';
        
        $html = "<{$tag} class='{$class} ml-6 mb-3'>";
        foreach ($items as $item) {
            $html .= '<li>' . e($item) . '</li>';
        }
        $html .= "</{$tag}>";
        
        return $html;
    }

    private static function renderCode($data)
    {
        $code = $data['code'] ?? '';
        return '<pre class="bg-gray-900 p-4 rounded-lg overflow-x-auto mb-3"><code>' . e($code) . '</code></pre>';
    }

    private static function renderImage($data)
    {
        $url = $data['url'] ?? '';
        $caption = $data['caption'] ?? '';
        
        $html = '<figure class="mb-4">';
        $html .= '<img src="' . e($url) . '" alt="' . e($caption) . '" class="rounded-lg max-w-full">';
        if ($caption) {
            $html .= '<figcaption class="text-sm text-muted mt-2">' . e($caption) . '</figcaption>';
        }
        $html .= '</figure>';
        
        return $html;
    }

    private static function renderAlert($data)
    {
        $type = $data['type'] ?? 'info';
        $message = $data['message'] ?? '';
        
        $colors = [
            'success' => 'bg-green-900/20 border-green-500 text-green-200',
            'info' => 'bg-blue-900/20 border-blue-500 text-blue-200',
            'warning' => 'bg-yellow-900/20 border-yellow-500 text-yellow-200',
            'danger' => 'bg-red-900/20 border-red-500 text-red-200',
        ];
        
        $colorClass = $colors[$type] ?? $colors['info'];
        
        return '<div class="alert border-l-4 p-4 mb-3 rounded ' . $colorClass . '">' . e($message) . '</div>';
    }
}
