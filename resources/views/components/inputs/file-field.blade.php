<?php 
if (!isset($mandatory)){
    $mandatory = false;
}

?>

<label class="form-label">
    {{ $label }}

    @if($mandatory)
        <span class="mandatory">*</span>
    @endif
</label>
<input type="file"  name="{{ $name }}" <?= $mandatory ? "required" : "" ?> {{ $attributes->merge(['class' => 'form-control',  ]) }}   />
@error($name)
    <div class="input-error">{{ $message }}</div>
@enderror
