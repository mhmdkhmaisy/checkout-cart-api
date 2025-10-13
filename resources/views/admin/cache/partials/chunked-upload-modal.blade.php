<!-- Chunked Upload Modal with Uppy.js -->
<div id="chunked-upload-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg max-w-4xl w-full mx-4 max-h-[90vh] overflow-auto">
        <div class="p-6 border-b border-dragon-border">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-semibold text-dragon-silver">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Chunked Upload (High Performance)
                </h3>
                <button onclick="hideChunkedUploadModal()" class="text-dragon-silver-dark hover:text-dragon-silver">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            <p class="text-dragon-silver-dark mt-2">
                Upload large files with automatic chunking, resume capability, and parallel uploads for maximum speed.
            </p>
        </div>

        <div class="p-6">
            <!-- Uppy Dashboard Container -->
            <div id="uppy-dashboard"></div>

            <!-- Upload Statistics -->
            <div id="upload-stats" class="mt-6 p-4 bg-dragon-black/30 rounded-lg hidden">
                <h4 class="text-lg font-semibold text-dragon-silver mb-3">Upload Statistics</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-dragon-silver-dark text-sm">Files Queued</p>
                        <p class="text-2xl font-bold text-blue-400" id="stat-queued">0</p>
                    </div>
                    <div>
                        <p class="text-dragon-silver-dark text-sm">Uploading</p>
                        <p class="text-2xl font-bold text-yellow-400" id="stat-uploading">0</p>
                    </div>
                    <div>
                        <p class="text-dragon-silver-dark text-sm">Completed</p>
                        <p class="text-2xl font-bold text-green-400" id="stat-completed">0</p>
                    </div>
                    <div>
                        <p class="text-dragon-silver-dark text-sm">Upload Speed</p>
                        <p class="text-2xl font-bold text-dragon-red" id="stat-speed">0 MB/s</p>
                    </div>
                </div>
            </div>

            <!-- Processing Queue -->
            <div id="processing-queue" class="mt-6 hidden">
                <h4 class="text-lg font-semibold text-dragon-silver mb-3">
                    <i class="fas fa-cog fa-spin mr-2"></i>Processing Files...
                </h4>
                <div id="processing-list" class="space-y-2"></div>
            </div>
        </div>
    </div>
</div>

<!-- Uppy.js CSS and JS via CDN -->
<link href="https://releases.transloadit.com/uppy/v3.20.0/uppy.min.css" rel="stylesheet">
<script src="https://releases.transloadit.com/uppy/v3.20.0/uppy.min.js"></script>

<script>
let uppyInstance = null;
let uploadStats = {
    queued: 0,
    uploading: 0,
    completed: 0,
    failed: 0
};

function initializeUppy() {
    const { Uppy, Dashboard, Tus, ProgressBar } = window.Uppy;

    uppyInstance = new Uppy({
        debug: true,
        autoProceed: false,
        allowMultipleUploadBatches: true,
        restrictions: {
            maxFileSize: 2 * 1024 * 1024 * 1024, // 2GB per file
            minNumberOfFiles: 1,
        },
        onBeforeFileAdded: (currentFile, files) => {
            const modifiedFile = {
                ...currentFile,
                meta: {
                    ...currentFile.meta,
                    relativePath: currentFile.meta.relativePath || currentFile.meta.webkitRelativePath || null
                }
            };
            return modifiedFile;
        }
    })
    .use(Dashboard, {
        inline: true,
        target: '#uppy-dashboard',
        height: 400,
        showProgressDetails: true,
        proudlyDisplayPoweredByUppy: false,
        theme: 'dark',
        note: 'Files are uploaded in chunks for reliability. Upload will resume automatically if interrupted.',
        browserBackButtonClose: false,
    })
    .use(Tus, {
        endpoint: '{{ route("admin.cache.chunked-upload") }}',
        chunkSize: 5 * 1024 * 1024, // 5MB chunks
        retryDelays: [0, 1000, 3000, 5000],
        parallelUploads: 3,
        removeFingerprintOnSuccess: true,
        storeFingerprintForResuming: true,
    });

    // Event Handlers
    uppyInstance.on('file-added', (file) => {
        uploadStats.queued++;
        updateUploadStats();
        document.getElementById('upload-stats').classList.remove('hidden');
    });

    uppyInstance.on('file-removed', (file) => {
        uploadStats.queued = Math.max(0, uploadStats.queued - 1);
        updateUploadStats();
    });

    uppyInstance.on('upload-started', () => {
        console.log('Upload started');
    });

    uppyInstance.on('upload-progress', (file, progress) => {
        if (progress.bytesUploaded === progress.bytesTotal) {
            uploadStats.uploading = Math.max(0, uploadStats.uploading - 1);
            uploadStats.completed++;
            updateUploadStats();
        }
    });

    uppyInstance.on('upload-success', (file, response) => {
        console.log('Upload success:', file.name);
        
        // Start polling for processing status
        if (response.uploadURL) {
            const uploadKey = extractUploadKeyFromUrl(response.uploadURL);
            pollProcessingStatus(uploadKey, file.name);
        }
    });

    uppyInstance.on('upload-error', (file, error, response) => {
        console.error('Upload error:', error);
        uploadStats.failed++;
        updateUploadStats();
        
        uppyInstance.info({
            message: `Failed to upload ${file.name}: ${error.message}`,
            details: error
        }, 'error', 5000);
    });

    uppyInstance.on('complete', (result) => {
        console.log('All uploads complete:', result);
        
        if (result.successful.length > 0) {
            uppyInstance.info({
                message: `Successfully uploaded ${result.successful.length} file(s). Processing in background...`,
            }, 'success', 5000);
        }

        if (result.failed.length > 0) {
            uppyInstance.info({
                message: `${result.failed.length} file(s) failed to upload`,
            }, 'error', 5000);
        }
    });

    // Track upload speed
    let lastUpdate = Date.now();
    let lastLoaded = 0;
    
    uppyInstance.on('progress', (progress) => {
        const now = Date.now();
        const timeDiff = (now - lastUpdate) / 1000; // seconds
        
        if (timeDiff > 0.5) { // Update every 500ms
            const bytesDiff = progress.bytesUploaded - lastLoaded;
            const speed = bytesDiff / timeDiff / (1024 * 1024); // MB/s
            
            document.getElementById('stat-speed').textContent = speed.toFixed(2) + ' MB/s';
            
            lastUpdate = now;
            lastLoaded = progress.bytesUploaded;
        }
    });
}

function updateUploadStats() {
    document.getElementById('stat-queued').textContent = uploadStats.queued;
    document.getElementById('stat-uploading').textContent = uploadStats.uploading;
    document.getElementById('stat-completed').textContent = uploadStats.completed;
}

function extractUploadKeyFromUrl(url) {
    const parts = url.split('/');
    return parts[parts.length - 1];
}

function pollProcessingStatus(uploadKey, filename) {
    const processingQueue = document.getElementById('processing-queue');
    const processingList = document.getElementById('processing-list');
    
    processingQueue.classList.remove('hidden');
    
    const itemId = 'processing-' + uploadKey;
    let item = document.getElementById(itemId);
    
    if (!item) {
        item = document.createElement('div');
        item.id = itemId;
        item.className = 'bg-dragon-black/30 rounded p-3';
        item.innerHTML = `
            <div class="flex justify-between items-center">
                <span class="text-dragon-silver">${filename}</span>
                <span class="text-dragon-silver-dark text-sm" id="status-${uploadKey}">Processing...</span>
            </div>
        `;
        processingList.appendChild(item);
    }
    
    const pollInterval = setInterval(async () => {
        try {
            const response = await fetch(`{{ route('admin.cache.chunked-upload-status') }}?upload_key=${uploadKey}`);
            const data = await response.json();
            
            const statusEl = document.getElementById('status-' + uploadKey);
            
            if (data.status === 'completed') {
                clearInterval(pollInterval);
                if (statusEl) statusEl.innerHTML = '<span class="text-green-400"><i class="fas fa-check-circle mr-1"></i>Complete</span>';
                
                setTimeout(() => {
                    item.remove();
                    if (processingList.children.length === 0) {
                        processingQueue.classList.add('hidden');
                        // Refresh page to show new files
                        location.reload();
                    }
                }, 2000);
            } else if (data.status === 'failed') {
                clearInterval(pollInterval);
                if (statusEl) statusEl.innerHTML = '<span class="text-red-400"><i class="fas fa-times-circle mr-1"></i>Failed</span>';
            } else if (data.status === 'processing') {
                if (statusEl) statusEl.textContent = 'Processing: ' + data.progress_percentage + '%';
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }, 1000);
}

function showChunkedUploadModal() {
    document.getElementById('chunked-upload-modal').classList.remove('hidden');
    if (!uppyInstance) {
        initializeUppy();
    }
    uploadStats = { queued: 0, uploading: 0, completed: 0, failed: 0 };
    updateUploadStats();
}

function hideChunkedUploadModal() {
    document.getElementById('chunked-upload-modal').classList.add('hidden');
    if (uppyInstance) {
        uppyInstance.cancelAll();
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add keyboard shortcut (Ctrl+Shift+U) to open chunked upload
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey && e.shiftKey && e.key === 'U') {
            e.preventDefault();
            showChunkedUploadModal();
        }
    });
});
</script>

<style>
.uppy-Dashboard--modal {
    z-index: 9999 !important;
}

.uppy-Dashboard-inner {
    background-color: #1a1a2e !important;
    border: 1px solid #2a2a3e !important;
}

.uppy-Dashboard-AddFiles {
    border-color: #ef4444 !important;
}

.uppy-Dashboard-AddFiles:hover {
    border-color: #dc2626 !important;
}

.uppy-StatusBar {
    background-color: #0f0f1e !important;
    border-top: 1px solid #2a2a3e !important;
}

.uppy-DashboardContent-bar {
    background-color: #0f0f1e !important;
    border-bottom: 1px solid #2a2a3e !important;
}

.uppy-DashboardItem {
    border-bottom: 1px solid #2a2a3e !important;
}
</style>
