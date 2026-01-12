@extends('layouts.user')

@section('page-title', 'My Profile')
@section('page-subtitle', 'Manage your personal information and account settings.')

@push('page-styles')
<style>
    .user-avatar {
        width: 80px;
        height: 80px;
        margin: 0 auto 1rem;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
        box-shadow: var(--shadow-lg);
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .status-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 0.75rem;
        padding: 0.5rem 1rem;
        background: rgba(16, 185, 129, 0.1);
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: var(--success);
        animation: blink 2s ease-in-out infinite;
    }
    
    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }
    
    .status-text {
        color: var(--success);
    }
    
    .nav-indicator {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 0;
        background: linear-gradient(180deg, var(--primary), var(--secondary));
        border-radius: 3px 0 0 3px;
        transition: var(--transition);
    }
    
    .nav-link.active .nav-indicator {
        height: 70%;
    }
    
    .logout-btn {
        color: var(--danger) !important;
    }
    
    .logout-btn:hover {
        background: rgba(239, 68, 68, 0.1) !important;
    }
    
    .profile-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--radius-xl);
        padding: 2rem;
        box-shadow: var(--shadow-xl);
        animation: fadeIn 0.6s ease-out;
    }
    
    .profile-header {
        display: flex;
        align-items: center;
        gap: 2rem;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--gray-200);
        position: relative;
    }
    
    .profile-header::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        width: 100px;
        height: 2px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        border-radius: 1px;
    }
    
    .profile-image {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
    }
    
    .profile-image::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        animation: shimmer 3s ease-in-out infinite;
    }
    
    @keyframes shimmer {
        0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
        100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
    }
    
    .profile-info h2 {
        background: linear-gradient(135deg, var(--gray-900), var(--gray-700));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-size: 1.875rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        letter-spacing: -0.025em;
    }
    
    .profile-info p {
        color: var(--gray-500);
        font-size: 1rem;
        font-weight: 500;
        margin: 0.25rem 0;
    }
    
    .profile-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .detail-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.8), rgba(255, 255, 255, 0.6));
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--radius-xl);
        padding: 1.5rem;
        position: relative;
        transition: var(--transition);
        overflow: hidden;
    }
    
    .detail-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
    }
    
    .detail-card.editable {
        cursor: pointer;
    }
    
    .detail-card.editable:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
        border-color: rgba(79, 70, 229, 0.3);
    }
    
    .detail-card h3 {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 1rem;
        color: var(--primary);
        font-size: 1rem;
        font-weight: 600;
    }
    
    .detail-card h3 i {
        font-size: 1.25rem;
    }
    
    .detail-card p {
        color: var(--gray-900);
        font-size: 1.125rem;
        font-weight: 600;
        margin: 0;
    }
    
    .edit-icon {
        position: absolute;
        top: 1rem;
        right: 1rem;
        color: var(--gray-400);
        font-size: 1rem;
        transition: var(--transition);
    }
    
    .detail-card.editable:hover .edit-icon {
        color: var(--primary);
        transform: scale(1.1);
    }
    
    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: var(--radius-xl);
        width: 90%;
        max-width: 500px;
        padding: 2rem;
        box-shadow: var(--shadow-xl);
        animation: modalSlideIn 0.3s ease-out;
    }
    
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--gray-200);
    }
    
    .modal-header h2 {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        font-weight: 700;
        margin: 0;
    }
    
    .close-btn {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: var(--gray-500);
        transition: var(--transition);
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .close-btn:hover {
        background: var(--gray-100);
        color: var(--gray-700);
        transform: rotate(90deg);
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--gray-700);
        font-weight: 600;
        font-size: 0.875rem;
    }
    
    .form-group input {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 1px solid var(--gray-300);
        border-radius: var(--radius-md);
        font-size: 0.875rem;
        font-family: inherit;
        transition: var(--transition);
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(5px);
    }
    
    .form-group input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        background: rgba(255, 255, 255, 0.95);
    }
    
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--gray-200);
    }
    
    .btn-cancel {
        background: linear-gradient(135deg, var(--gray-100), var(--gray-200));
        color: var(--gray-700);
        border: 1px solid var(--gray-300);
    }
    
    .btn-cancel:hover {
        background: linear-gradient(135deg, var(--gray-200), var(--gray-300));
        color: var(--gray-800);
    }
    
    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .profile-image {
            width: 100px;
            height: 100px;
            font-size: 2.5rem;
        }
        
        .profile-details {
            grid-template-columns: 1fr;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .modal-content {
            width: 95%;
            padding: 1.5rem;
        }
    }
</style>
@endpush

@section('page-content')
<div class="profile-container">
            <div class="profile-header">
                <div class="profile-image">
                    <i class="fas fa-user"></i>
                </div>
                <div class="profile-info">
                    <h2>{{ Auth::user()->full_name }}</h2>
                    <p>{{ Auth::user()->position }}</p>
                    <p>{{ Auth::user()->emp_id }}</p>
                    <p><strong>Branch:</strong> {{$ff}}</p>
                </div>
            </div>

            <div class="profile-details">
                <div class="detail-card">
                    <h3><i class="fas fa-envelope"></i> Email Address</h3>
                    <p>{{ Auth::user()->email }}</p>
                </div>
                <div class="detail-card">
                    <h3><i class="fas fa-phone"></i> Phone Number</h3>
                    <p>{{ Auth::user()->phone }}</p>
                </div>
                <div class="detail-card">
                    <h3><i class="fas fa-building"></i> Department</h3>
                    <p>{{ Auth::user()->department->name?? "N/A" }}</p>
                </div>
                <div class="detail-card">
                    <h3><i class="fas fa-calendar-alt"></i> Join Date</h3>
                    <p>{{ Auth::user()->hire_date ? Auth::user()->hire_date->format('M d, Y') : 'N/A' }}</p>
                </div>
                <div class="detail-card editable" onclick="openDobModal()">
                    <h3><i class="fas fa-birthday-cake"></i> Date of Birth</h3>
                    <p id="dobDisplay">{{ Auth::user()->dob ? Auth::user()->dob->format('M d, Y') : 'Not Set' }}</p>
                    <span class="edit-icon"><i class="fas fa-edit"></i></span>
                </div>
                <div class="detail-card">
                    <h3><i class="fas fa-briefcase"></i> Position</h3>
                    <p>{{ Auth::user()->position }}</p>
                </div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="openEmailModal()">
                    <i class="fas fa-envelope"></i> Change Email
                </button>
                <button class="btn btn-success" onclick="openPasswordModal()">
                    <i class="fas fa-lock"></i> Change Password
                </button>
                <button class="btn btn-info" onclick="openDobModal()">
                    <i class="fas fa-birthday-cake"></i> Update Date of Birth
                </button>
            </div>
        </div>

        <!-- Email Change Modal -->
        <div class="modal" id="emailModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Change Email Address</h2>
                    <button class="close-btn" onclick="closeModal('emailModal')">&times;</button>
                </div>
                <div class="form-group">
                    <label for="currentEmail">Current Email</label>
                    <input type="email" id="currentEmail" value="{{ Auth::user()->email }}" disabled>
                </div>
                <div class="form-group">
                    <label for="newEmail">New Email Address</label>
                    <input type="email" id="newEmail" placeholder="Enter new email address">
                </div>
                <div class="form-group">
                    <label for="confirmEmail">Confirm New Email</label>
                    <input type="email" id="confirmEmail" placeholder="Confirm new email address">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-cancel" onclick="closeModal('emailModal')">Cancel</button>
                    <button class="btn btn-primary" onclick="updateEmail()">Update Email</button>
                </div>
            </div>
        </div>

        <!-- Password Change Modal -->
        <div class="modal" id="passwordModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Change Password</h2>
                    <button class="close-btn" onclick="closeModal('passwordModal')">&times;</button>
                </div>
                <div class="form-group">
                    <label for="currentPassword">Current Password</label>
                    <input type="password" id="currentPassword" placeholder="Enter current password">
                </div>
                <div class="form-group">
                    <label for="newPassword">New Password</label>
                    <input type="password" id="newPassword" placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Confirm New Password</label>
                    <input type="password" id="confirmPassword" placeholder="Confirm new password">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-cancel" onclick="closeModal('passwordModal')">Cancel</button>
                    <button class="btn btn-primary" onclick="updatePassword()">Update Password</button>
                </div>
            </div>
        </div>

        <!-- Date of Birth Modal -->
        <div class="modal" id="dobModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Update Date of Birth</h2>
                    <button class="close-btn" onclick="closeModal('dobModal')">&times;</button>
                </div>
                <div class="form-group">
                    <label for="currentDob">Current Date of Birth</label>
                    <input type="text" id="currentDob" value="{{ Auth::user()->dob ? Auth::user()->dob->format('Y-m-d') : '' }}" disabled>
                </div>
                <div class="form-group">
                    <label for="newDob">New Date of Birth</label>
                    <input type="date" id="newDob">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-cancel" onclick="closeModal('dobModal')">Cancel</button>
                    <button class="btn btn-primary" onclick="updateDob()">Update Date of Birth</button>
                </div>
            </div>
        </div>
        </main>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
function openEmailModal() {
    document.getElementById('emailModal').style.display = 'flex';
}

function openPasswordModal() {
    document.getElementById('passwordModal').style.display = 'flex';
}

function openDobModal() {
    document.getElementById('dobModal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}

function updateEmail() {
    let newEmail = $("#newEmail").val();
    let confirmEmail = $("#confirmEmail").val();
    
    if (!newEmail || !confirmEmail) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Please fill in all fields!',
        });
        return;
    }
    
    if (newEmail !== confirmEmail) {
        Swal.fire({
            icon: 'error',
            title: 'Emails do not match',
            text: 'Please make sure both email addresses are identical.',
        });
        return;
    }

    let click = "changeemail";
    $.ajax({
        url: "{{ route('api.profile.update') }}",
        method: "POST",
        data: {click:click, newEmail:newEmail, _token: '{{ csrf_token() }}'},
        success: function(e){
            Swal.fire({
                title: 'Updating Email',
                text: 'Please wait...',
                timer: 2000,
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            }).then(()=>{
                if(e == "success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Email Updated',
                        text: 'Your email address has been successfully updated.',
                        timer: 3000
                    }).then(() => {
                        $("#emailModal").hide();
                        document.querySelector('.detail-card:nth-child(1) p').textContent = newEmail;
                    });
                }
                else{
                    Swal.fire({
                        icon: 'error',  
                        title: 'Email Not Updated',
                        text: 'Something Went Wrong Please Try Again Later!.',
                    });
                }
            });
        }
    });
}

function updatePassword() {
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (!currentPassword || !newPassword || !confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Please fill in all fields!',
        });
        return;
    }
    
    if (newPassword !== confirmPassword) {
        Swal.fire({
            icon: 'error',
            title: 'Passwords do not match',
            text: 'Please make sure both passwords are identical.',
        });
        return;
    }
    
    if (newPassword.length < 8) {
        Swal.fire({
            icon: 'error',
            title: 'Password too short',
            text: 'Password must be at least 8 characters long.',
        });
        return;
    }
    
    let click = "changepass";
    $.ajax({
        url: "{{ route('api.profile.update') }}",
        method: "POST",
        data: {click:click, newPassword:newPassword, currentPassword:currentPassword, _token: '{{ csrf_token() }}'},
        success: function(e){
            Swal.fire({
                title: 'Updating Password',
                text: 'Please wait...',
                timer: 2000,
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            }).then(()=>{
                if(e == "success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Updated',
                        text: 'Your Password has been successfully updated.',
                        timer: 3000
                    }).then(() => {
                        $("#passwordModal").hide();
                    });
                }
                else{
                    Swal.fire({
                        icon: 'error',  
                        title: 'Password Not Updated',
                        text: 'Something Went Wrong Please Try Again Later!.',
                    });
                }
            });
        }
    });
}

function updateDob() {
    const newDob = document.getElementById('newDob').value;
    
    if (!newDob) {
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'Please select a date of birth!',
        });
        return;
    }
    
    const selectedDate = new Date(newDob);
    const today = new Date();
    if (selectedDate > today) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid Date',
            text: 'Date of birth cannot be in the future.',
        });
        return;
    }
    
    let click = "changdob";
    $.ajax({
        url: "{{ route('api.profile.update') }}",
        method: "POST",
        data: {click:click, newDob:newDob, _token: '{{ csrf_token() }}'},
        success: function(e){
            Swal.fire({
                title: 'Updating DOB',
                text: 'Please wait...',
                timer: 2000,
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            }).then(()=>{
                if(e == "success"){
                    Swal.fire({
                        icon: 'success',
                        title: 'DOB Updated',
                        text: 'Your DOB has been successfully updated.',
                        timer: 3000
                    }).then(() => {
                        $("#dobModal").hide();
                        document.querySelector('.detail-card:nth-child(5) p').textContent = newDob;
                    });
                }
                else{
                    Swal.fire({
                        icon: 'error',  
                        title: 'DOB Not Updated',
                        text: 'Something Went Wrong Please Try Again Later!.',
                    });
                }
            });
        }
    });
}
</script>
@endpush