<div>
    @if (!empty($getRecord()->image_url) || !empty($getRecord()->image))
        <img src="{{ $getRecord()->image_url ?? $getRecord()->image }}" alt="Image Preview"
            style="max-width: 200px; height: auto;">
    @else
        <p>No image available</p>
    @endif
</div>
