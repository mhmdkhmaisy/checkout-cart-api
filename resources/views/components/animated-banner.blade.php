@props(['text', 'width' => 200, 'height' => 80])

<div class="animated-banner-container" style="width: {{ $width }}px; height: {{ $height }}px; position: relative; display: inline-block;">
    <img src="{{ route('banner.generate', ['text' => $text, 'width' => $width, 'height' => $height]) }}" 
         alt="{{ $text }}" 
         style="display: block; width: 100%; height: 100%;">
    
    <div class="banner-edge-overlay"></div>
</div>

<style>
.animated-banner-container {
    position: relative;
    border-radius: 4px;
    overflow: hidden;
}

.banner-edge-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    box-shadow: 
        inset 0 0 8px rgba(255, 100, 150, 0.3),
        inset 0 0 12px rgba(255, 100, 150, 0.2),
        inset 0 0 16px rgba(200, 50, 100, 0.1);
    animation: bannerPulse 2s ease-in-out infinite;
}

@keyframes bannerPulse {
    0%, 100% {
        box-shadow: 
            inset 0 0 8px rgba(255, 100, 150, 0.4),
            inset 0 0 12px rgba(255, 100, 150, 0.3),
            inset 0 0 16px rgba(200, 50, 100, 0.2);
    }
    50% {
        box-shadow: 
            inset 0 0 12px rgba(255, 120, 170, 0.6),
            inset 0 0 16px rgba(255, 120, 170, 0.4),
            inset 0 0 20px rgba(220, 70, 120, 0.3);
    }
}
</style>
