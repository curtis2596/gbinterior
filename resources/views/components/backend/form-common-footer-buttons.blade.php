<div class="form-buttons">
    @if(in_array("submit", $buttonList))
        <button type="submit" class="btn btn-primary">Submit</button>
    @endif

    @if(in_array("submit_and_redirect_to_summary", $buttonList))
        <button type="submit" class="btn btn-primary" name="redirect_to_index" value="1">Submit and Go To Summary</button>
    @endif

    @if(in_array("reset", $buttonList))
        <button type="reset" class="btn btn-secondary">Reset</button>
    @endif
</div>