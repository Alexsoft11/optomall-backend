<!-- resources/views/filament/components/image-preview.blade.php -->
<div>
    @if (!empty($getRecord()->image))
        <img src="{{ $getRecord()->image }}" alt="Image Preview" style="max-width: 200px; height: auto;">
    @else
        <p>No image available</p>
    @endif
</div>
