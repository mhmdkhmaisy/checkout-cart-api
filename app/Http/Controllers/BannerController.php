<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BannerController extends Controller
{
    private $pixelFont = [
        'A' => [[0,1,1,1,0],[1,0,0,0,1],[1,1,1,1,1],[1,0,0,0,1],[1,0,0,0,1]],
        'B' => [[1,1,1,1,0],[1,0,0,0,1],[1,1,1,1,0],[1,0,0,0,1],[1,1,1,1,0]],
        'C' => [[0,1,1,1,1],[1,0,0,0,0],[1,0,0,0,0],[1,0,0,0,0],[0,1,1,1,1]],
        'D' => [[1,1,1,1,0],[1,0,0,0,1],[1,0,0,0,1],[1,0,0,0,1],[1,1,1,1,0]],
        'E' => [[1,1,1,1,1],[1,0,0,0,0],[1,1,1,1,0],[1,0,0,0,0],[1,1,1,1,1]],
        'F' => [[1,1,1,1,1],[1,0,0,0,0],[1,1,1,1,0],[1,0,0,0,0],[1,0,0,0,0]],
        'G' => [[0,1,1,1,1],[1,0,0,0,0],[1,0,1,1,1],[1,0,0,0,1],[0,1,1,1,0]],
        'H' => [[1,0,0,0,1],[1,0,0,0,1],[1,1,1,1,1],[1,0,0,0,1],[1,0,0,0,1]],
        'I' => [[1,1,1,1,1],[0,0,1,0,0],[0,0,1,0,0],[0,0,1,0,0],[1,1,1,1,1]],
        'J' => [[0,0,0,1,1],[0,0,0,0,1],[0,0,0,0,1],[1,0,0,0,1],[0,1,1,1,0]],
        'K' => [[1,0,0,0,1],[1,0,0,1,0],[1,1,1,0,0],[1,0,0,1,0],[1,0,0,0,1]],
        'L' => [[1,0,0,0,0],[1,0,0,0,0],[1,0,0,0,0],[1,0,0,0,0],[1,1,1,1,1]],
        'M' => [[1,0,0,0,1],[1,1,0,1,1],[1,0,1,0,1],[1,0,0,0,1],[1,0,0,0,1]],
        'N' => [[1,0,0,0,1],[1,1,0,0,1],[1,0,1,0,1],[1,0,0,1,1],[1,0,0,0,1]],
        'O' => [[0,1,1,1,0],[1,0,0,0,1],[1,0,0,0,1],[1,0,0,0,1],[0,1,1,1,0]],
        'P' => [[1,1,1,1,0],[1,0,0,0,1],[1,1,1,1,0],[1,0,0,0,0],[1,0,0,0,0]],
        'Q' => [[0,1,1,1,0],[1,0,0,0,1],[1,0,0,0,1],[1,0,0,1,0],[0,1,1,0,1]],
        'R' => [[1,1,1,1,0],[1,0,0,0,1],[1,1,1,1,0],[1,0,0,1,0],[1,0,0,0,1]],
        'S' => [[0,1,1,1,1],[1,0,0,0,0],[0,1,1,1,0],[0,0,0,0,1],[1,1,1,1,0]],
        'T' => [[1,1,1,1,1],[0,0,1,0,0],[0,0,1,0,0],[0,0,1,0,0],[0,0,1,0,0]],
        'U' => [[1,0,0,0,1],[1,0,0,0,1],[1,0,0,0,1],[1,0,0,0,1],[0,1,1,1,0]],
        'V' => [[1,0,0,0,1],[1,0,0,0,1],[1,0,0,0,1],[0,1,0,1,0],[0,0,1,0,0]],
        'W' => [[1,0,0,0,1],[1,0,0,0,1],[1,0,1,0,1],[1,1,0,1,1],[1,0,0,0,1]],
        'X' => [[1,0,0,0,1],[0,1,0,1,0],[0,0,1,0,0],[0,1,0,1,0],[1,0,0,0,1]],
        'Y' => [[1,0,0,0,1],[0,1,0,1,0],[0,0,1,0,0],[0,0,1,0,0],[0,0,1,0,0]],
        'Z' => [[1,1,1,1,1],[0,0,0,1,0],[0,0,1,0,0],[0,1,0,0,0],[1,1,1,1,1]],
        '0' => [[0,1,1,1,0],[1,0,0,1,1],[1,0,1,0,1],[1,1,0,0,1],[0,1,1,1,0]],
        '1' => [[0,0,1,0,0],[0,1,1,0,0],[0,0,1,0,0],[0,0,1,0,0],[0,1,1,1,0]],
        '2' => [[0,1,1,1,0],[1,0,0,0,1],[0,0,1,1,0],[0,1,0,0,0],[1,1,1,1,1]],
        '3' => [[1,1,1,1,0],[0,0,0,0,1],[0,1,1,1,0],[0,0,0,0,1],[1,1,1,1,0]],
        '4' => [[1,0,0,1,0],[1,0,0,1,0],[1,1,1,1,1],[0,0,0,1,0],[0,0,0,1,0]],
        '5' => [[1,1,1,1,1],[1,0,0,0,0],[1,1,1,1,0],[0,0,0,0,1],[1,1,1,1,0]],
        '6' => [[0,1,1,1,0],[1,0,0,0,0],[1,1,1,1,0],[1,0,0,0,1],[0,1,1,1,0]],
        '7' => [[1,1,1,1,1],[0,0,0,0,1],[0,0,0,1,0],[0,0,1,0,0],[0,1,0,0,0]],
        '8' => [[0,1,1,1,0],[1,0,0,0,1],[0,1,1,1,0],[1,0,0,0,1],[0,1,1,1,0]],
        '9' => [[0,1,1,1,0],[1,0,0,0,1],[0,1,1,1,1],[0,0,0,0,1],[0,1,1,1,0]],
        '!' => [[0,0,1,0,0],[0,0,1,0,0],[0,0,1,0,0],[0,0,0,0,0],[0,0,1,0,0]],
        '?' => [[0,1,1,1,0],[1,0,0,0,1],[0,0,0,1,0],[0,0,0,0,0],[0,0,1,0,0]],
        '%' => [[1,1,0,0,1],[1,1,0,1,0],[0,0,1,0,0],[0,1,0,1,1],[1,0,0,1,1]],
        '+' => [[0,0,0,0,0],[0,0,1,0,0],[0,1,1,1,0],[0,0,1,0,0],[0,0,0,0,0]],
        '-' => [[0,0,0,0,0],[0,0,0,0,0],[0,1,1,1,0],[0,0,0,0,0],[0,0,0,0,0]],
        ' ' => [[0,0,0,0,0],[0,0,0,0,0],[0,0,0,0,0],[0,0,0,0,0],[0,0,0,0,0]],
    ];

    public function generate(Request $request)
    {
        $text = strtoupper($request->input('text', 'PROMOTION'));
        $width = (int) $request->input('width', 200);
        $height = (int) $request->input('height', 80);

        $svg = $this->generateSvgBanner($text, $width, $height);

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    private function generateSvgBanner($text, $width, $height)
    {
        $pixelSize = 2;
        $charSpacing = 2;
        $charWidth = 5 * $pixelSize;
        $charHeight = 5 * $pixelSize;
        
        $totalTextWidth = 0;
        $chars = str_split($text);
        foreach ($chars as $char) {
            if (isset($this->pixelFont[$char])) {
                $totalTextWidth += $charWidth + ($charSpacing * $pixelSize);
            }
        }
        $totalTextWidth -= ($charSpacing * $pixelSize);
        
        $startX = ($width - $totalTextWidth) / 2;
        $startY = ($height - $charHeight) / 2;
        
        $pixels = [];
        $currentX = $startX;
        
        foreach ($chars as $char) {
            if (!isset($this->pixelFont[$char])) {
                $currentX += $charWidth + ($charSpacing * $pixelSize);
                continue;
            }
            
            $charData = $this->pixelFont[$char];
            
            for ($row = 0; $row < 5; $row++) {
                for ($col = 0; $col < 5; $col++) {
                    if ($charData[$row][$col] == 1) {
                        $pixelX = $currentX + ($col * $pixelSize);
                        $pixelY = $startY + ($row * $pixelSize);
                        $pixels[] = ['x' => $pixelX, 'y' => $pixelY, 'size' => $pixelSize];
                    }
                }
            }
            
            $currentX += $charWidth + ($charSpacing * $pixelSize);
        }
        
        $svg = '<?xml version="1.0" encoding="UTF-8"?>';
        $svg .= '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $height . '" viewBox="0 0 ' . $width . ' ' . $height . '">';
        
        $svg .= '<defs>';
        $svg .= '<linearGradient id="edgeGradient" x1="0%" y1="0%" x2="100%" y2="0%">';
        $svg .= '<stop offset="0%" style="stop-color:rgb(204,102,153);stop-opacity:0.6" />';
        $svg .= '<stop offset="50%" style="stop-color:rgb(255,100,150);stop-opacity:0.8" />';
        $svg .= '<stop offset="100%" style="stop-color:rgb(204,102,153);stop-opacity:0.6" />';
        $svg .= '</linearGradient>';
        
        $svg .= '<filter id="glow">';
        $svg .= '<feGaussianBlur stdDeviation="2" result="coloredBlur"/>';
        $svg .= '<feMerge><feMergeNode in="coloredBlur"/><feMergeNode in="SourceGraphic"/></feMerge>';
        $svg .= '</filter>';
        $svg .= '</defs>';
        
        $svg .= '<rect width="' . $width . '" height="' . $height . '" fill="#0a0a0a"/>';
        
        for ($i = 0; $i < 8; $i++) {
            $opacity = (8 - $i) / 8 * 0.8;
            $color = 'rgba(' . (255 - $i * 10) . ',' . (100 - $i * 5) . ',' . (150 - $i * 8) . ',' . $opacity . ')';
            
            $svg .= '<rect x="' . $i . '" y="' . $i . '" width="' . ($width - 2 * $i) . '" height="' . ($height - 2 * $i) . '" fill="none" stroke="' . $color . '" stroke-width="1"/>';
        }
        
        $svg .= '<animate attributeName="opacity" values="0.6;1;0.6" dur="2s" repeatCount="indefinite"/>';
        
        foreach ($pixels as $pixel) {
            $svg .= '<rect x="' . ($pixel['x'] + 1) . '" y="' . ($pixel['y'] + 1) . '" width="' . $pixel['size'] . '" height="' . $pixel['size'] . '" fill="#645000" opacity="0.5"/>';
        }
        
        foreach ($pixels as $pixel) {
            $svg .= '<rect x="' . $pixel['x'] . '" y="' . $pixel['y'] . '" width="' . $pixel['size'] . '" height="' . $pixel['size'] . '" fill="#FFD700" filter="url(#glow)"/>';
        }
        
        $svg .= '</svg>';
        
        return $svg;
    }
}
