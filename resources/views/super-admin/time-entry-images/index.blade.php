@extends('super-admin.layouts.app')

@section('title', 'Time Entry Images')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Time Entry Images</h1>
        <p class="page-subtitle">View all captured images during punch in/out</p>
    </div>
    <div class="page-actions">
        <div class="dropdown" style="display: inline-block; position: relative;">
            <button class="btn btn-secondary" type="button" id="selectDropdown" onclick="toggleDropdown()">
                <i class="fas fa-check-square"></i> Select
            </button>
            <ul class="dropdown-menu" id="selectDropdownMenu" style="display: none; position: absolute; top: 100%; left: 0; z-index: 1000; background: white; border: 1px solid #ccc; border-radius: 4px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); min-width: 150px; padding: 5px 0;">
                <li><a class="dropdown-item" href="#" onclick="selectAll(); hideDropdown(); return false;" style="display: block; padding: 8px 16px; text-decoration: none; color: #333;">Select All</a></li>
                <li><a class="dropdown-item" href="#" onclick="deselectAll(); hideDropdown(); return false;" style="display: block; padding: 8px 16px; text-decoration: none; color: #333;">Deselect All</a></li>
                <li><a class="dropdown-item" href="#" onclick="toggleSelection(); hideDropdown(); return false;" style="display: block; padding: 8px 16px; text-decoration: none; color: #333;">Toggle Selection</a></li>
            </ul>
        </div>
        <div id="bulkActions" style="display: none; margin-left: 10px;">
            <button class="btn btn-primary" onclick="downloadSelected()">
                <i class="fas fa-download"></i> Download (<span id="selectedCount">0</span>)
            </button>
            <button class="btn btn-danger" onclick="deleteSelected()">
                <i class="fas fa-trash"></i> Delete (<span id="selectedCount2">0</span>)
            </button>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card">
    <div class="card-body">
        <form method="GET" style="display: grid; grid-template-columns: 200px 200px 200px 200px 120px; gap: 15px; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Period</label>
                <select name="period" class="form-control" onchange="toggleCustomDates(this.value)">
                    <option value="current_month" {{ request('period', 'current_month') == 'current_month' ? 'selected' : '' }}>Current Month</option>
                    <option value="last_month" {{ request('period') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;" id="from_date_group" {{ request('period') != 'custom' ? 'style=display:none;' : '' }}>
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0;" id="to_date_group" {{ request('period') != 'custom' ? 'style=display:none;' : '' }}>
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Search Employee</label>
                <input type="text" name="search" class="form-control" placeholder="Name or ID" value="{{ request('search') }}">
            </div>
            <button type="submit" class="btn btn-secondary">
                <i class="fas fa-search"></i> Filter
            </button>
        </form>
    </div>
</div>

<!-- Images Grid -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Captured Images ({{ $images->total() }} total)</h3>
    </div>
    <div class="card-body">
        @if($images->count() > 0)
            <div class="images-grid">
                @foreach($images as $index => $image)
                <div class="image-card" data-image-id="{{ $image->id }}" data-image-file="{{ $image->imageFile }}">
                    <div class="image-container" onclick="openFullscreen({{ $index }})">
                        <img src="/entry_images/{{ $image->imageFile }}" alt="Entry Image" loading="lazy">
                        <div class="image-overlay">
                            <div class="image-info">
                                <div class="employee-name">{{ $image->employee->username ?? 'Unknown' }}</div>
                                <div class="entry-details">
                                    {{ ucwords(str_replace('_', ' ', $image->entry_type)) }} • 
                                    {{ $image->entry_time->format('M d, Y h:i A') }}
                                </div>
                            </div>
                        </div>
                        <div class="selection-checkbox">
                            <input type="checkbox" class="image-checkbox" value="{{ $image->id }}" onchange="updateSelection()">
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div style="margin-top: 20px;">
                {{ $images->appends(request()->query())->links() }}
            </div>
        @else
            <div style="text-align: center; padding: 60px; color: #6b7280;">
                <i class="fas fa-camera" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                <h3>No images found</h3>
                <p>No time entry images match your search criteria.</p>
            </div>
        @endif
    </div>
</div>

<!-- Hidden data for JavaScript -->
<script type="application/json" id="images-data">
{!! json_encode($images->map(function($image) {
    return [
        'imageFile' => $image->imageFile,
        'employee' => $image->employee->username ?? 'Unknown',
        'entry_type' => ucwords(str_replace('_', ' ', $image->entry_type)),
        'entry_time' => $image->entry_time->format('M d, Y h:i A')
    ];
})) !!}
</script>
@endsection

@push('styles')
<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
}

.page-actions {
    display: flex;
    align-items: center;
}

.images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.image-card {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.image-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.image-card.selected {
    border: 3px solid #ff6b35;
    box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
}

.image-container {
    position: relative;
    width: 100%;
    height: 250px;
    overflow: hidden;
    cursor: pointer;
}

.image-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.image-card:hover img {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
    backdrop-filter: blur(10px);
    padding: 20px 15px 15px;
    color: white;
}

.employee-name {
    font-weight: 600;
    font-size: 16px;
    margin-bottom: 4px;
}

.entry-details {
    font-size: 12px;
    opacity: 0.9;
}

.selection-checkbox {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
}

.selection-checkbox input[type="checkbox"] {
    width: 20px;
    height: 20px;
    cursor: pointer;
    accent-color: #ff6b35;
}

/* Fullscreen viewer */
.fullscreen-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.95);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fullscreen-container {
    position: relative;
    width: 80vw;
    height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fullscreen-image {
    width: 80vw;
    height: 80vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
}

.fullscreen-info {
    position: absolute;
    bottom: -60px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    text-align: center;
    background: rgba(0, 0, 0, 0.7);
    padding: 10px 20px;
    border-radius: 8px;
    backdrop-filter: blur(10px);
}

.nav-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 24px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
    backdrop-filter: blur(10px);
}

.nav-arrow:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-50%) scale(1.1);
}

.nav-arrow.prev {
    left: 20px;
}

.nav-arrow.next {
    right: 20px;
}

.close-btn {
    position: absolute;
    top: 20px;
    right: 20px;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    font-size: 20px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.2s ease;
    backdrop-filter: blur(10px);
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.1);
}
</style>
@endpush

@push('scripts')
<script>
let currentImageIndex = 0;
let imagesData = [];

document.addEventListener('DOMContentLoaded', function() {
    const imagesDataElement = document.getElementById('images-data');
    if (imagesDataElement) {
        imagesData = JSON.parse(imagesDataElement.textContent);
    }
});

function openFullscreen(index) {
    // Don't open fullscreen if clicking on checkbox
    if (event.target.type === 'checkbox') {
        event.stopPropagation();
        return;
    }
    currentImageIndex = index;
    showFullscreenImage();
}

// Selection functions
function selectAll() {
    document.querySelectorAll('.image-checkbox').forEach(cb => {
        cb.checked = true;
        cb.closest('.image-card').classList.add('selected');
    });
    updateSelection();
}

function deselectAll() {
    document.querySelectorAll('.image-checkbox').forEach(cb => {
        cb.checked = false;
        cb.closest('.image-card').classList.remove('selected');
    });
    updateSelection();
}

function toggleSelection() {
    document.querySelectorAll('.image-checkbox').forEach(cb => {
        cb.checked = !cb.checked;
        if (cb.checked) {
            cb.closest('.image-card').classList.add('selected');
        } else {
            cb.closest('.image-card').classList.remove('selected');
        }
    });
    updateSelection();
}

function updateSelection() {
    const selected = document.querySelectorAll('.image-checkbox:checked');
    const count = selected.length;
    
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('selectedCount2').textContent = count;
    
    if (count > 0) {
        document.getElementById('bulkActions').style.display = 'inline-block';
    } else {
        document.getElementById('bulkActions').style.display = 'none';
    }
    
    // Update card styling
    document.querySelectorAll('.image-card').forEach(card => {
        const checkbox = card.querySelector('.image-checkbox');
        if (checkbox.checked) {
            card.classList.add('selected');
        } else {
            card.classList.remove('selected');
        }
    });
}

function downloadSelected() {
    const selected = Array.from(document.querySelectorAll('.image-checkbox:checked'));
    if (selected.length === 0) return;
    
    const imageFiles = selected.map(cb => cb.closest('.image-card').dataset.imageFile);
    
    // Create form for bulk download
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/super-admin/time-entry-images/download';
    form.style.display = 'none';
    
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfToken);
    
    imageFiles.forEach(file => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'images[]';
        input.value = file;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function deleteSelected() {
    const selected = Array.from(document.querySelectorAll('.image-checkbox:checked'));
    if (selected.length === 0) return;
    
    const imageIds = selected.map(cb => cb.value);
    
    Swal.fire({
        title: 'Delete Images?',
        text: `Are you sure you want to delete ${imageIds.length} selected image(s)? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete them!'
    }).then((result) => {
        if (result.isConfirmed) {
            // Create form for bulk delete
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/super-admin/time-entry-images/delete';
            form.style.display = 'none';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrfToken);
            
            imageIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'image_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function showFullscreenImage() {
    if (imagesData.length === 0) return;
    
    const image = imagesData[currentImageIndex];
    
    const overlay = document.createElement('div');
    overlay.className = 'fullscreen-overlay';
    overlay.onclick = (e) => {
        if (e.target === overlay) closeFullscreen();
    };
    
    const container = document.createElement('div');
    container.className = 'fullscreen-container';
    
    const img = document.createElement('img');
    img.src = `/entry_images/${image.imageFile}`;
    img.className = 'fullscreen-image';
    img.alt = 'Entry Image';
    
    const info = document.createElement('div');
    info.className = 'fullscreen-info';
    info.innerHTML = `
        <div style="font-weight: 600; margin-bottom: 4px;">${image.employee}</div>
        <div style="font-size: 14px; opacity: 0.9;">${image.entry_type} • ${image.entry_time}</div>
    `;
    
    const closeBtn = document.createElement('button');
    closeBtn.className = 'close-btn';
    closeBtn.innerHTML = '<i class="fas fa-times"></i>';
    closeBtn.onclick = closeFullscreen;
    
    if (imagesData.length > 1) {
        const prevBtn = document.createElement('button');
        prevBtn.className = 'nav-arrow prev';
        prevBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
        prevBtn.onclick = (e) => {
            e.stopPropagation();
            navigateImage(-1);
        };
        
        const nextBtn = document.createElement('button');
        nextBtn.className = 'nav-arrow next';
        nextBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
        nextBtn.onclick = (e) => {
            e.stopPropagation();
            navigateImage(1);
        };
        
        container.appendChild(prevBtn);
        container.appendChild(nextBtn);
    }
    
    container.appendChild(img);
    container.appendChild(info);
    container.appendChild(closeBtn);
    overlay.appendChild(container);
    
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';
    
    // Keyboard navigation
    document.addEventListener('keydown', handleKeydown);
}

function navigateImage(direction) {
    currentImageIndex += direction;
    if (currentImageIndex < 0) currentImageIndex = imagesData.length - 1;
    if (currentImageIndex >= imagesData.length) currentImageIndex = 0;
    
    const overlay = document.querySelector('.fullscreen-overlay');
    if (overlay) {
        document.body.removeChild(overlay);
        showFullscreenImage();
    }
}

function closeFullscreen() {
    const overlay = document.querySelector('.fullscreen-overlay');
    if (overlay) {
        document.body.removeChild(overlay);
        document.body.style.overflow = 'auto';
        document.removeEventListener('keydown', handleKeydown);
    }
}

function handleKeydown(e) {
    switch(e.key) {
        case 'Escape':
            closeFullscreen();
            break;
        case 'ArrowLeft':
            navigateImage(-1);
            break;
        case 'ArrowRight':
            navigateImage(1);
            break;
    }
}

function toggleCustomDates(value) {
    const fromDateGroup = document.getElementById('from_date_group');
    const toDateGroup = document.getElementById('to_date_group');
    
    if (value === 'custom') {
        fromDateGroup.style.display = 'block';
        toDateGroup.style.display = 'block';
    } else {
        fromDateGroup.style.display = 'none';
        toDateGroup.style.display = 'none';
    }
}

// Dropdown functions
function toggleDropdown() {
    const menu = document.getElementById('selectDropdownMenu');
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function hideDropdown() {
    document.getElementById('selectDropdownMenu').style.display = 'none';
}

// Hide dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('#selectDropdown') && !e.target.closest('#selectDropdownMenu')) {
        hideDropdown();
    }
});
</script>
@endpush