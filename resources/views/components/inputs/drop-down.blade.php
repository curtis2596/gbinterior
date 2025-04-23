{{-- <?php 
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

$attr = "";

if ($mandatory)
{
    $attr .= ' required="true"';
}

if ($is_disabled)
{
    $attr .= ' disabled="true"';
}

?>

@if(isset($label) && $label)
<label class="form-label">
    {{ $label }}
    @if($mandatory)
        <span class="mandatory">*</span>
    @endif
</label>
@endif

@php
   $value_list = [];

   $name_without_square_braces = str_replace("[]", "", $name);

   $v = old($name_without_square_braces, $value);

   if (is_string($v) && strpos($v, ",") !== false)
   {
        $value_list = $v ? explode(",", $v) : [];
   }
   else if (is_array($v))
   {
        $value_list = $v;
   }
   else
   {
        $value_list[] = $v;
   }

@endphp
<select name="{{ $name }}" {{ $attributes->merge(['class' => 'form-control']) }} {!! $attr !!}>
    @if (!$attributes->has('multiple')) 
        @if($pleaseSelect)
            <option value="">Please Select</option>
        @endif
    @endif
    @foreach($list as $k => $t)
        @php 
            $attr = in_array($k, $value_list) ? 'selected="selected"' : "";
        @endphp
        <option value="{{ $k }}" {!! $attr !!} >{{$t}}</option>
    @endforeach
</select>

@if($is_disabled)
   <input type="hidden" name="{{ $name }}" value="{{ implode(',', $value_list) }}" />
@endif

@error($errorName)
    <div class="input-error">{{ $message }}</div>
@enderror --}}


@props([
    'name',
    'label' => null,
    'list' => [],
    'value' => '',
    'mandatory' => false,
    'is_disabled' => false,
    'pleaseSelect' => false,
    'errorName' => null,
])

@php
    if ($is_disabled) {
        $mandatory = false;
    }

    $attr = '';
    if ($mandatory) {
        $attr .= ' required="true"';
    }
    if ($is_disabled) {
        $attr .= ' disabled="true"';
    }

    $errorName = $errorName ?: $name;

    $value_list = [];

    $name_without_square_braces = str_replace("[]", "", $name);

    $v = old($name_without_square_braces, $value);

    if (is_string($v) && strpos($v, ",") !== false) {
        $value_list = $v ? explode(",", $v) : [];
    } elseif (is_array($v)) {
        $value_list = $v;
    } else {
        $value_list[] = $v;
    }
@endphp

@if ($label)
    <label class="form-label">
        {{ $label }}
        @if ($mandatory)
            <span class="mandatory">*</span>
        @endif
    </label>
@endif

<select name="{{ $name }}" {{ $attributes->merge(['class' => 'form-control']) }} {!! $attr !!}>
    @if (!$attributes->has('multiple') && $pleaseSelect)
        <option value="">Please Select</option>
    @endif

    @foreach($list as $k => $t)
        @php 
            $selectedAttr = in_array($k, $value_list) ? 'selected="selected"' : '';
        @endphp
        <option value="{{ $k }}" {!! $selectedAttr !!}>{{ $t }}</option>
    @endforeach
</select>

@if ($is_disabled)
    <input type="hidden" name="{{ $name }}" value="{{ implode(',', $value_list) }}" />
@endif

@error($errorName)
    <div class="input-error">{{ $message }}</div>
@enderror
