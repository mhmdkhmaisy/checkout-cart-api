<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $update->title }} - Screenshot</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            margin: 0;
            padding: 20px;
            background: #0a0a0a;
            width: 1200px;
        }
        
        #capture-area {
            background: #0a0a0a;
            padding: 40px;
            max-width: 1120px;
        }
    </style>
</head>
<body>
    <div id="capture-area">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-dragon-red mb-4">{{ $update->title }}</h1>
            
            @if($update->author || $update->created_at)
                <div class="flex items-center gap-4 text-dragon-silver-dark text-sm mb-4">
                    @if($update->author)
                        <span><i class="fas fa-user mr-2"></i>{{ $update->author }}</span>
                    @endif
                    <span><i class="far fa-calendar mr-2"></i>{{ $update->created_at->format('F j, Y') }}</span>
                </div>
            @endif
        </div>

        @if($update->featured_image)
            <div class="mb-8">
                <img src="{{ $update->featured_image }}" 
                     alt="{{ $update->title }}" 
                     class="w-full rounded-lg shadow-lg">
            </div>
        @endif

        <div class="update-content">
            {!! App\Helpers\UpdateRenderer::render($update->content) !!}
        </div>

        <div class="mt-8 pt-6 border-t border-dragon-border">
            <a href="{{ route('updates.show', $update->slug) }}" 
               class="text-dragon-red hover:text-dragon-red-bright transition-colors text-lg font-semibold">
                Read full update → {{ url(route('updates.show', $update->slug)) }}
            </a>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                const captureArea = document.getElementById('capture-area');
                
                html2canvas(captureArea, {
                    backgroundColor: '#0a0a0a',
                    scale: 2,
                    logging: false,
                    useCORS: true,
                    allowTaint: true
                }).then(canvas => {
                    canvas.toBlob(blob => {
                        const formData = new FormData();
                        formData.append('screenshot', blob, 'screenshot.png');
                        formData.append('update_id', '{{ $update->id }}');
                        
                        fetch('{{ route("admin.updates.process-screenshot") }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.close();
                            }
                        });
                    }, 'image/png');
                });
            }, 1000);
        });
    </script>
</body>
</html>
