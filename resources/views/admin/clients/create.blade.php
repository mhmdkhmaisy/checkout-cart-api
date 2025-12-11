@extends('admin.layout')

@section('title', 'Upload New Client')
@section('header', 'Upload New Client')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass-effect rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-semibold text-dragon-silver">Upload New Client</h2>
                <p class="text-dragon-silver-dark text-sm">Add a new game client for a specific platform</p>
            </div>
            <a href="{{ route('admin.clients.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Back to Clients
            </a>
        </div>

        <form action="{{ route('admin.clients.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" id="upload-form">
            @csrf
            
            <!-- Platform Selection -->
            <div>
                <label for="os" class="block text-sm font-medium text-dragon-silver mb-2">Platform *</label>
                <select name="os" id="os" class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:outline-none focus:border-dragon-red @error('os') border-red-500 @enderror" required>
                    <option value="">Select Platform</option>
                    @foreach($osTypes as $value => $label)
                        <option value="{{ $value }}" 
                                data-extension="{{ App\Models\Client::FILE_EXTENSIONS[$value] }}"
                                {{ old('os') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('os')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dragon-silver-dark text-sm mt-1">Select the target platform for this client build.</p>
            </div>

            <!-- File Upload -->
            <div>
                <label for="file" class="block text-sm font-medium text-dragon-silver mb-2">Client File *</label>
                <div class="relative">
                    <input type="file" class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:outline-none focus:border-dragon-red @error('file') border-red-500 @enderror" 
                           id="file" name="file" required accept=".exe,.jar">
                </div>
                @error('file')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dragon-silver-dark text-sm mt-1" id="file-help">
                    Maximum file size: 500MB. Expected extension will be shown when you select a platform.
                </p>
            </div>

            <!-- Version -->
            <div>
                <label for="version" class="block text-sm font-medium text-dragon-silver mb-2">Version</label>
                <input type="text" class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:outline-none focus:border-dragon-red @error('version') border-red-500 @enderror" 
                       id="version" name="version" value="{{ old('version') }}" 
                       placeholder="e.g., 0.1.3" pattern="^\d+\.\d+\.\d+$">
                @error('version')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dragon-silver-dark text-sm mt-1">
                    Leave empty to auto-increment from the latest version. Format: major.minor.patch (e.g., 0.1.3)
                </p>
            </div>

            <!-- Changelog -->
            <div>
                <label for="changelog" class="block text-sm font-medium text-dragon-silver mb-2">Changelog</label>
                <textarea class="w-full bg-dragon-black border border-dragon-border text-dragon-silver rounded-lg px-3 py-2 focus:outline-none focus:border-dragon-red @error('changelog') border-red-500 @enderror" 
                          id="changelog" name="changelog" rows="4" 
                          placeholder="What's new in this version?">{{ old('changelog') }}</textarea>
                @error('changelog')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-dragon-silver-dark text-sm mt-1">Optional. Describe what's new or changed in this version.</p>
            </div>

            <!-- Enable Client -->
            <div>
                <label class="flex items-center">
                    <input type="checkbox" class="sr-only peer" id="enabled" name="enabled" 
                           value="1" {{ old('enabled', true) ? 'checked' : '' }}>
                    <div class="relative w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-dragon-red"></div>
                    <span class="ml-3 text-dragon-silver">Enable this client immediately</span>
                </label>
                <p class="text-dragon-silver-dark text-sm mt-1">
                    If checked, this will become the active client for the selected platform and disable any previous versions.
                </p>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-between pt-6">
                <a href="{{ route('admin.clients.index') }}" class="px-6 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors">Cancel</a>
                <button type="submit" id="upload-btn" class="px-6 py-2 bg-dragon-red hover:bg-dragon-red-bright text-dragon-silver rounded-lg transition-colors">
                    <i class="fas fa-upload mr-2"></i>Upload Client
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Upload Progress Modal -->
<div id="upload-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="bg-dragon-black border border-dragon-border rounded-lg p-8 max-w-md w-full mx-4">
        <div class="text-center">
            <!-- Upload Icon -->
            <div class="mb-6">
                <div class="relative inline-block">
                    <i class="fas fa-cloud-upload-alt text-6xl text-dragon-red mb-4" id="upload-icon"></i>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="animate-spin rounded-full h-16 w-16 border-2 border-dragon-red border-t-transparent" id="upload-spinner"></div>
                    </div>
                </div>
            </div>

            <!-- Upload Status -->
            <h3 class="text-xl font-semibold text-dragon-silver mb-2" id="upload-status">Uploading Client...</h3>
            <p class="text-dragon-silver-dark mb-6" id="upload-message">Please wait while your file is being uploaded and processed.</p>

            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="flex justify-between text-sm text-dragon-silver-dark mb-2">
                    <span id="progress-text">Uploading...</span>
                    <span id="progress-percent">0%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-3">
                    <div class="bg-gradient-to-r from-dragon-red to-dragon-red-bright h-3 rounded-full transition-all duration-300 ease-out" 
                         style="width: 0%" id="progress-bar"></div>
                </div>
            </div>

            <!-- Upload Stats -->
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div class="text-center">
                    <div class="text-dragon-silver-dark">Upload Speed</div>
                    <div class="text-dragon-silver font-medium" id="upload-speed">-- KB/s</div>
                </div>
                <div class="text-center">
                    <div class="text-dragon-silver-dark">Time Remaining</div>
                    <div class="text-dragon-silver font-medium" id="time-remaining">Calculating...</div>
                </div>
            </div>

            <!-- File Info -->
            <div class="mt-6 p-4 bg-dragon-black/50 rounded-lg">
                <div class="text-sm">
                    <div class="flex justify-between mb-1">
                        <span class="text-dragon-silver-dark">File:</span>
                        <span class="text-dragon-silver" id="file-name">--</span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span class="text-dragon-silver-dark">Size:</span>
                        <span class="text-dragon-silver" id="file-size">--</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-dragon-silver-dark">Uploaded:</span>
                        <span class="text-dragon-silver" id="uploaded-size">0 B</span>
                    </div>
                </div>
            </div>

            <!-- Cancel Button (only show during upload) -->
            <button type="button" id="cancel-upload" class="mt-6 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                <i class="fas fa-times mr-2"></i>Cancel Upload
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let uploadStartTime = 0;
    let uploadedBytes = 0;
    let totalBytes = 0;
    let uploadXHR = null;

    // Update file extension help text when platform changes
    $('#os').change(function() {
        const selectedOption = $(this).find(':selected');
        const extension = selectedOption.data('extension');
        
        if (extension) {
            $('#file-help').html(`Maximum file size: 500MB. Expected extension: <strong>${extension}</strong>`);
            $('#file').attr('accept', extension);
        } else {
            $('#file-help').text('Maximum file size: 500MB. Expected extension will be shown when you select a platform.');
            $('#file').attr('accept', '.exe,.jar');
        }
    });

    // Auto-generate version suggestion
    $('#os').change(function() {
        const os = $(this).val();
        if (os && !$('#version').val()) {
            $('#version').attr('placeholder', 'Auto-generated (e.g., 0.1.3)');
        }
    });

    // Handle form submission with progress tracking
    $('#upload-form').on('submit', function(e) {
        e.preventDefault();
        
        const fileInput = $('#file')[0];
        const file = fileInput.files[0];
        
        if (!file) {
            alert('Please select a file to upload.');
            return;
        }

        // Show upload modal
        showUploadModal(file);
        
        // Prepare form data - ensure all form fields are included
        const formData = new FormData();
        
        // Add all form fields manually to ensure they're included
        formData.append('_token', $('input[name="_token"]').val());
        formData.append('os', $('#os').val());
        formData.append('file', file);
        formData.append('version', $('#version').val() || '');
        formData.append('changelog', $('#changelog').val() || '');
        formData.append('enabled', $('#enabled').is(':checked') ? '1' : '0');
        
        // Initialize upload tracking
        uploadStartTime = Date.now();
        totalBytes = file.size;
        uploadedBytes = 0;

        // Create XMLHttpRequest for progress tracking
        uploadXHR = new XMLHttpRequest();
        
        // Track upload progress
        uploadXHR.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                updateProgress(e.loaded, e.total);
            }
        });

        // Handle completion
        uploadXHR.addEventListener('load', function() {
            console.log('Upload completed with status:', uploadXHR.status);
            console.log('Response length:', uploadXHR.responseText.length);
            console.log('Response preview:', uploadXHR.responseText.substring(0, 500));
            
            if (uploadXHR.status >= 200 && uploadXHR.status < 400) {
                const responseText = uploadXHR.responseText.toLowerCase();
                
                // Check for validation errors in HTML response
                if (responseText.includes('text-red-400') || responseText.includes('is-invalid') || responseText.includes('the os field') || responseText.includes('the file field')) {
                    // Extract error message if possible
                    const errorMatch = uploadXHR.responseText.match(/<p class="text-red-400[^>]*>([^<]+)<\/p>/);
                    if (errorMatch) {
                        showUploadError('Validation error: ' + errorMatch[1]);
                    } else {
                        showUploadError('Validation failed. Check Laravel logs for details. OS value: ' + $('#os').val());
                    }
                    return;
                }
                
                // Check for success indicators (redirected to clients page or success message)
                if (responseText.includes('client management') || responseText.includes('uploaded successfully') || responseText.includes('success')) {
                    showUploadSuccess();
                    setTimeout(() => {
                        window.location.href = "/admin/clients";
                    }, 2000);
                } else {
                    // Try to parse as JSON for error messages
                    try {
                        const response = JSON.parse(uploadXHR.responseText);
                        if (response.errors) {
                            let errorMsg = 'Validation errors:\n';
                            for (let field in response.errors) {
                                errorMsg += `${field}: ${response.errors[field].join(', ')}\n`;
                            }
                            showUploadError(errorMsg);
                        } else {
                            showUploadError('Upload may have succeeded. Redirecting...');
                            setTimeout(() => {
                                window.location.href = "/admin/clients";
                            }, 2000);
                        }
                    } catch (e) {
                        // HTML response - assume success if no error indicators
                        console.log('Non-JSON response, assuming success');
                        showUploadSuccess();
                        setTimeout(() => {
                            window.location.href = "/admin/clients";
                        }, 2000);
                    }
                }
            } else {
                console.error('Upload failed with status:', uploadXHR.status);
                console.error('Response:', uploadXHR.responseText.substring(0, 1000));
                showUploadError(`Upload failed with status ${uploadXHR.status}. Check browser console for details.`);
            }
        });

        // Handle errors
        uploadXHR.addEventListener('error', function() {
            console.error('Upload error occurred');
            showUploadError('Network error occurred during upload.');
        });

        // Handle abort
        uploadXHR.addEventListener('abort', function() {
            console.log('Upload aborted');
            hideUploadModal();
        });

        // Send request
        uploadXHR.open('POST', $(this).attr('action'));
        uploadXHR.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        uploadXHR.send(formData);
    });

    // Cancel upload
    $('#cancel-upload').on('click', function() {
        if (uploadXHR) {
            uploadXHR.abort();
        }
        hideUploadModal();
    });

    function showUploadModal(file) {
        // Update file info
        $('#file-name').text(file.name);
        $('#file-size').text(formatBytes(file.size));
        
        // Reset progress
        $('#progress-bar').css('width', '0%');
        $('#progress-percent').text('0%');
        $('#progress-text').text('Initializing...');
        $('#upload-speed').text('-- KB/s');
        $('#time-remaining').text('Calculating...');
        $('#uploaded-size').text('0 B');
        
        // Show modal
        $('#upload-modal').removeClass('hidden');
        
        // Disable form
        $('#upload-form input, #upload-form select, #upload-form textarea, #upload-form button').prop('disabled', true);
    }

    function updateProgress(loaded, total) {
        uploadedBytes = loaded;
        const percent = Math.round((loaded / total) * 100);
        const elapsed = (Date.now() - uploadStartTime) / 1000; // seconds
        const speed = loaded / elapsed; // bytes per second
        const remaining = (total - loaded) / speed; // seconds remaining

        // Update progress bar
        $('#progress-bar').css('width', percent + '%');
        $('#progress-percent').text(percent + '%');
        $('#uploaded-size').text(formatBytes(loaded));
        
        // Update status text
        if (percent < 100) {
            $('#progress-text').text('Uploading...');
        } else {
            $('#progress-text').text('Processing...');
        }
        
        // Update speed
        $('#upload-speed').text(formatSpeed(speed));
        
        // Update time remaining
        if (remaining > 0 && percent < 100) {
            $('#time-remaining').text(formatTime(remaining));
        } else {
            $('#time-remaining').text('Almost done...');
        }
    }

    function showUploadSuccess() {
        $('#upload-status').text('Upload Complete!');
        $('#upload-message').text('Your client has been successfully uploaded and processed.');
        $('#progress-text').text('Complete');
        $('#progress-bar').css('width', '100%');
        $('#progress-percent').text('100%');
        $('#time-remaining').text('Done!');
        $('#cancel-upload').hide();
        
        // Change icon to success
        $('#upload-icon').removeClass('fa-cloud-upload-alt').addClass('fa-check-circle text-green-500');
        $('#upload-spinner').hide();
    }

    function showUploadError(message) {
        $('#upload-status').text('Upload Failed');
        $('#upload-message').text(message);
        $('#progress-text').text('Error');
        $('#cancel-upload').text('Close').off('click').on('click', hideUploadModal);
        
        // Change icon to error
        $('#upload-icon').removeClass('fa-cloud-upload-alt').addClass('fa-exclamation-circle text-red-500');
        $('#upload-spinner').hide();
        
        // Re-enable form
        $('#upload-form input, #upload-form select, #upload-form textarea, #upload-form button').prop('disabled', false);
    }

    function hideUploadModal() {
        $('#upload-modal').addClass('hidden');
        
        // Re-enable form
        $('#upload-form input, #upload-form select, #upload-form textarea, #upload-form button').prop('disabled', false);
        
        // Reset modal state
        $('#upload-icon').removeClass('fa-check-circle fa-exclamation-circle text-green-500 text-red-500').addClass('fa-cloud-upload-alt text-dragon-red');
        $('#upload-spinner').show();
        $('#cancel-upload').show().text('Cancel Upload');
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function formatSpeed(bytesPerSecond) {
        return formatBytes(bytesPerSecond) + '/s';
    }

    function formatTime(seconds) {
        if (seconds < 60) {
            return Math.round(seconds) + 's';
        } else if (seconds < 3600) {
            const minutes = Math.floor(seconds / 60);
            const secs = Math.round(seconds % 60);
            return minutes + 'm ' + secs + 's';
        } else {
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            return hours + 'h ' + minutes + 'm';
        }
    }
});
</script>
@endpush
@endsection