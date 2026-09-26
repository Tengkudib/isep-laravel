<div class="icon-preset-grid">
@foreach ($icon_presets as $i => $preset)
    <label class="icon-preset-option" for="icon_{{ $group_id }}_{{ $i }}"><input type="radio" name="icon" id="icon_{{ $group_id }}_{{ $i }}" value="{{ $preset['icon'] }}" {{ $preset['icon'] === $selected_icon ? 'checked' : '' }}><div class="icon-preset-box"><i class="{{ $preset['icon'] }}"></i><span>{{ $preset['label'] }}</span></div></label>
@endforeach
</div>
