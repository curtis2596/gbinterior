<table class="table table-borderless" style="min-width: 50rem; margin: 3rem;">
    <tbody>
        <tr>
            <td class="pb-0" colspan="2"><strong></strong> For: {{ $company->name }} </td>
            <td class="pb-0"><strong></strong>Authorized</td>
        </tr>
    </tbody>
</table>
<footer class="border-top border-dark foot-screen">
    <table class="table table-borderless">
        <tbody>
            <tr>
                <td><strong>Email:</strong> {{ $company->email }}</td>
                <td><strong>Website:</strong> {{ $company->website }}</td>
                <td><strong>Mobile No:</strong> {{ $company->phone_number }}</td>
            </tr>
        </tbody>
    </table>
</footer>