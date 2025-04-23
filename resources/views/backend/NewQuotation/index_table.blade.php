<div class="card summary-card">

    <div class="card-header">
        <x-Backend.pagination-links :records="$records" />
    </div>

    <div class="card-body">
        <table class="table table-striped table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th><?= sortable_anchor('id', 'ID') ?></th>
                    <th>Customer Info</th>
                    <th>Satus</th>
                    <th>Comments</th>
                    <th style="width: 15%">Info</th>
                    <th style="width: 12%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($records as $record)
                <tr>
                    <td>{{ $record->id }}</td>
                    <td>
                        @if($record->is_new === 0)
                        Party : {{ $record->party->name ?? null}}
                        <br />
                        @else
                        Name : {{ $record->customer_name }}
                        <br />
                        Email : {{ $record->customer_email }}
                        <br />
                        @endif
                    </td>
                    <td>
                        @if($record->status == 'Pending')
                        Nothing to show
                        @elseif($record->status == 'follow_up')
                        Follow Up By: {{ $record->user->executive_name ?? 'N/A' }}
                        <br />
                        Follow Up Date: {{ $record->follow_up_date ?? 'N/A' }}
                        <br />
                        Follow Up Type: {{ $record->follow_up_type ?? 'N/A' }}
                        @elseif($record->status == 'Not Interested')
                        Not Interested Reason: {{ $record->not_in_interested_reason ?? 'N/A' }}
                        @elseif($record->status == 'Mature')
                        Action To Take: {{ $record->mature_action_type ?? 'N/A' }}
                        @else
                        {{ $record->status }}
                        @endif
                    </td>
                    <td>
                        {{ $record->comments }}
                    </td>
                    <td>
                        Email sent : {{$record->is_email_sent ? 'Yes' : 'No'}}
                        <x-Backend.index-table-info :record="$record" :userList="$userListCache" />
                    </td>
                    <td>
                        <x-Backend.summary-comman-actions :id="$record->id" :routePrefix="$routePrefix" />
                        <br />
                        <a href="{{ asset('files/pdfs/' . basename($record->pdf)) }}" download class="btn btn-secondary summary-action-button css-toggler mb-1">Pdf File</a>
                        <br /><br />

                        <span class="btn btn-info btn-sm css-toggler mb-1"
                            data-sr-css-class-toggle-target="#record-{{ $record->id }}" data-sr-css-class-toggle-class="hidden">
                            Details
                        </span>
                        <button class="btn btn-secondary summary-action-button" data-bs-toggle="modal"
                            data-bs-target="#sendMailModal"
                            data-id="{{ $record->id }}"
                            data-email="{{ $record->customer_email }}"
                            data-subject="Quotation for {{ $record->party->name ?? $record->customer_name }}"
                            data-content="Dear {{ $record->party->name ?? $record->customer_name }}, please find the attached quotation."
                            data-pdf="{{ asset('pdfs/' . basename($record->pdf)) }}"
                            data-attachments="{{ json_encode($record->quotationFiles->pluck('file')) }}">
                            Send Mail
                        </button>
                    </td>
                </tr>
                <tr id="record-{{ $record->id }}" class="hidden">
                    <td></td>
                    <td colspan="5">
                        <h4>Items</h4>
                        <table class="table table-striped table-bordered table-hover mb-0 sub-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th>Price Per Unit</th>
                                    <th>Qty</th>
                                    <th>Amount</th>
                                    <th>Extra</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->quotationItem as $k => $quotation_item)
                                <tr>
                                    <td><?= $k + 1 ?></td>
                                    <td>{{ $quotation_item->Item->name ?? Null}}</td>
                                    <td>{{ $quotation_item->price }}</td>
                                    <td>{{ $quotation_item->qty }}</td>
                                    <td>{{ $quotation_item->amount }}</td>
                                    <td>{{ $quotation_item->extra }}</td>
                                </tr>

                                @endforeach
                            </tbody>
                        </table>
                        @if(isset($record) && !empty($record->quotationFiles) && count($record->quotationFiles) > 0)
                        <h4 class="mt-3">Attachments</h4>
                        <table class="table table-striped table-bordered table-hover mb-0 sub-table">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($record->quotationFiles as $file)
                                <tr>
                                    <td>{{ basename($file->file) }}</td>
                                    <td>
                                        <a href="{{ Storage::url($file->file) }}" download class="btn btn-sm btn-primary">Download</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <!-- Email Modal -->
    <div class="modal fade" id="sendMailModal" tabindex="-1" aria-labelledby="sendMailModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendMailModalLabel">Send Quotation Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="sendMailForm">
                        @csrf
                        <input type="hidden" name="record_id" id="record_id">

                        <div class="mb-3">
                            <label for="to_email" class="form-label">To Email</label>
                            <input type="email" class="form-control" id="to_email" name="to_email" required>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content</label>
                            <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Attached Files</label>
                            <ul id="attachments_list" class="no-bullet"></ul>
                            <ul id="pdf_list" class="no-bullet"></ul>
                        </div>

                        <button type="submit" id="sendMailButton" class="btn btn-primary">Send Email</button>
                    </form>

                </div>
            </div>
        </div>
    </div>
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
        <div id="emailToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Email sent successfully!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <x-Backend.pagination-links :records="$records" />
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let sendMailModal = document.getElementById("sendMailModal");

        sendMailModal.addEventListener("show.bs.modal", function(event) {
            let button = event.relatedTarget;

            document.getElementById("record_id").value = button.getAttribute("data-id");
            document.getElementById("to_email").value = button.getAttribute("data-email");
            document.getElementById("subject").value = button.getAttribute("data-subject");
            document.getElementById("content").value = button.getAttribute("data-content");

            let attachmentsList = document.getElementById("attachments_list");
            let pdfsList = document.getElementById("pdf_list");
            attachmentsList.innerHTML = "";
            pdfsList.innerHTML = "";

            let pdf = button.getAttribute("data-pdf");
            if (pdf) {
                let pdfName = pdf.split('/').pop();
                pdfsList.innerHTML += `<li><a href="${pdf}" target="_blank" >${pdfName}</a></li>`;
            }
            // console.log(pdf);


            let attachments = JSON.parse(button.getAttribute("data-attachments") || "[]");
            // console.log(attachments);


            attachments.forEach(file => {
                attachmentsList.innerHTML += `<li><a href="${file}" target="_blank">${file.split('/').pop()}</a></li>`;
            });
        });

        document.getElementById("sendMailForm").addEventListener("submit", function(event) {
            event.preventDefault();
            let form = document.getElementById("sendMailForm");

            if (form) {
                let submitButton = document.querySelector("#sendMailButton");
                submitButton.disabled = true;
                submitButton.textContent = "Sending...";

                let formData = new FormData(this);

                let attachments = [];
                document.querySelectorAll("#attachments_list li a").forEach(file => {
                    let filePath = file.getAttribute("href");
                    if (filePath) {
                        attachments.push(filePath);
                    }
                });
                let pdfElement = document.querySelector("#pdf_list li a");
                let pdfattachments = pdfElement ? pdfElement.getAttribute("href") : null;

                formData.append("attachments", JSON.stringify(attachments));
                formData.append("pdf_attachment", pdfattachments);

                fetch("{{ route('send.email') }}", {
                        method: "POST",
                        body: formData,
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").getAttribute("content"),
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Email Sent!',
                            text: 'Your email has been sent successfully!',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        form.reset();
                        bootstrap.Modal.getInstance(document.getElementById('sendMailModal')).hide();
                    })
                    .catch(error => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed!',
                            text: "Failed to send email: " + error.message,
                        });
                    })
                    .finally(() => {
                        submitButton.disabled = false;
                        submitButton.textContent = "Send Email";
                    });
            } else {
                console.error("Form #sendMailForm not found in HTML!");
            }
        });




    });
</script>