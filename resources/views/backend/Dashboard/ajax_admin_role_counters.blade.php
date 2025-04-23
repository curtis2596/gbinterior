<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-3 mb-3">
        <div class="card counter">
            <h5 class="card-title blue">Leads</h5>
            <div class="card-body">
                <ol>
                    @foreach($lead_counters as $lead_status => $lead_counter)
                    <li>{{ $lead_status }} : {{$lead_counter}}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-3 mb-3">
        <div class="card counter">
            <h5 class="card-title purple">Quotations</h5>
            <div class="card-body">
                <ol>
                    @foreach($quotation_counters as $quotation_status => $quotation_counter)
                    <li>{{ $quotation_status }} : {{$quotation_counter}}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-3 mb-3">
        <div class="card counter">
            <h5 class="card-title magenta">Purchase</h5>
            <div class="card-body">
                <ol>
                    @foreach($purchase_counters as $key => $value)
                    <li>{{ $key }} : {{$value}}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-3 mb-3">
        <div class="card counter">
            <h5 class="card-title yellow">Sale</h5>
            <div class="card-body">
                <ol>
                    @foreach($sale_counters as $key => $value)
                    <li>{{ $key }} : {{$value}}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
    <div class="col-xs-12 col-sm-12 col-md-4 col-lg-3 mb-3">
        <div class="card counter">
            <h5 class="card-title blue">Complaints</h5>
            <div class="card-body">
                <ol>
                    @foreach($complaint_counters as $key => $value)
                    <li>{{ $key }} : {{$value}}</li>
                    @endforeach
                </ol>
            </div>
        </div>
    </div>
</div>

<hr>
<div class="row">
    <div class="col-xs-12 col-md-12 col-md-6 col-lg-6">
        <h4>Complete Job Orders (Top 20)</h4>
        <table class="table table-striped table-bordered table-hover mb-0 dashboard-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Job Worker</th>
                    <th>Receive Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($job_order_complete as $k => $job_order)
                <tr>
                    <td>{{ $k + 1}}</td>
                    <td>{{ $job_order->party->name }}</td>
                    <td>{{ if_date($job_order->jobOrderReceive->receive_date) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="col-xs-12 col-md-12 col-md-12 col-lg-6">
        <h4>Pending Job Orders (Top 20)</h4>
        <table class="table table-striped table-bordered table-hover mb-0 dashboard-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Job Worker</th>
                    <th>Expected Receive Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($job_order_pending as $k => $job_order)
                <tr>
                    <td>{{ $k + 1}}</td>
                    <td>{{ $job_order->party->name }}</td>
                    <td>
                        {{ if_date($job_order->expected_complete_date) }}

                        @if($job_order->is_expired)
                            <span class="badge rounded-pill bg-danger text-white">Expired</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>