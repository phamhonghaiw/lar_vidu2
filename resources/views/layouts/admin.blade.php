<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin - Quản trị hệ thống</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body { display: flex; min-height: 100vh; background: #f8f9fa; }
        .sidebar { width: 240px; background-color: #343a40; color: white; padding-top: 20px; position: fixed; height: 100%; }
        .sidebar a { color: #fff; padding: 12px 20px; display: block; text-decoration: none; }
        .sidebar a:hover { background: #495057; }
        .content { flex: 1; padding: 20px; margin-left: 240px; }

        /* CHAT UI */
        #admin-chat-box { position: fixed; bottom: 20px; right: 20px; width: 350px; z-index: 9999; }
        #chat-popup { display: none; height: 500px; border-radius: 8px; overflow: hidden; }
        #user-list { border-bottom: 1px solid #ddd; max-height: 120px; overflow-y: auto; background: #eee; }
        .user-item { padding: 8px 15px; cursor: pointer; border-bottom: 1px solid #ddd; font-size: 0.9rem; }
        .user-item:hover { background: #ddd; }
        .user-item.active { background: #007bff; color: white; }
        
        #chat-messages { height: 220px; overflow-y: auto; padding: 10px; background: #fff; }
        .msg-row { margin-bottom: 5px; font-size: 0.85rem; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="text-center mb-3">
        <strong>{{ Auth::user()->name }}</strong><br>
        <span class="badge badge-success">Admin</span>
    </div>
    <a href="{{ route('admin.dashboard') }}">Dashboard</a>
    <a href="{{ route('admin.products.index') }}">Sản phẩm</a>
    <a href="{{ route('admin.categories.index') }}">Danh mục</a>
    <a href="{{ route('admin.users.index') }}">Người dùng</a>
    <a href="{{ route('admin.orders.index') }}">Đơn hàng</a>

    <form action="{{ route('logout') }}" method="POST" class="p-3">
        @csrf
        <button class="btn btn-danger btn-block btn-sm">Đăng xuất</button>
    </form>
</div>

<div class="content">
    @yield('content')
</div>

<div id="admin-chat-box">
    <button id="chat-toggle" class="btn btn-dark shadow">💬 Chat Khách hàng</button>

    <div id="chat-popup" class="card shadow-lg">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <strong>Hỗ trợ trực tuyến</strong>
            <button id="chat-close" class="btn btn-sm btn-light">X</button>
        </div>

        <div id="user-list">
            <div class="p-2 text-center text-muted"><small>Đang tải danh sách...</small></div>
        </div>

        <div id="chat-messages">
            <div class="text-center mt-5 text-muted">Chọn một khách hàng để xem tin nhắn</div>
        </div>

        <div class="card-footer bg-white">
            <div class="input-group">
                <input type="text" id="chat-input" class="form-control form-control-sm" placeholder="Nhập câu trả lời...">
                <div class="input-group-append">
                    <button id="send-btn" class="btn btn-success btn-sm">Gửi</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let currentUserId = null;
const chatPopup = document.getElementById("chat-popup");
const chatMessages = document.getElementById("chat-messages");
const chatInput = document.getElementById("chat-input");

// Mở/Đóng popup
document.getElementById("chat-toggle").onclick = () => {
    chatPopup.style.display = "block";
    loadUsers();
};
document.getElementById("chat-close").onclick = () => {
    chatPopup.style.display = "none";
};

// 1. Load danh sách User đã từng nhắn tin
function loadUsers() {
    fetch("{{ route('admin.chat.users') }}")
        .then(res => res.json())
        .then(users => {
            let html = "";
            users.forEach(user => {
                let activeClass = (currentUserId == user.id) ? 'active' : '';
                html += `<div class="user-item ${activeClass}" onclick="selectUser(${user.id}, this)">
                            ${user.name}
                         </div>`;
            });
            document.getElementById("user-list").innerHTML = html || '<div class="p-2 text-muted">Chưa có hội thoại</div>';
        });
}

// 2. Chọn User để chat
function selectUser(userId, element) {
    currentUserId = userId;
    // Highlight user được chọn
    document.querySelectorAll('.user-item').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    loadMessages();
}

// 3. Load tin nhắn của User đang được chọn
function loadMessages() {
    if (!currentUserId) return;

    fetch(`/admin/chat/messages/${currentUserId}`)
        .then(res => res.json())
        .then(messages => {
            let html = "";
            messages.forEach(msg => {
                let senderName = msg.sender_id == "{{ Auth::id() }}" ? "Bạn" : msg.sender.name;
                let color = msg.sender_id == "{{ Auth::id() }}" ? "blue" : "black";
                html += `<div class="msg-row" style="color: ${color}">
                            <strong>${senderName}:</strong> ${msg.content}
                         </div>`;
            });
            chatMessages.innerHTML = html;
            chatMessages.scrollTop = chatMessages.scrollHeight; // Cuộn xuống cuối
        });
}

// 4. Gửi tin nhắn cho User
function sendMessage() {
    let message = chatInput.value.trim();
    if (!message || !currentUserId) return;

    fetch("{{ route('admin.chat.send') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            message: message,
            user_id: currentUserId
        })
    })
    .then(res => res.json())
    .then(data => {
        chatInput.value = "";
        loadMessages();
    })
    .catch(err => console.error("Lỗi gửi tin:", err));
}

document.getElementById("send-btn").onclick = sendMessage;
chatInput.onkeypress = (e) => { if(e.key === 'Enter') sendMessage(); };

// 5. Polling (Tự động cập nhật mỗi 3 giây)
setInterval(() => {
    if (chatPopup.style.display === "block") {
        loadMessages();
        loadUsers(); // Cập nhật danh sách nếu có người mới nhắn
    }
}, 3000);
</script>

</body>
</html>