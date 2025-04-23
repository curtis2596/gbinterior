<?php 
if (!isset($mandatory)){
    $mandatory = false;
} 

if (!isset($value)){
    $value = "";
} 

if (!isset($errorName) || empty($errorName)){
    $errorName = $name;
} 

$cls = $attributes->get("class", 'form-control invalid-char');
$id = $attributes->get("id", $name);
?>

<label class="form-check">
    @php
        $attr = old($name, $value) ? 'checked="checked"' : '';
    @endphp
    <input type="hidden" name="{{ $name }}" value="0">
    <input type="checkbox" name="{{ $name }}" value="1" id="{{ $id }}" {{ $attributes->merge(['class' => 'form-check-input primary']) }} {!! $attr !!}>
    <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
</label>

@error($errorName)
    <div class="input-error">{{ $message }}</div>
@enderror