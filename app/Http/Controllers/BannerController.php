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

        $image = imagecreatetruecolor($width, $height);
        imagesavealpha($image, true);

        $bgColor = imagecolorallocate($image, 10, 10, 10);
        imagefill($image, 0, 0, $bgColor);

        $this->drawAnimatedEdges($image, $width, $height);
        $this->drawPixelText($image, $text, $width, $height);

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();

        imagedestroy($image);

        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    private function drawAnimatedEdges($image, $width, $height)
    {
        $edgeWidth = 8;
        
        for ($i = 0; $i < $edgeWidth; $i++) {
            $intensity = (($edgeWidth - $i) / $edgeWidth);
            
            $r = (int)(255 * $intensity * 0.8);
            $g = (int)(100 * $intensity * 0.6);
            $b = (int)(150 * $intensity * 0.7);
            
            $edgeColor = imagecolorallocate($image, $r, $g, $b);
            
            for ($y = 0; $y < $height; $y++) {
                imagesetpixel($image, $i, $y, $edgeColor);
                imagesetpixel($image, $width - 1 - $i, $y, $edgeColor);
            }
            
            for ($x = 0; $x < $width; $x++) {
                imagesetpixel($image, $x, $i, $edgeColor);
                imagesetpixel($image, $x, $height - 1 - $i, $edgeColor);
            }
        }
        
        for ($i = 0; $i < 3; $i++) {
            $glowColor = imagecolorallocate($image, 255, 150, 200);
            
            imagesetpixel($image, $edgeWidth + $i, $edgeWidth + $i, $glowColor);
            imagesetpixel($image, $width - $edgeWidth - $i - 1, $edgeWidth + $i, $glowColor);
            imagesetpixel($image, $edgeWidth + $i, $height - $edgeWidth - $i - 1, $glowColor);
            imagesetpixel($image, $width - $edgeWidth - $i - 1, $height - $edgeWidth - $i - 1, $glowColor);
        }
    }

    private function drawPixelText($image, $text, $width, $height)
    {
        $yellowColor = imagecolorallocate($image, 255, 215, 0);
        $shadowColor = imagecolorallocate($image, 100, 80, 0);
        
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
        
        $startX = (int)(($width - $totalTextWidth) / 2);
        $startY = (int)(($height - $charHeight) / 2);
        
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
                        
                        imagefilledrectangle(
                            $image,
                            $pixelX + 1,
                            $pixelY + 1,
                            $pixelX + $pixelSize,
                            $pixelY + $pixelSize,
                            $shadowColor
                        );
                        
                        imagefilledrectangle(
                            $image,
                            $pixelX,
                            $pixelY,
                            $pixelX + $pixelSize - 1,
                            $pixelY + $pixelSize - 1,
                            $yellowColor
                        );
                    }
                }
            }
            
            $currentX += $charWidth + ($charSpacing * $pixelSize);
        }
    }
}
