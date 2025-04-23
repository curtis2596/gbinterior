<?php 
if (!isset($mandatory)){
    $mandatory = false;
} 

if (!isset($is_disabled)){
    $is_disabled = false;
} 

if (!isset($value)){
    $value = "";
} 

if (!isset($errorName) || empty($errorName)){
    $errorName = $name;
}

if ($is_disabled)
{
    $mandatory = false;
}

$cls = $attributes->get("class", 'form-control invalid-char');

$attr = "";

if ($mandatory)
{
    $attr .= ' required="true"';
}

if ($is_disabled)
{
    $attr .= ' disabled="true"';
}

// d($attr);
?>

@if(isset($label) && $label)
<label class="form-label">
    {{ $label }}

    @if($mandatory)
        <span class="mandatory">*</span>
    @endif
</label>
@endif

<input name="{{ $name }}" value="{{ old($name, $value) }}" class="{{ $cls }}" {{ $attributes->merge(['type' => 'text' ]) }}  {!! $attr !!}/>

@error($errorName)
    <div class="input-error">{{ $message }}</div>
@enderror
