@extends($layout)

@section('content')

<?php
    $page_header_links = [

    ];
?>

@include($partial_path . ".page_header")

<div class="card">
    <div class="card-body">
        <table class="table table-striped table-bordered table-hover i-data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th data-search="like">URL</th>
                    <th data-search="like" data-sort="text">Route Name</th>
                    <th data-sort="text">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $i = 0;
                foreach($routes as $route):
                    $i++;
                ?>
                <tr>
                    <td><?= $i ?></td>
                    <td><?= $route['url'] ?></td>
                    <td><?= $route['route_name'] ?></td>
                    <td><?= $route['action'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer">

    </div>
</div>

@endsection