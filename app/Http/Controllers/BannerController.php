<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function generate(Request $request)
    {
        $text = $request->input('text', 'PROMOTION');
        $width = (int) $request->input('width', 200);
        $height = (int) $request->input('height', 50);

        $image = imagecreatetruecolor($width, $height);

        $bgColor = imagecolorallocate($image, 10, 10, 10);
        imagefill($image, 0, 0, $bgColor);

        $edgeGradientWidth = 15;
        for ($i = 0; $i < $edgeGradientWidth; $i++) {
            $alpha = (int)((1 - ($i / $edgeGradientWidth)) * 80);
            $pinkColor = imagecolorallocatealpha($image, 255, 182, 193, 127 - $alpha);

            for ($y = 0; $y < $height; $y++) {
                imagesetpixel($image, $i, $y, $pinkColor);
                imagesetpixel($image, $width - 1 - $i, $y, $pinkColor);
            }
        }

        $goldColor = imagecolorallocate($image, 255, 215, 0);

        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = (int)(($width - $textWidth) / 2);
        $y = (int)(($height - $textHeight) / 2);

        imagestring($image, $fontSize, $x, $y, strtoupper($text), $goldColor);

        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();

        imagedestroy($image);

        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
