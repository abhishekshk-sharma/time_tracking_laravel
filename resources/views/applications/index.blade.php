@extends('layouts.user')

@section('page-title', 'Employee Applications')
@section('page-subtitle', 'Submit and manage your leave requests, complaints, and other applications.')

@push('page-styles')
<style>
    /* Page-specific styles for applications.php */
    .application-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    @media (max-width: 768px) {
        .application-cards {
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            max-height: 75vh;
            overflow-y: auto;
            padding-right: 10px;
        }
        
        .application-cards::-webkit-scrollbar {
            /* width: 6px; */
            display: none;
        }
        
        /* .application-cards::-webkit-scrollbar-track {
            background: #04305b;
            border-radius: 3px;
        }
        
        .application-cards::-webkit-scrollbar-thumb {
            background: #82043f;
            border-radius: 3px;
        }
        
        .application-cards::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        } */
    }
    
    .app-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    
    .app-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        border-color: var(--secondary);
    }
    
    .app-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 24px;
    }
    
    .leave-icon {
        background-color: #e1f0ff;
        color: var(--secondary);
    }
    
    .sick-icon {
        background-color: #ffece8;
        color: var(--danger);
    }
    
    .complaint-icon {
        background-color: #fff4e6;
        color: var(--warning);
    }
    .regularization-icon {
        background-color: #d3eefdff;
        color: var(--success);
    }
    
    .other-icon {
        background-color: #e6f7ee;
        color: var(--success);
    }
    
    .app-card h3 {
        margin-bottom: 10px;
        color: var(--dark);
    }
    
    .app-card p {
        color: var(--gray);
        font-size: 14px;
    }
    
    .application-form {
        display: none;
        background: #fffbfbff;
        border-radius: 10px;
        padding: 25px;
        margin-top: 20px;
    }
    
    .form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    
    .back-button {
        background: var(--gray);
        color: white;
        border: none;
        border-radius: 5px;
        padding: 8px 15px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }
    
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-top: 20px;
    }
    
    .applications-history {
        margin-top: 40px;
    }
    
    .history-filters {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    
    .tableflow {
        width: 100%;
        overflow-x: auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #e5e7eb;
    }
    
    .tableflow::-webkit-scrollbar {
        height: 8px;
    }
    
    .tableflow::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    
    .tableflow::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    
    .tableflow::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    .tableflow table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .tableflow table th {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        color: #374151;
        font-weight: 600;
        padding: 1rem;
        text-align: left;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e5e7eb;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .tableflow table td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
        font-size: 0.875rem;
        vertical-align: middle;
    }
    
    .tableflow table tbody tr {
        transition: all 0.2s ease;
    }
    
    .tableflow table tbody tr:hover {
        background: linear-gradient(135deg, #f8fafc, #f1f5f9);
        transform: translateX(2px);
    }
    
    .tableflow table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .tableflow .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.375rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    .tableflow .status-pending {
        background: #fef3c7;
        color: #d97706;
        border: 1px solid #fbbf24;
    }
    
    .tableflow .status-approved {
        background: #dcfce7;
        color: #166534;
        border: 1px solid #22c55e;
    }
    
    .tableflow .status-rejected {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #ef4444;
    }
    
    .tableflow .btn {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }
    
    .tableflow .btn-primary {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
    }
    
    .tableflow .btn-primary:hover {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
    }
    
    .notification {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        display: none;
    }
    
    .notification.success {
        background-color: #e7f6e9;
        color: var(--success);
        border: 1px solid #c8e6c9;
    }
    
    .notification.error {
        background-color: #ffebee;
        color: var(--danger);
        border: 1px solid #ffcdd2;
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        border-radius: 10px;
        width: 600px;
        max-width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        position: relative;
    }
    
    .application-details {
        margin-bottom: 20px;
    }
    
    .detail-item {
        display: flex;
        margin-bottom: 15px;
    }
    
    .detail-label {
        width: 120px;
        font-weight: 600;
        color: var(--dark);
    }
    
    .detail-value {
        flex: 1;
    }
    
    .attachment-link {
        color: var(--secondary);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .attachment-link:hover {
        text-decoration: underline;
    }
    
    #imageModal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 20000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .image-modal-content {
        background: white;
        border-radius: 10px;
        padding: 20px;
        max-width: 90%;
        max-height: 90%;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }
    
    .image-modal-content img {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
        border-radius: 8px;
        margin-bottom: 15px;
    }
    
    .image-modal-content .btn {
        background: var(--secondary);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.3s ease;
    }
    
    .image-modal-content .btn:hover {
        background: var(--primary);
    }
    
    .notification-content-head-div {
        margin-top: 20px;
    }
    
    @media(max-width: 1378px) {
        #imageModal {
            height: 46vw;
        }
    }
    
    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .history-filters {
            flex-direction: column;
        }
        
        .tableflow {
            width: 100%;
            margin-left: 0;
            overflow-x: auto;
            border-radius: 8px;
        }
        
        .tableflow table {
            min-width: 600px;
        }
        
        .tableflow table th,
        .tableflow table td {
            padding: 0.75rem 0.5rem;
            font-size: 0.8rem;
        }
        
        .tableflow table th {
            white-space: nowrap;
        }
        
        .tableflow .btn {
            padding: 0.375rem 0.75rem;
            font-size: 0.7rem;
        }
        
        .modal-content {
            left: 5%;
            width: 90%;
        }
        
        #imageModal {
            top: 3%;
            height: 95vh;
            width: 95vw;
        }
        
        .today-summary {
            grid-template-columns: 1fr;
            gap: 10px;
        }
    }
</style>
@endpush

@section('page-content')

<div class="application-cards">
                    <div class="app-card" data-type="casual_leave">
                        <div class="app-icon leave-icon">
                            <i class="fas fa-umbrella-beach"></i>
                        </div>
                        <h3>Casual Leave</h3>
                        <p>Request for personal time off</p>
                    </div>
                    
                    <div class="app-card" data-type="sick_leave">
                        <div class="app-icon sick-icon">
                            <i class="fas fa-heartbeat"></i>
                        </div>
                        <h3>Sick Leave</h3>
                        <p>Request for medical leave</p>
                    </div>
                    
                    <div class="app-card" data-type="half_leave">
                        <div class="app-icon leave-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3>Half Day Leave</h3>
                        <p>Request for half day off</p>
                    </div>
                    
                    <div class="app-card" data-type="complaint">
                        <div class="app-icon complaint-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3>Report Complaint</h3>
                        <p>Submit a workplace complaint</p>
                    </div>
                    
                    <div class="app-card" data-type="other">
                        <div class="app-icon other-icon">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h3>Other Request</h3>
                        <p>Submit other types of requests</p>
                    </div>
                    <div class="app-card" data-type="regularization">
                        <div class="app-icon regularization-icon">
                            <i class="fas  fa-suitcase"></i>
                        </div>
                        <h3>RegulariZation</h3>
                        <p>Submit RegulariZation</p>
                    </div>
                    <div class="app-card" data-type="punch_Out_regularization">
                        <div class="app-icon leave-icon">
                            <i class="fas  fa-person-circle-question"></i>
                        </div>
                        <h3>Missing Punch Out Request</h3>
                        <p>Submit Missing Punch Out Request</p>
                    </div>
                    <div class="app-card" data-type="work_from_home">
                        <div class="app-icon other-icon ">
                            <i class="fas  fa-house-user"></i>
                        </div>
                        <h3>Work From Home Request</h3>
                        <p>Submit Work From Home Request</p>
                    </div>
                </div>
                
                <div id="applicationForm" class="application-form">
                    <div class="form-header">
                        <h3 id="formTitle">Application Form</h3>
                        <button class="back-button" id="backButton">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                    </div>
                    
                    <form id="requestForm" enctype="multipart/form-data">
                        @csrf
                        <div class="form-row">
                            <div class="form-group">
                                <label for="requestType">Request Type</label>
                                <select id="requestType" name="requestType" required>
                                    <option value="">Select Request Type</option>
                                    <option value="casual_leave">Casual Leave</option>
                                    <option value="sick_leave">Sick Leave</option>
                                    <option value="half_leave">Half Day Leave</option>
                                    <option value="complaint">Complaint</option>
                                    <option value="regularization">Regularization</option>
                                    <option value="punch_Out_regularization">Missing Punch Out Request</option>
                                    <option value="work_from_home">Work From Home Request</option>
                                    <option value="other">Other Request</option>
                                </select>
                            </div>
                            
                            <div class="form-group" id="halfDayGroup" style="display: none;">
                                <label for="halfDayType">Half Day Type</label>
                                <select id="halfDayType" name="halfDayType">
                                    <option value="first_half">First Half</option>
                                    <option value="second_half">Second Half</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="startDate">Start Date</label>
                                <input type="date" id="startDate" name="startDate" required>
                            </div>
                            
                            <div class="form-group" id="endDateGroup">
                                <label for="endDate" id="request_end_day">End Date</label>
                                <input type="date" id="endDate" name="endDate" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="Enter subject of your request" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" placeholder="Please provide details of your request" required></textarea>
                        </div>
                        
                        <div class="form-group" id="attachmentGroup">
                            <label for="attachment">Attachment (if any) <p style="color:red;">Limit is 2MB (JPEG & PNG)</p></label>
                            <input type="file" id="attachment" name="image" accept="image/jpeg,image/jpg,image/png,image/gif">
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" id="cancelBtn">
                                Cancel
                            </button>
                            <button type="button" class="btn btn-primary" id="submit">
                                <i class="fas fa-paper-plane"></i> Submit Request
                            </button>
                        </div>
                    </form>
                </div>

                <div class="today-summary">
                        <div class="summary-card">
                            <h3>Total Sick Leave </h3>
                            <p id="rsl" value="" style='display: inline;'></p> &nbsp;/&nbsp;
                            <p id="tsl" value="" style='display: inline;'></p>
                        </div>
                        {{-- <div class="summary-card">
                            <h3>Remaining Sick Leave</h3>
                        </div> --}}
                        <div class="summary-card">
                            <h3>Total Casual Leave</h3>
                            <p id="tcl" value="" style='display: inline;'></p> &nbsp;/&nbsp;
                            <p id="rcl" value="" style='display: inline;'></p>
                        </div>
                        {{-- <div class="summary-card">
                            <h3>Remaining Casual Leave</h3>
                        </div> --}}
                       
                    </div>
                
                <div class="applications-history">
                    <h3 class="section-title">
                        <i class="fas fa-history"></i>
                        Application History
                    </h3>
                    
                    <div class="history-filters">
                        <div class="filter-group">
                            <label for="statusFilter">Status</label>
                            <select id="statusFilter">
                                <option value="all">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="typeFilter">Type</label>
                            <select id="typeFilter">
                                <option value="all">All Types</option>
                                <option value="casual_leave">Casual Leave</option>
                                <option value="sick_leave">Sick Leave</option>
                                <option value="half_leave">Half Day Leave</option>
                                <option value="complaint">Complaint</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label for="dateFilter">Date Range</label>
                            <select id="dateFilter">
                                <option value="all">All Time</option>
                                <option value="month">This Month</option>
                                <option value="quarter">This Quarter</option>
                                <option value="year">This Year</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <button type="button" class="btn btn-primary" id="checkfilter">
                                <i class="fas fa-paper-plane"></i> check
                            </button>
                        </div>
                        
                    </div>
                    
                    
                    <div class="tableflow">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Subject</th>
                                    <th width="25%">Period</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="Tbody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection

    <!-- Application Detail Modal -->
    <div class="modal" id="applicationModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Application Details</h3>
                <button class="close-btn">&times;</button>
            </div>
            
            <div class="application-details">
                <div class="detail-item">
                    <div class="detail-label">Application ID:</div>
                    <div class="detail-value" id="detail-id">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Employee:</div>
                    <div class="detail-value" id="detail-employee">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Request Type:</div>
                    <div class="detail-value" id="detail-type">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Subject:</div>
                    <div class="detail-value" id="detail-subject">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Date Range:</div>
                    <div class="detail-value" id="detail-dates">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Half Day:</div>
                    <div class="detail-value" id="detail-halfday">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Description:</div>
                    <div class="detail-value" id="detail-description">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Attachment:</div>
                    <div class="detail-value" id="detail-attachment" value="">
                        <i class="fas fa-paperclip"></i> 
                        <a href="#" class="attachment-link">
                        </a>
                    </div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Applied On:</div>
                    <div class="detail-value" id="detail-applied">Loading....</div>
                </div>
                
                <div class="detail-item">
                    <div class="detail-label">Status:</div>
                    <div class="detail-value">
                        <span class="status-badge status-pending" id="detail-status">Loading....</span>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <button class="btn btn-secondary" id="modalcancelBtn">
                    <i class="fas fa-times"></i> Close
                </button>
                
            </div>
        </div>
    </div>

    <div id="imageModal">
        <div class="image-modal-content">
            <img id="modalImage" src="" alt="Attachment">
            <button class="btn" onclick="$(this).closest('#imageModal').css('display', 'none')">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>


@push('page-scripts')
<script src="{{ asset('js/jQuery.min.js') }}"></script>
<script src="{{ asset('js/sweetAlert.js') }}"></script>
<script>
        document.addEventListener('DOMContentLoaded', function() {

            document.documentElement.lang = "en-GB";

            
            $(document).on("click", ".view_request", function(){
            let info = "req_modal";
            let id = $(this).data("id");
            let type = $(this).data("type");
            let time = $(this).data("time");

            $.ajax({
                url: "{{ route('api.requests.modal') }}",
                method: "POST",
                dataType: 'json', 
                data: {info:info, id:id, type:type, time:time, _token: '{{ csrf_token() }}'},
                success: function(e){
                    $("#detail-id").html(e.id);
                    $("#detail-employee").html(e.name);
                    $("#detail-type").html(e.req_type);
                    $("#detail-subject").html(e.subject);
                    $("#detail-dates").html(e.startdate+" - "+e.enddate);
                    $("#detail-halfday").html(e.halfday);
                    $("#detail-description").html(e.description);
                    
                    // Handle attachment properly
                    if(e.file && e.file !== "-" && e.file !== "") {
                        $(".attachment-link").html("View Attachment");
                        $(".attachment-link").attr("href", "{{ url('/') }}/" + e.file);
                        $(".attachment-link").data("img-path", "{{ url('/') }}/" + e.file);
                        $("#detail-attachment").show();
                    } else {
                        $(".attachment-link").html("No attachment");
                        $(".attachment-link").removeAttr("href");
                        $(".attachment-link").removeData("img-path");
                    }
                    
                    $("#detail-applied").html(e.createdat);
                    $("#detail-status").html(e.status);
                    if(e.status == "rejected"){
                        $("#detail-status").css("color", "red");
                        $(".status-pending").css({
                            "background-color":"#fabdb9",
                            "opacity": 0.6
                        });
                                                
                    }
                    if(e.status == "approved"){
                        $("#detail-status").css("color", "green");
          
                        $(".status-pending").css({
                            "background-color":"#b7f7a8",
                            "opacity": 0.6
                        });
                        
                    }
                    
                    
                    if(e.req_type === "punch_Out_regularization"){
                        $(".formissingpunchout_time").html("Requested Punch Out Time:")
                    }
                    else{
                        $(".formissingpunchout_time").html("Date Range:")
                    }
                    
                    $("#applicationModal").css('display', 'flex');
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });
                    
                    Swal.fire({
                        title: "Error Loading Details",
                        text: "Could not load application details. Status: " + xhr.status,
                        icon: "error"
                    });
                }
            });
        });


        $(".close-btn").click(function(){
            $("#applicationModal").css('display', 'none');
            $(".status-pending").css({
                            "background-color":"#fff4e6",
                            "opacity": 0.6
                        });
        });
        $("#modalcancelBtn").click(function(){
            $("#applicationModal").css('display', 'none');
            $(".status-pending").css({
                            "background-color":"#fff4e6",
                            "opacity": 0.6
                        });
        });


        $(".attachment-link").on("click", function(e) {
            e.preventDefault();
            var imgPath = $(this).data("img-path");
            if(imgPath) {
                $("#modalImage").attr("src", imgPath);
                $("#imageModal").css('display', 'flex');
            }
        });



            // Set today's date as default start date
            document.getElementById('startDate').valueAsDate = new Date();
            
            // Application card click handler
            const appCards = document.querySelectorAll('.app-card');
            const applicationForm = document.getElementById('applicationForm');
            const formTitle = document.getElementById('formTitle');
            const requestType = document.getElementById('requestType');
            const notification = document.getElementById('notification');
            const notificationText = document.getElementById('notification-text');
            
            appCards.forEach(card => {
                card.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    applicationForm.style.display = 'block';
                    
                    // Set the form title based on application type
                    const titles = {
                        'casual_leave': 'Casual Leave Application',
                        'sick_leave': 'Sick Leave Application',
                        'half_leave': 'Half Day Leave Application',
                        'complaint': 'Report a Complaint',
                        'regularization': 'Apply For RegulariZation',
                        'punch_Out_regularization': 'Request for Missing Punch Out',
                        'work_from_home': 'Work From Home Request',
                        'other': 'Other Request'
                    };
                    
                    formTitle.textContent = titles[type];
                    requestType.value = type;
                    
                    // Show/hide half day options
                    toggleHalfDayOptions(type);
                    
                    // Show/hide end date for leave types
                    toggleEndDateField(type);
                    
                    // Scroll to form
                    applicationForm.scrollIntoView({ behavior: 'smooth' });
                });
            });
            
            // Back button handler
            document.getElementById('backButton').addEventListener('click', function() {
                applicationForm.style.display = 'none';
            });
            
            // Cancel button handler
            document.getElementById('cancelBtn').addEventListener('click', function() {
                applicationForm.style.display = 'none';
                document.getElementById('requestForm').reset();
            });
            
            // Request type change handler
            requestType.addEventListener('change', function() {
                const type = this.value;
                toggleHalfDayOptions(type);
                toggleEndDateField(type);
            });
            
            // Toggle half day options based on request type
            function toggleHalfDayOptions(type) {
                const halfDayGroup = document.getElementById('halfDayGroup');
                if (type === 'half_leave') {
                    halfDayGroup.style.display = 'block';
                } else {
                    halfDayGroup.style.display = 'none';
                }
            }
            
            // Toggle end date field based on request type
            function toggleEndDateField(type) {
                const endDateGroup = document.getElementById('endDateGroup');
                if (type === 'casual_leave' || type === 'sick_leave' || type === 'regularization' || type === 'punch_Out_regularization' || type === 'work_from_home') {

                    endDateGroup.style.display = 'block';
                    document.getElementById('endDate').setAttribute('required', 'required'); 
                    if(type == "punch_Out_regularization"){
                        document.getElementById('endDate').setAttribute('type', 'datetime-local'); 
                        
                        document.getElementById('request_end_day').innerHTML = "Punch Out Time";

                    }
                    else{

                        document.getElementById('endDate').setAttribute('type', 'date'); 
                        document.getElementById('request_end_day').innerHTML = "End Date";
                    }
                } else {
                    endDateGroup.style.display = 'none';
                    document.getElementById('endDate').removeAttribute('required');
                }
            }
            
           
            function showNotification(message, type) {
                notificationText.textContent = message;
                notification.className = 'notification ' + type;
                notification.style.display = 'flex';
                
                // Hide notification after 5 seconds
                setTimeout(hideNotification, 5000);
            }
            
            // Hide notification
            function hideNotification() {
                notification.style.display = 'none';
            }
        });
    </script>

    <script>
        
        $(document).ready(function(){
            
            checkrequests();
            leaveDayCheck();

            function leaveDayCheck(){
                info = "leaveDayCheck";
                $.ajax({
                    url: "{{ route('api.requests.leave-check') }}",
                    method: "POST",
                    dataType: "json",
                    data: {info: info, _token: '{{ csrf_token() }}'},
                    success: function(e){
                        if(e){
                            $("#tsl").html(e.tsl);
                            $("#tcl").html(e.tcl);
                            $("#rsl").html(e.rsl);
                            $("#rcl").html(e.rcl);
                            
                            $("#rsl").val(e.valrsl);
                            $("#rcl").val(e.valrcl);
                        }
                    },
                    error(response, status, error){
                        console.log("error: "+error);
                        console.log("response: "+response);
                        console.log("status: "+status);
                    }
                });
            }
            
            function checkrequests(){

                let info = "checkReq";
                let id = '{{ auth()->user()->emp_id ?? "" }}';

                $.ajax({
                    url: "{{ route('api.requests.check') }}",
                    method: "POST",
                    data: {info:info, id:id, _token: '{{ csrf_token() }}'},
                    success: function(e){
                        $("#Tbody").html(e);
                    }
                });
            }

            let reqType = "nothing";
            $(document).on("click", "#submit", function(e) {
                reqType = $("#requestType").val();
                let type = reqType;
                let start_date = $("#startDate").val();
                let end_date = $("#endDate").val();
                let subject = $("#subject").val();
                let description = $("#description").val();
                let formdata = new FormData();
                let image = $("#attachment")[0].files[0];
                let id = '{{ auth()->user()->emp_id ?? "" }}';
                let half_day_type = $("#halfDayType").val();

                // Validation
                if (!type || !start_date || !subject || !description) {
                    Swal.fire({
                        text: "Please fill all required fields!",
                        icon: "error"
                    });
                    return;
                }

                // Date validation for types that require end date
                if (['casual_leave', 'sick_leave', 'regularization', 'work_from_home'].includes(type)) {
                    if (!end_date) {
                        Swal.fire({
                            text: "End date is required for this request type!",
                            icon: "error"
                        });
                        return;
                    }
                    
                    let start_date1 = new Date(start_date);
                    let end_date1 = new Date(end_date);
                    if (start_date1 > end_date1) {
                        Swal.fire({
                            text: "End Date can't be less than Start Date!",
                            icon: "error"
                        });
                        return;
                    }
                }

                // Check leave balance for leave types
                if (type === 'casual_leave') {
                    let rcl = $("#rcl").val();
                    formdata.append('rcl', rcl);
                    formdata.append('info', 'casualLeave');
                } else if (type === 'sick_leave') {
                    let rsl = $("#rsl").val();
                    formdata.append('rsl', rsl);
                    formdata.append('info', 'sick_leave');
                } else if (type === 'half_leave') {
                    formdata.append('info', 'half_day');
                    formdata.append('half_day_type', half_day_type);
                } else if (type === 'complaint') {
                    formdata.append('info', 'complaint');
                } else if (type === 'regularization') {
                    formdata.append('info', 'regularization');
                } else if (type === 'punch_Out_regularization') {
                    formdata.append('info', 'punch_Out_regularization');
                } else if (type === 'work_from_home') {
                    formdata.append('info', 'work_from_home');
                } else {
                    formdata.append('info', 'other');
                }

                formdata.append('id', id);
                formdata.append('type', type);
                formdata.append('start_date', start_date);
                formdata.append('end_date', end_date);
                formdata.append('subject', subject);
                formdata.append('description', description);
                formdata.append('_token', '{{ csrf_token() }}');
                
                if (image) {
                    formdata.append('image', image);
                }

                submitRequest(formdata);
            });

            function submitRequest(formdata) {
                checksession();

                $.ajax({
                    url: "{{ route('api.requests.store') }}",
                    method: "POST",
                    data: formdata,
                    contentType: false,
                    processData: false,   
                    success: function(e) {
                        if (e == 100) {
                            Swal.fire({
                                text: "Request Submitted Successfully!",
                                icon: "success"
                            });

                            $("#cancelBtn").trigger("click");
                            checkrequests();
                            leaveDayCheck();
                        } else {
                            Swal.fire({
                                text: "Error! " + e,
                                icon: "error"
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            title: "Oops!",
                            text: "Something went wrong. Please try again later.\nError: " + error,
                            icon: "error",
                            confirmButtonText: "OK"
                        });
                    }
                });
            }

            checksession();
            function checksession(){
                var info = "check";
                $.ajax({
                    url: "{{ route('api.session.check') }}",
                    method: "POST",
                    data: {info:info, _token: '{{ csrf_token() }}'},
                    success: function(e){
                        if(e == "expired"){
                            window.location.href = "{{ route('login') }}";
                        }
                    }
                });
            }

            $(document).on("click", "#checkfilter", function(){
                let click = "filterReq";
                let status = $("#statusFilter").val();
                let type = $("#typeFilter").val();
                let limit = $("#dateFilter").val();

                $.ajax({
                    url: "{{ route('api.filter.requests') }}",
                    method: "POST",
                    data: {click:click, status:status, type:type, limit:limit, _token: '{{ csrf_token() }}'},
                    success: function(e){
                        if(e){
                            $("#Tbody").html(e);
                        }
                    }
                });
            });

        });
    </script>
@endpush