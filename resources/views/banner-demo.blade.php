<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banner Generator Demo - Aragon RSPS</title>
    <style>
        body {
            margin: 0;
            padding: 40px;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #e8e8e8;
            min-height: 100vh;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        h1 {
            color: #d40000;
            text-align: center;
            font-size: 3em;
            margin-bottom: 10px;
            text-shadow: 0 0 20px rgba(212, 0, 0, 0.5);
        }
        .subtitle {
            text-align: center;
            color: #c0c0c0;
            margin-bottom: 40px;
            font-size: 1.2em;
        }
        .demo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        .demo-item {
            background: rgba(26, 26, 26, 0.6);
            backdrop-filter: blur(10px);
            border: 2px solid #333;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s ease;
        }
        .demo-item:hover {
            border-color: #d40000;
            box-shadow: 0 0 20px rgba(212, 0, 0, 0.3);
            transform: translateY(-5px);
        }
        .demo-item h3 {
            color: #d40000;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        .demo-item p {
            color: #c0c0c0;
            margin-bottom: 20px;
            font-size: 0.9em;
        }
        .banner-wrapper {
            display: inline-block;
            margin: 10px 0;
        }
        .generator-form {
            background: rgba(26, 26, 26, 0.6);
            backdrop-filter: blur(10px);
            border: 2px solid #333;
            border-radius: 12px;
            padding: 30px;
            margin-top: 40px;
        }
        .generator-form h2 {
            color: #d40000;
            margin-top: 0;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #d40000;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            background: #0a0a0a;
            border: 2px solid #333;
            border-radius: 6px;
            color: #e8e8e8;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #d40000;
            box-shadow: 0 0 10px rgba(212, 0, 0, 0.3);
        }
        .form-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr;
            gap: 15px;
        }
        .preview-area {
            margin-top: 30px;
            padding: 30px;
            background: rgba(10, 10, 10, 0.5);
            border: 2px solid #333;
            border-radius: 8px;
            text-align: center;
        }
        .preview-area h3 {
            color: #d40000;
            margin-top: 0;
            margin-bottom: 20px;
        }
        button {
            background: linear-gradient(135deg, #d40000 0%, #aa0000 100%);
            color: #e8e8e8;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }
        button:hover {
            box-shadow: 0 0 20px rgba(212, 0, 0, 0.5);
            transform: translateY(-2px);
        }
        .code-box {
            background: #0a0a0a;
            border: 2px solid #333;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
            text-align: left;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            color: #4ec9b0;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🐉 Banner Generator Demo</h1>
        <p class="subtitle">Pixelated Banners with Animated Dragon Edges</p>

        <div class="demo-grid">
            <div class="demo-item">
                <h3>Store Banner</h3>
                <p>Perfect for the store page</p>
                <div class="banner-wrapper">
                    <x-animated-banner text="STORE" :width="200" :height="80" />
                </div>
            </div>

            <div class="demo-item">
                <h3>Vote Banner</h3>
                <p>Encourage players to vote</p>
                <div class="banner-wrapper">
                    <x-animated-banner text="VOTE NOW" :width="200" :height="80" />
                </div>
            </div>

            <div class="demo-item">
                <h3>Play Banner</h3>
                <p>Call to action for players</p>
                <div class="banner-wrapper">
                    <x-animated-banner text="PLAY" :width="200" :height="80" />
                </div>
            </div>

            <div class="demo-item">
                <h3>Promotion Banner</h3>
                <p>Highlight special offers</p>
                <div class="banner-wrapper">
                    <x-animated-banner text="50% OFF" :width="200" :height="80" />
                </div>
            </div>

            <div class="demo-item">
                <h3>New Updates</h3>
                <p>Announce new content</p>
                <div class="banner-wrapper">
                    <x-animated-banner text="NEW UPDATE" :width="220" :height="80" />
                </div>
            </div>

            <div class="demo-item">
                <h3>Events Banner</h3>
                <p>Promote in-game events</p>
                <div class="banner-wrapper">
                    <x-animated-banner text="EVENT!" :width="200" :height="80" />
                </div>
            </div>
        </div>

        <div class="generator-form">
            <h2>Custom Banner Generator</h2>
            <form id="bannerForm">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="text">Banner Text</label>
                        <input type="text" id="text" name="text" value="ARAGON RSPS" maxlength="20">
                    </div>
                    <div class="form-group">
                        <label for="width">Width (px)</label>
                        <input type="number" id="width" name="width" value="200" min="100" max="500">
                    </div>
                    <div class="form-group">
                        <label for="height">Height (px)</label>
                        <input type="number" id="height" name="height" value="80" min="50" max="200">
                    </div>
                </div>
                <button type="button" onclick="generateBanner()">Generate Banner</button>
            </form>

            <div class="preview-area" id="previewArea">
                <h3>Preview</h3>
                <div id="bannerPreview"></div>
            </div>

            <div class="code-box" id="codeBox" style="display: none;">
                <strong style="color: #d40000;">Blade Component Usage:</strong><br>
                <code id="bladeCode"></code>
                <br><br>
                <strong style="color: #d40000;">Direct Image URL:</strong><br>
                <code id="urlCode"></code>
            </div>
        </div>
    </div>

    <script>
        function generateBanner() {
            const text = document.getElementById('text').value || 'ARAGON';
            const width = document.getElementById('width').value || 200;
            const height = document.getElementById('height').value || 80;

            const imageUrl = `/banner/generate?text=${encodeURIComponent(text)}&width=${width}&height=${height}`;
            
            document.getElementById('bannerPreview').innerHTML = `
                <div class="animated-banner-container" style="width: ${width}px; height: ${height}px; position: relative; display: inline-block;">
                    <img src="${imageUrl}" alt="${text}" style="display: block; width: 100%; height: 100%;">
                    <div class="banner-edge-overlay"></div>
                </div>
            `;

            document.getElementById('bladeCode').innerHTML = 
                `&lt;x-animated-banner text="${text}" :width="${width}" :height="${height}" /&gt;`;
            
            document.getElementById('urlCode').innerHTML = 
                `${window.location.origin}${imageUrl}`;
            
            document.getElementById('codeBox').style.display = 'block';
        }

        generateBanner();
    </script>
</body>
</html>
