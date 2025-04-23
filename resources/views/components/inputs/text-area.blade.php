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
?>

<label class="form-label">
    {{ $label }}

    @if($mandatory)
        <span class="mandatory">*</span>
    @endif
</label>

<textarea name="{{ $name }}" <?= $mandatory ? "required" : "" ?> {{ $attributes->merge(['rows' => 4]) }} class="{{ $cls }}" >{{ old($name, $value) }}</textarea>

@error($errorName)
    <div class="input-error">{{ $message }}</div>
@enderror
