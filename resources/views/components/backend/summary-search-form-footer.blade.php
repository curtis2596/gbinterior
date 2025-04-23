<div class="row">
    <div class="col-sm-4">
        Limit : 
        <select name="pagination_limit" class="pagination_limit">            
            @foreach($paginationList as $k => $t)
                @php
                    $attr = $selectedPaginationLimit == $k ? 'selected="selected"' : "";
                @endphp
                <option value="{{ $k }}" {{ $attr }}>{{$t}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-sm-6">
        <div>
            <button type="submit" class="btn btn-primary">Search</button>
            <span class="btn btn-light text-dark border-primary clear_form_search_conditions">Clear</span>
        </div>
    </div>
</div>